<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Http\Requests\btcDepositeRequest;
use App\Http\Services\TransactionService;
use App\Model\Bank;
use App\Model\BuyCoinHistory;
use App\Model\BuyCoinReferralHistory;
use App\Model\Coin;
use App\Model\CoinRequest;
use App\Model\DepositeTransaction;
use App\Model\IcoPhase;
use App\Model\Wallet;
use App\Model\WithdrawHistory;
use App\Repository\CoinRepository;
use App\Services\CoinPaymentsAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CoinController extends Controller
{
    // buy coin
    public function buyCoinPage()
    {
        try {
            $data['title'] = __('Buy Coin');
            $data['settings'] = allsetting();
            $data['STRIPE_KEY'] = isset(settings()['STRIPE_KEY']) ? settings()['STRIPE_KEY'] : 'pk_test_51HeHMCAk5wRsVMs9lKf2I5NzB8RyfvofDKdjq1sHQ8ydQtkmuRKgQYj7AokBqzo9tDWdkJOYPt3mjVy0UZzoRuHZ00U2htSb77';
            $data['banks'] = Bank::where(['status' => STATUS_ACTIVE])->get();
            if (env('APP_ENV') == 'local') {
                $data['coins'] = Coin::where(['status' => STATUS_ACTIVE])->where('type', '<>', DEFAULT_COIN_TYPE)->get();
            } else {
                $data['coins'] = Coin::where(['status' => STATUS_ACTIVE])->whereNotIn('type', [DEFAULT_COIN_TYPE, COIN_TYPE_LTCT])->get();
            }
            $baseCoin = strtoupper(allsetting('base_coin_type')) ?? 'BTC';
            $url = file_get_contents('https://min-api.cryptocompare.com/data/price?fsym=USD&tsyms=' . $baseCoin);
            $data['coin_price'] = settings('coin_price');
            $data['btc_dlr'] = (settings('coin_price') * json_decode($url, true)[$baseCoin]);
            $data['btc_dlr'] = custom_number_format($data['btc_dlr']);

            $activePhases = checkAvailableBuyPhase();

            $data['no_phase'] = false;
            if ($activePhases['status'] == false) {
                $data['no_phase'] = true;
            } else {
                if ($activePhases['futurePhase'] == false) {
                    $phase_info = $activePhases['pahse_info'];
                    if (isset($phase_info)) {
                        $data['coin_price'] = number_format($phase_info->rate, 4);
                        $data['btc_dlr'] = ($data['coin_price'] * json_decode($url, true)[$baseCoin]);
                        $data['btc_dlr'] = custom_number_format($data['btc_dlr']);
                    }
                }
            }
            $data['activePhase'] = $activePhases;

            return response()->json($data);
        } catch (\Exception $e) {
            Log::info('buy coin  ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function handleStripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        try {
            // Verify the webhook signature
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);

            if ($event->type == 'payment_intent.succeeded') {
                $paymentIntent = $event->data->object;

                // Process the successful payment here (update BuyCoinHistory, etc.)
                $this->processSuccessfulPayment($paymentIntent);
            }

            return response()->json(['success' => true, 'message' => 'Webhook handled']);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response()->json(['success' => false, 'message' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }
    }

    private function processSuccessfulPayment($paymentIntent)
    {
        DB::beginTransaction();
        try {
            // Retrieve metadata from paymentIntent
            $user_email = $paymentIntent->metadata->user_email;
            $coin_amount = $paymentIntent->metadata->coin_amount;
            $coin_price_btc = $paymentIntent->metadata->coin_price_btc;
            $phase_id = $paymentIntent->metadata->phase_id;
            $referral_level = $paymentIntent->metadata->referral_level;
            $phase_fees = $paymentIntent->metadata->phase_fees;
            $bonus = $paymentIntent->metadata->bonus;
            $affiliation_percentage = $paymentIntent->metadata->affiliation_percentage;

            // Save to BuyCoinHistory or any other relevant logic
            $btc_transaction = new BuyCoinHistory();
            $btc_transaction->type = STRIPE;
            $btc_transaction->address = 'N/A';
            $btc_transaction->user_id = Auth::id();
            $btc_transaction->doller = $coin_amount;  // Using $coin_amount here as placeholder for dollars
            $btc_transaction->btc = $coin_price_btc;
            $btc_transaction->phase_id = $phase_id;
            $btc_transaction->referral_level = $referral_level;
            $btc_transaction->fees = $phase_fees;
            $btc_transaction->bonus = $bonus;
            $btc_transaction->referral_bonus = $affiliation_percentage;
            $btc_transaction->requested_amount = $coin_amount;
            $btc_transaction->coin = $coin_amount;
            $btc_transaction->coin_type = strtoupper(allsetting('base_coin_type')) ?? 'BTC';
            $btc_transaction->stripe_token = $paymentIntent->id;
            $btc_transaction->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process Stripe payment: ' . $e->getMessage());
        }
    }

    public function buyCoinRate(Request $request)
    {

            $data['amount'] = $request->amount ?? 0;
            $data['coin_type'] = $request->payment_type ? check_coin_type($request->payment_type) : strtoupper(allsetting('base_coin_type'));

            $coin_price = settings('coin_price');
            $activePhases = checkAvailableBuyPhase();
            $data['phase_fees'] = 0;
            $data['bonus'] = 0;
            $data['no_phase'] = false;
            if ($activePhases['status'] == false) {
                $data['no_phase'] = true;
            } else {
                if ($activePhases['futurePhase'] == false) {
                    $phase_info = $activePhases['pahse_info'];
                    if (isset($phase_info)) {
                        $coin_price = customNumberFormat($phase_info->rate);
                        $data['phase_fees'] = calculate_phase_percentage($data['amount'], $phase_info->fees);
                        $data['bonus'] = calculate_phase_percentage($data['amount'], $phase_info->bonus);
                        $coin_amount = ($data['amount'] - $data['bonus']);
                        $data['amount'] = $coin_amount;
                        $data['phase_fees'] = customNumberFormat($data['phase_fees']);
                    }
                }
            }

            $data['coin_price'] = bcmul($coin_price, $data['amount'], 8);
            $data['coin_price'] = customNumberFormat($data['coin_price']);
            if ($request->pay_type == BTC) {
                $coinpayment = new CoinPaymentsAPI();
                $api_rate = $coinpayment->GetRates('');
                $data['btc_dlr'] = converts_currency($data['coin_price'], $data['coin_type'], $api_rate);
            } else {
                $data['coin_type'] = allsetting('base_coin_type');
                $url = file_get_contents('https://min-api.cryptocompare.com/data/price?fsym=USD&tsyms=' . $data['coin_type']);
                $data['btc_dlr'] = $data['coin_price'] * (json_decode($url, true)[$data['coin_type']]);
            }

            $data['btc_dlr'] = custom_number_format($data['btc_dlr']);

            return response()->json($data);

    }

    // buy coin process
    public function buyCoin(btcDepositeRequest $request)
    {
        try {
            $baseCoin = strtoupper(allsetting('base_coin_type')) ?? 'BTC';
            $coinRepo = new CoinRepository();
            $url = file_get_contents('https://min-api.cryptocompare.com/data/price?fsym=USD&tsyms=' . $baseCoin);

            if (isset(json_decode($url, true)[$baseCoin])) {
                $phase_fees = 0;
                $affiliation_percentage = 0;
                $bonus = 0;
                $coin_amount = $request->coin;
                $phase_id = '';
                $referral_level = '';

                if (isset($request->phase_id)) {
                    $phase = IcoPhase::where('id', $request->phase_id)->first();
                    if (isset($phase)) {
                        $total_sell = BuyCoinHistory::where('phase_id', $phase->id)->sum('coin');
                        if (($total_sell + $coin_amount) > $phase->amount) {
                            return response()->json(['success' => false, 'message' => __('Insufficient phase amount')], 400);
                        }
                        $phase_id = $phase->id;
                        $referral_level = $phase->affiliation_level;
                        $phase_fees = calculate_phase_percentage($request->coin, $phase->fees);
                        $affiliation_percentage = 0;
                        $bonus = calculate_phase_percentage($request->coin, $phase->bonus);
                        $coin_amount = ($request->coin - $bonus);
                        $coin_price_doller = bcmul($coin_amount, $phase->rate, 8);
                        $coin_price_btc = bcmul(custom_number_format(json_decode($url, true)[$baseCoin]), $coin_price_doller, 8);
                    } else {
                        $coin_price_doller = bcmul($request->coin, settings('coin_price'), 8);
                        $coin_price_btc = bcmul(custom_number_format(json_decode($url, true)[$baseCoin]), $coin_price_doller, 8);
                    }
                } else {
                    $coin_price_doller = bcmul($request->coin, settings('coin_price'), 8);
                    $coin_price_btc = bcmul(custom_number_format(json_decode($url, true)[$baseCoin]), $coin_price_doller, 8);
                }

                if ($request->payment_type == BTC) {
                    $buyCoinWithCoinPayment = $coinRepo->buyCoinWithCoinPayment($request, $coin_amount, $coin_price_doller, $phase_id, $referral_level, $phase_fees, $bonus, $affiliation_percentage);
                    if ($buyCoinWithCoinPayment['success'] == true) {
                        return response()->json(['success' => true, 'message' => $buyCoinWithCoinPayment['message'], 'data' => ['address' => $buyCoinWithCoinPayment['data']->address]]);
                    } else {
                        return response()->json(['success' => false, 'message' => $buyCoinWithCoinPayment['message']], 400);
                    }
                } elseif ($request->payment_type == BANK_DEPOSIT) {
                    $buyCoinWithBank = $coinRepo->buyCoinWithBank($request, $coin_amount, $coin_price_doller, $coin_price_btc, $phase_id, $referral_level, $phase_fees, $bonus, $affiliation_percentage);
                    if ($buyCoinWithBank['success'] == true) {
                        return response()->json(['success' => true, 'message' => $buyCoinWithBank['message']]);
                    } else {
                        return response()->json(['success' => false, 'message' => $buyCoinWithBank['message']], 400);
                    }
                } elseif ($request->payment_type == STRIPE) {
                    $buyCoinWithStripe = $coinRepo->buyCoinWithStripe($request, $coin_amount, $coin_price_doller, $coin_price_btc, $phase_id, $referral_level, $phase_fees, $bonus, $affiliation_percentage);
                    if ($buyCoinWithStripe['success'] == true) {
                        return response()->json(['success' => true, 'message' => $buyCoinWithStripe['message']]);
                    } else {
                        return response()->json(['success' => false, 'message' => $buyCoinWithStripe['message']], 400);
                    }
                } else {
                    return response()->json(['success' => false, 'message' => "Something went wrong"], 400);
                }
            } else {
                return response()->json(['success' => false, 'message' => "Something went wrong"], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => "Something went wrong"], 500);
        }
    }

    //bank details
    public function bankDetails(Request $request)
    {
        $data = ['success' => false, 'message' => __('Invalid request'), 'data_generate' => ''];
        $data_generate = '';
        if (isset($request->val)) {
            $bank = Bank::where('id', $request->val)->first();
            if (isset($bank)) {
                $data_generate = '<h3 class="text-center">' . __('Bank Details') . '</h3><table class="table">';
                $data_generate .= '<tr><td>' . __("Bank Name") . ' :</td> <td>' . $bank->bank_name . '</td></tr>';
                $data_generate .= '<tr><td>' . __("Account Holder Name") . ' :</td> <td>' . $bank->account_holder_name . '</td></tr>';
                $data_generate .= '<tr><td>' . __("Bank Address") . ' :</td> <td>' . $bank->bank_address . '</td></tr>';
                $data_generate .= '<tr><td>' . __("Country") . ' :</td> <td>' . country($bank->country) . '</td></tr>';
                $data_generate .= '<tr><td>' . __("IBAN") . ' :</td> <td>' . $bank->iban . '</td></tr>';
                $data_generate .= '<tr><td>' . __("Swift Code") . ' :</td> <td>' . $bank->swift_code . '</td></tr>';
                $data_generate .= '</table>';
                $data['data_generate'] = $data_generate;
                $data['success'] = true;
                $data['message'] = __('Data get successfully.');
            }
        }

        return response()->json($data);
    }

    // coin payment success page
    public function buyCoinByAddress($address)
    {
        $data['title'] = __('Coin Payment');
        if (is_numeric($address)) {
            $coinAddress = BuyCoinHistory::where(['user_id' => Auth::id(), 'id' => $address, 'status' => STATUS_PENDING])->first();
        } else {
            $coinAddress = BuyCoinHistory::where(['user_id' => Auth::id(), 'address' => $address, 'status' => STATUS_PENDING])->first();
        }
        if (isset($coinAddress)) {
            $data['coinAddress'] = $coinAddress;
            return response()->json(['success' => true, 'data' => $data]);
        } else {
            return response()->json(['success' => false, 'message' => __('Address not found')], 404);
        }
    }

    // buy coin history
    public function buyCoinHistory(Request $request)
    {
        $query = BuyCoinHistory::where('user_id', Auth::id());;
        // Dynamic Filters
        $items = $this->applyFiltersAndSorting($query, $request);
        // Transform data as per the original method
        $data = $items->getCollection()->transform(function ($item) {
            $item->type = byCoinType($item->type);
            $item->deposit_status = deposit_status($item->status);
            return $item;
        });

        // Return JSON response for React
        return response()->json([
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
    }

    // buy coin history
    public function buyCoinReferralHistory(Request $request)
    {
        $query = BuyCoinReferralHistory::where(['user_id' => Auth::id(), 'status' => STATUS_ACTIVE]);

        $items = $this->applyFiltersAndSorting($query, $request);
        // Transform data as per the original method
        $data = $items->getCollection()->transform(function ($item) {
            $item->created_at = $item->created_at->toDateTimeString();
            $item->deposit_status = deposit_status($item->status);
            $item->coin_type = check_default_coin_type($item->wallet->coin_type);
            return $item;
        });

        // Return JSON response for React
        return response()->json([
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);

    }
    // give or request coin
    public function requestCoin(Request $request)
    {
        try {
            $data['wallets'] = Wallet::where(['user_id' => Auth::id(), 'coin_type' => 'Default'])
                ->where('balance', '>', 0)
                ->get();
            $data['coin'] = Coin::where(['type' => DEFAULT_COIN_TYPE])->first();
            $data['qr'] = $request->qr ?? 'requests';

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // send coin request
    public function sendCoinRequest(Request $request)
    {
        $coin = Coin::where(['type' => DEFAULT_COIN_TYPE])->first();
        $rules = [
            'email' => 'required|exists:users,email',
            'amount' => ['required', 'numeric', 'min:' . $coin->minimum_withdrawal, 'max:' . $coin->maximum_withdrawal],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->all()], 400);
        }

        try {
            $response = app(CoinRepository::class)->sendCoinAmountRequest($request);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // send coin request
    public function giveCoin(Request $request)
    {
        $coin = Coin::where(['type' => DEFAULT_COIN_TYPE])->first();
        $rules = [
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => ['required', 'numeric', 'min:' . $coin->minimum_withdrawal, 'max:' . $coin->maximum_withdrawal],
            'email' => 'required|exists:users,email',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->all()], 400);
        }

        try {
            $response = app(CoinRepository::class)->giveCoinToUser($request);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // send coin history
    public function giveCoinHistory(Request $request)
    {
        $query = CoinRequest::where(['sender_user_id' => Auth::id()]);
        $fieldTableMap = [
            'sender_user_id' => ['sender' => 'email'],
            'receiver_user_id' => ['receiver' => 'email'],
        ];
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);
        $data = $items->getCollection()->transform(function ($item) {
            $item->sender_user_id = $item->sender->email;
            $item->deposit_status = deposit_status($item->status);
            $item->coin_type = settings('coin_name');
            $item->receiver_user_id = $item->receiver->email;

            return $item;
        });

        // Return JSON response for React
        return response()->json([
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
    }

    // send coin history
    public function receiveCoinHistory(Request $request)
    {
        $query = CoinRequest::where(['receiver_user_id' => Auth::id()]);
        $fieldTableMap = [
            'sender_user_id' => ['sender' => 'email'],
            'receiver_user_id' => ['receiver' => 'email'],
        ];
        $items = $this->applyFiltersAndSorting($query, $request,$fieldTableMap);
        $data = $items->getCollection()->transform(function ($item) {
            $item->sender_user_id = $item->sender->email;
            $item->deposit_status = deposit_status($item->status);
            $item->coin_type = settings('coin_name');
            $item->receiver_user_id = $item->receiver->email;

            return $item;
        });
        // Return JSON response for React
        return response()->json([
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);

    }
    // pending request coin history
    public function pendingRequest(Request $request)
    {
        $query = CoinRequest::where(['sender_user_id' => Auth::id(), 'status' => STATUS_PENDING]);
        $fieldTableMap = [
            'sender_user_id' => ['sender' => 'email'],
            'receiver_user_id' => ['receiver' => 'email'],
        ];
        $items = $this->applyFiltersAndSorting($query, $request,$fieldTableMap);
        $data = $items->getCollection()->transform(function ($item) {
            $item->deposit_status = deposit_status($item->status);
            $item->coin_type = settings('coin_name');
            $item->receiver_user_id = $item->receiver->email;
            $item->action = new \stdClass();
            $item->action->Accept = route('acceptCoinRequest', ['id' => encrypt($item->id)]);
            $item->action->Reject = route('declineCoinRequest', ['id' => encrypt($item->id)]);
            return $item;
        });

        // Return JSON response for React
        return response()->json([
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
    }
    // coin request accept process

    public function acceptCoinRequest($id)
    {
        try {
            $request_id = decrypt($id);
            $response = app(CoinRepository::class)->acceptCoinRequest($request_id);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // pending coin reject process
    public function declineCoinRequest($id)
    {
        try {
            $request_id = decrypt($id);
            $response = app(CoinRepository::class)->rejectCoinRequest($request_id);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // withdrawal coin history
    public function defaultWithdrawalHistory(Request $request)
    {
        if ($request->ajax()) {
            $items = WithdrawHistory::where('user_id', Auth::id());
            return datatables($items)
                ->editColumn('status', function ($item) {
                    return deposit_status($item->status);
                })
                ->make(true);
        }

        return response()->json(['success' => false, 'message' => __('Invalid request')], 400);
    }

    // when withdrawal failed then it should be called
    public function withdrawalCancelCallback(Request $request)
    {
        Log::info('withdrawalCancelCallback called');
        DB::beginTransaction();
        try {
            $temp = WithdrawHistory::find($request->temp_id);
            if ($temp) {
                $temp->delete();
                DB::commit();
                return response()->json(['success' => true, 'message' => __('Withdrawal cancelled')]);
            } else {
                Log::info('Temp withdrawal not found: ' . $request->temp_id);
                return response()->json(['success' => false, 'message' => __('Temp withdrawal not found')], 404);
            }
        } catch (\Exception $exception) {
            Log::error('Error in withdrawalCancelCallback: ' . $exception->getMessage());
            DB::rollBack();
            return response()->json(['success' => false, 'message' => __('Something went wrong')], 500);
        }
    }

    // default coin deposit
    public function depositCallback(Request $request)
    {
        Log::info('Call deposit wallet');
        $data = ['success' => false, 'message' => 'Something went wrong'];

        DB::beginTransaction();
        try {
            $request = (object) $request->all();
            Log::info(json_encode($request));

            $walletAddress = DepositeTransaction::where('transaction_id', $request->transactionHash)->first();
            $wallet = Wallet::where("user_id", Auth::id())->where("coin_type", DEFAULT_COIN_TYPE)->first();

            if (empty($walletAddress) && !empty($wallet)) {
                $checkDeposit = DepositeTransaction::where('transaction_id', $request->transactionHash)->first();
                if (isset($checkDeposit)) {
                    $data['message'] = 'Transaction ID already exists in deposit';
                    Log::info('Transaction ID already exists in deposit');
                    return response()->json($data);
                }

                $depositData = [
                    'address' => $request->from,
                    'address_type' => ADDRESS_TYPE_EXTERNAL,
                    'amount' => $request->value,
                    'fees' => 0,
                    'doller' => $request->transactionIndex * settings('coin_price'),
                    'btc' => 0,
                    'type' => $wallet->coin_type,
                    'transaction_id' => $request->transactionHash,
                    'confirmations' => $request->blockNumber,
                    'status' => STATUS_SUCCESS,
                    'receiver_wallet_id' => $wallet->id
                ];

                $depositCreate = DepositeTransaction::create($depositData);
                Log::info(json_encode($depositCreate));

                if ($depositCreate) {
                    Log::info('Balance before deposit ' . $wallet->balance);
                    $wallet->increment('balance', $depositCreate->amount);
                    Log::info('Balance after deposit ' . $wallet->balance);
                    $data = [
                        'success' => true,
                        'message' => 'Deposited successfully',
                        'hash' => $request->transactionHash
                    ];
                } else {
                    Log::info('Deposit not created');
                    $data['message'] = 'Deposit not created';
                }
            } else {
                $data['message'] = 'No wallet found';
                Log::info('No wallet found');
            }

            DB::commit();
            return response()->json($data);
        } catch (\Exception $e) {
            $data['message'] = $e->getMessage() . ' CoinController.php' . $e->getLine();
            Log::info($data['message']);
            DB::rollBack();
            return response()->json($data);
        }
    }


    // withdrawal coin
    public function withdrawalCoin(Request $request)
    {
        try {
            $wallet = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.user_id' => Auth::id(), 'wallets.coin_type' => DEFAULT_COIN_TYPE])
                ->select('wallets.*', 'coins.status as coin_status', 'coins.is_withdrawal', 'coins.minimum_withdrawal', 'coins.maximum_withdrawal', 'coins.withdrawal_fees')
                ->first();

            if (!$wallet) {
                return response()->json(['success' => false, 'message' => 'Wallet not found'], 404);
            }

            return response()->json([
                'success' => true,
                'wallet' => $wallet,
                'qr' => $request->qr ?? 'requests'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    // withdrawal callback
    public function callback(Request $request)
    {
        try {
            $temp = WithdrawHistory::find($request->temp);

            if (!$temp) {
                return response()->json(['success' => false, 'message' => 'Withdrawal not found'], 404);
            }

            $temp->status = STATUS_ACCEPTED;
            $temp->transaction_hash = $request->hash;
            $temp->save();

            $wallet = Wallet::find($temp->wallet_id);
            if (!$wallet) {
                return response()->json(['success' => false, 'message' => 'Wallet not found'], 404);
            }

            $deductAmount = $temp->amount + $temp->fees;
            $wallet->decrement('balance', $deductAmount);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal is now completed',
                'hash' => $request->hash
            ]);
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }


    // check default balance
    public function checkBalance($balance, Request $request)
    {
        Log::info('Withdrawal balance ' . $balance);
        Log::info(json_encode($request->all()));

        try {
            $transactionService = new TransactionService();
            $wallet = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.user_id' => Auth::id(), 'wallets.coin_type' => DEFAULT_COIN_TYPE])
                ->select('wallets.*', 'coins.status as coin_status', 'coins.is_withdrawal', 'coins.minimum_withdrawal', 'coins.maximum_withdrawal', 'coins.withdrawal_fees')
                ->first();

            if (!$wallet) {
                return response()->json(['success' => false, 'message' => 'Wallet not found'], 404);
            }

            if ($balance >= $wallet->balance) {
                return response()->json(['success' => false, 'message' => "Amount can't be more than available balance"], 400);
            }

            $user = Auth::user();
            $checkKyc = $transactionService->kycValidationCheck($user->id);

            if (!$checkKyc['success']) {
                return response()->json($checkKyc);
            }

            $checkValidate = $transactionService->checkWithdrawalValidation($request, $user, $wallet);
            if (!$checkValidate['success']) {
                return response()->json($checkValidate);
            }

            $result = $transactionService->sendChainExternal($wallet->id, $request->address, $request->amount);
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'cl' => allsetting('chain_link'),
                    'ca' => allsetting('contract_address'),
                    'wa' => allsetting('wallet_address'),
                    'pk' => allsetting('private_key'),
                    'chain_link' => allsetting('chain_link'),
                    'temp' => $result['temp']
                ]);
            } else {
                return response()->json($result);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

}
