<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CoinRequest;
use App\Http\Requests\Admin\GiveCoinRequest;
use App\Http\Services\CoinService;
use App\Jobs\AdjustWalletJob;
use App\Jobs\DistributeBuyCoinReferralBonus;
use App\Model\AdminGiveCoinHistory;
use App\Model\BuyCoinHistory;
use App\Model\Coin;
use App\Model\Wallet;
use App\Services\CoinPaymentsAPI;
use App\Services\Logger;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CoinController extends Controller
{

    public $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    // Admin pending coin order
    public function adminCoinOrder(Request $request)
    {
        $query =  BuyCoinHistory::query();
        $items = $this->applyFiltersAndSorting($query, $request);
        $data = collect($items->items())->transform(function ($dpst) use ($request) {
            if (deposit_status($dpst->status) == 'Pending'){
                $dpst->action = new \stdClass();
                $dpst->action->accept = route('adminAcceptPendingBuyCoin', encrypt($dpst->id));
                $dpst->action->reject = route('adminRejectPendingBuyCoin', encrypt($dpst->id));
            }
            return [
                'id' => $dpst->id,
                'payment_type' => $dpst->type == BANK_DEPOSIT ? ['title' => 'Bank Deposit' , 'image' => imageSrc($dpst->bank_sleep, IMG_SLEEP_VIEW_PATH)] : byCoinType($dpst->type),
                'email' => $dpst->user ? $dpst->user->email : '',
                'coin' => $dpst->coin,
                'address' => $dpst->address,
                'deposit_status' => deposit_status($dpst->status),
                'btc' => $dpst->btc . ' ' . find_coin_type($dpst->coin_type),
                'created_at' => $dpst->created_at->toDateTimeString(),
                'actions' => $dpst->action,
            ];
        });

        // Return JSON response for React
        return response()->json([
            'title' => __('Coin List'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);

    }

    // Admin approved coin order
    public function adminApprovedOrder(Request $request)
    {
        if ($request->ajax()) {
            $deposit = BuyCoinHistory::where(['status' => STATUS_ACTIVE])->get();

            $data = $deposit->map(function ($dpst) {
                return [
                    'id' => $dpst->id,
                    'payment_type' => $dpst->type == BANK_DEPOSIT ? receipt_view_html(imageSrc($dpst->bank_sleep, IMG_SLEEP_VIEW_PATH)) : byCoinType($dpst->type),
                    'email' => $dpst->user ? $dpst->user->email : '',
                    'btc' => $dpst->btc . ' ' . find_coin_type($dpst->coin_type),
                ];
            });

            return response()->json(['data' => $data]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
    }

    // Admin rejected coin order
    public function adminRejectedOrder(Request $request)
    {
        if ($request->ajax()) {
            $deposit = BuyCoinHistory::where(['status' => STATUS_REJECTED])->get();

            $data = $deposit->map(function ($dpst) {
                return [
                    'id' => $dpst->id,
                    'payment_type' => $dpst->type == BANK_DEPOSIT ? receipt_view_html(imageSrc($dpst->bank_sleep, IMG_SLEEP_VIEW_PATH)) : byCoinType($dpst->type),
                    'email' => $dpst->user ? $dpst->user->email : '',
                    'btc' => $dpst->btc . ' ' . find_coin_type($dpst->coin_type),
                    'created_at' => $dpst->created_at->toDateTimeString(),
                ];
            });

            return response()->json(['data' => $data]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
    }

    // Pending coin accept process
    public function adminAcceptPendingBuyCoin($id)
    {
        if (isset($id)) {
            try {
                $wdrl_id = decrypt($id);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Invalid ID'], 400);
            }

            DB::beginTransaction();
            try {
                $transaction = BuyCoinHistory::where(['id' => $wdrl_id, 'status' => STATUS_PENDING])->firstOrFail();

                $primary = get_primary_wallet($transaction->user_id, DEFAULT_COIN_TYPE);
                $primary->increment('balance', $transaction->coin);
                $transaction->status = STATUS_SUCCESS;
                $transaction->save();
                sendBuyCoinEmail('transactions.coin-order-accept',$transaction);
                if (!empty($transaction->phase_id)) {
                    dispatch(new DistributeBuyCoinReferralBonus($transaction))->onQueue('referral');
                }

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Request accepted successfully']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Invalid ID'], 400);
    }

    // Pending coin reject process
    public function adminRejectPendingBuyCoin($id)
    {
        if (isset($id)) {
            try {
                $wdrl_id = decrypt($id);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Invalid ID'], 400);
            }

            $transaction = BuyCoinHistory::where(['id' => $wdrl_id, 'status' => STATUS_PENDING])->firstOrFail();
            $transaction->status = STATUS_REJECTED;
            $transaction->update();
            sendBuyCoinEmail('transactions.coin-order-reject',$transaction);


            return response()->json(['success' => true, 'message' => 'Request cancelled successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Invalid ID'], 400);
    }

    // Give coin page
    public function giveCoinToUser()
    {
        return response()->json([
            'success' => true,
            'title' => __('Give default coin to user'),
            'users' => User::where(['role' => USER_ROLE_USER, 'status' => STATUS_ACTIVE])->get()
        ]);
    }

    // Give coin process
    public function giveCoinToUserProcess(GiveCoinRequest $request)
    {
        try {
            if ($request->amount <= 0) {
                return response()->json(['success' => false, 'message' => __('Minimum coin amount is 1')], 400);
            }

            if ($request->amount > settings('admin_send_default_maximum')) {
                return response()->json(['success' => false, 'message' => __('Maximum coin amount is ') . settings('admin_send_default_maximum')], 400);
            }

            if (isset($request->user_id[0])) {
                DB::beginTransaction();
                foreach ($request->user_id as $value) {
                    $user = User::find($value);
                    $wallet = Wallet::where(['user_id' => $value, 'coin_type' => DEFAULT_COIN_TYPE, 'is_primary' => STATUS_ACTIVE])->first();
                    if ($user && $wallet) {
                        $wallet->increment('balance', $request->amount);
                        $this->saveGiveCoinHistory($user->id, $wallet->id, $request->amount);
                    }
                }
                DB::commit();
                return response()->json(['success' => true, 'message' => __('Coin sent successfully')]);
            } else {
                return response()->json(['success' => false, 'message' => __('Please select at least one user')], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Save give coin history
    public function saveGiveCoinHistory($user_id, $wallet_id, $amount)
    {
        try {
            AdminGiveCoinHistory::create(['user_id' => $user_id, 'wallet_id' => $wallet_id, 'amount' => $amount]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // Give coin history
    public function giveCoinHistory(Request $request)
    {
        $query = AdminGiveCoinHistory::join('users', 'users.id', '=', 'admin_give_coin_histories.user_id')
            ->select('admin_give_coin_histories.*', 'users.email as email');

        $fieldTableMap = [
            'email' => 'users',
            'wallet_id' => ['wallet' => 'name'],
        ];

        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);
        $data = collect($items->items())->transform(function ($item) {
            return [
                'id' => $item->id,
                'wallet_id' => $item->wallet ? $item->wallet->name : 'N/A',
                'email' => $item->email,
                'amount' => $item->amount,
                'created_at' => $item->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'title' => __('All Deposit And Withdrawal History'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);

    }

    // all coin list
    public function adminCoinList(Request $request)
    {
        try {
            // Dispatch job to adjust wallet
            dispatch(new AdjustWalletJob())->onQueue('default');

            $query =  Coin::where('status', '<>', STATUS_DELETED);
            $items = $this->applyFiltersAndSorting($query, $request);
            $data = $items->getCollection()->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'status' => status($item->status),
                    'type' => $item->type,
                    'is_withdrawal' => $item->is_withdrawal,
                    'minimum_withdrawal' => $item->minimum_withdrawal,
                    'maximum_withdrawal' => $item->maximum_withdrawal,
                    'bg_color' => $item->bg_color,
                    'withdrawal_fees' => $item->withdrawal_fees,
                    'updated_at' => $item->updated_at->toDateTimeString(),
                    'action' => ['Edit' => route('adminCoinEdit', ['id' => encrypt($item->id)])]

                ];
            });

            // Return JSON response for React
            return response()->json([
                'title' => __('Coin List'),
                'data' => $data,
                'recordsTotal' => $items->total(),
                'recordsFiltered' => $items->total(), // Adjust if there are filters
                'draw' => $request->input('draw'), // Include draw for DataTables
            ]);


        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Adjust coin with coin payment
    public function adminCoinListWithCoinPayment(Request $request)
    {
        try {
            if ($request->update === "coinPayment") {
                $coinpayment = new CoinPaymentsAPI();
                $api_rate = $coinpayment->GetRates('');

                if ($api_rate['error'] === "ok") {
                    $active_coins = [];
                    foreach ($api_rate['result'] as $key => $result) {
                        if ($result['accepted'] == 1) {
                            $active_coins[$key] = [
                                'coin_type' => $key,
                                'name' => $result['name'],
                                'accepted' => $result['accepted']
                            ];
                        }
                    }

                    if (!empty($active_coins)) {
                        foreach ($active_coins as $key => $active) {
                            Coin::updateOrCreate(['type' => $active['coin_type']], [
                                'name' => $active['name'],
                                'type' => $active['coin_type'],
                                'status' => STATUS_ACTIVE
                            ]);
                        }
                    } else {
                        Coin::updateOrCreate(['type' => 'BTC'], [
                            'name' => 'Bitcoin',
                            'type' => 'BTC',
                            'status' => STATUS_ACTIVE
                        ]);
                    }

                    $dbCoins = Coin::where('status', '<>', STATUS_DELETED)->orderBy('id', 'asc')->get();
                    $db_coins = [];
                    foreach ($dbCoins as $dbc) {
                        $db_coins[$dbc->type] = [
                            'coin_type' => $dbc->type,
                            'name' => $dbc->name,
                            'accepted' => $dbc->status
                        ];
                    }

                    if (!empty($active_coins) && !empty($db_coins)) {
                        $inactive_coins = array_diff_key($db_coins, $active_coins);

                        foreach ($inactive_coins as $key => $value) {
                            if ($key !== DEFAULT_COIN_TYPE && $key !== COIN_TYPE_LTCT) {
                                Coin::where('type', $key)->update(['status' => STATUS_DELETED]);
                            }
                        }
                    }

                    dispatch(new AdjustWalletJob())->onQueue('default');

                    return response()->json([
                        'success' => true,
                        'message' => __('Coins updated successfully')
                    ]);
                } else {
                    dispatch(new AdjustWalletJob())->onQueue('default');

                    return response()->json([
                        'success' => false,
                        'message' => __('Failed to fetch coin rates')
                    ], 500);
                }
            }
            return response()->json([
                'success' => false,
                'message' => __('Invalid request')
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Change coin status
    public function adminCoinStatus(Request $request)
    {
        $coin = Coin::find($request->active_id);
        if ($coin) {
            $coin->update(['status' => $coin->status == STATUS_ACTIVE ? STATUS_DEACTIVE : STATUS_ACTIVE]);
            return response()->json([
                'success' => true,
                'message' => __('Status changed successfully')
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => __('Coin not found')
            ], 404);
        }
    }

    // Edit coin
    public function adminCoinEdit($id)
    {
        $service = new CoinService();
        $coinId = decryptId($id);

        if (is_array($coinId)) {
            return response()->json([
                'success' => false,
                'message' => __('Coin not found')
            ], 404);
        }

        $item = $service->getCoinDetailsById($coinId);

        if (isset($item) && !$item['success']) {
            return response()->json([
                'success' => false,
                'message' => $item['message']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'item' => $item['data'],
            'title' => __('Update Coin'),
            'button_title' => __('Update')
        ]);
    }

    // Coin save process
    public function adminCoinSaveProcess(CoinRequest $request)
    {
        try {
            // Validate and get only the required fields
            $validatedData = $request->all();
            // Map boolean fields to 1 or 0
            $booleanFields = [
                'is_deposit', 'is_withdrawal', 'status', 'trade_status',
                'is_wallet', 'is_buy', 'is_virtual_amount', 'is_currency',
                'is_base', 'is_transferable'
            ];

            foreach ($booleanFields as $field) {
                $validatedData[$field] = isset($validatedData[$field]) ? 1 : 0;
            }

            // Handle file upload
            if ($request->hasFile('coin_icon')) {
                $icon = uploadFile($request->file('coin_icon'), IMG_ICON_PATH, '');

                if ($icon === false) {
                    throw new \Exception('Failed to upload coin icon.');
                }

                $validatedData['coin_icon'] = $icon;
            }

            // Add optional fields if they exist
            $optionalFields = ['bg_color', 'name'];
            foreach ($optionalFields as $field) {
                if ($request->has($field)) {
                    $validatedData[$field] = $request->input($field);
                }
            }

            // Save coin data using the service layer
            $coinService = new CoinService();

            $validatedData = array_filter($validatedData, fn($key) => $key !== 'coin_id', ARRAY_FILTER_USE_KEY);
            $coin = $coinService->addCoin($validatedData, $request->input('coin_id'));

            // Handle service response
            if ($coin['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $coin['message']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $coin['message']
            ], 400);

        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error in adminCoinSaveProcess: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request.'
            ], 500);
        }
    }
}
