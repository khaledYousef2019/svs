<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoinSwapRequest;
use App\Http\Requests\WalletCreateRequest;
use App\Http\Requests\withDrawRequest;
use App\Http\Services\TransactionService;
use App\Jobs\Withdrawal;
use App\Model\Coin;
use App\Model\CoWalletWithdrawApproval;
use App\Model\DepositeTransaction;
use App\Model\TempWithdraw;
use App\Model\Wallet;
use App\Model\WalletAddressHistory;
use App\Model\WalletCoUser;
use App\Model\WalletSwapHistory;
use App\Model\WithdrawHistory;
use App\Repository\WalletRepository;
use App\Services\CoinPaymentsAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class WalletController extends Controller
{
    public $repo;

    public function __construct()
    {
        $this->repo = new WalletRepository();
    }

    // Get My Pocket Details
    public function myPocket(Request $request)
    {
        $data = [];

        $wallets = Wallet::select('wallets.*')
            ->join('wallet_co_users', 'wallet_co_users.wallet_id', '=', 'wallets.id')
            ->join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where(['wallets.type' => CO_WALLET, 'wallet_co_users.user_id' => Auth::id()])
            ->orderBy('id', 'ASC');
        if (!$request->tab || $request->tab == 1 )
            $wallets = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.user_id' => Auth::id(), 'wallets.type' => PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE])
                ->orderBy('id', 'ASC')
                ->select('wallets.*');
        $fieldTableMap = [
            'name' => 'wallets',
            'status' => 'coins',
            'balance' => 'wallets',
            'coin_type' => 'wallets',
            'type' => 'wallets',
        ];
        $items = $this->applyFiltersAndSorting($wallets, $request,$fieldTableMap);

        $data['wallets'] = $items->getCollection()->transform(function ($item) {
            $item->status = status($item->status);
            return $item;
        });
        // Return JSON response for React
        return response()->json([
            'title' => __('My Pocket'),
            'tab' => $request->tab ?? null,
            'coins' =>  Coin::where('status', STATUS_ACTIVE)->get(),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);

    }

    // Get Coin Swap Details
    public function getCoinSwapDetails(Request $request)
    {
        $wallet = Wallet::find($request->id);
        $items = Coin::select('coins.*', 'wallets.name as wallet_name', 'wallets.id as wallet_id')
                ->join('wallets', 'wallets.coin_type', '=', 'coins.type')
                ->where('coins.status', STATUS_ACTIVE)
                ->where('wallets.user_id', Auth::id())
                ->where('coins.type', '!=', $wallet->coin_type)
                ->where('coins.type', '=', DEFAULT_COIN_TYPE)
                ->get();

        $data = $items->transform(function ($item) {
            return [
                'data-to_wallet_id'=>$item->wallet_id,
                'data-from_coin_type' => $item->type,
                'wallet_name' => $item->wallet_name .'('.check_default_coin_type($item->type).')',
            ];
        });
        return response()->json($data);

    }

    // Get Rate of Coin
    public function getRate(CoinSwapRequest $request)
    {
        $data = $this->repo->get_wallet_rate($request);

        if ($data['success'] === true) {
            return response()->json($data);
        }

        return response()->json(['error' => 'Unable to retrieve rate'], 400);
    }

    // Swap Coin
    public function swapCoin(CoinSwapRequest $request)
    {
        $fromWallet = Wallet::where(['id' => $request->from_coin_id])->first();
        if (!empty($fromWallet) && $fromWallet->type == CO_WALLET) {
            return response()->json(['success' => false ,'message' => __('Something went wrong')], 400);
        }

        $response = $this->repo->get_wallet_rate($request);
        if ($response['success'] === false) {
            return response()->json(['success' => $response['success'] ,'message' => __('Something went wrong')], 400);
        }

        $swap_coin = $this->repo->coinSwap($response['from_wallet'], $response['to_wallet'], $response['convert_rate'], $response['amount'], $response['rate']);

        return response()->json($swap_coin,$swap_coin['success'] ? 200 : 400);

    }

    // make default account
    public function makeDefaultAccount(Request $request)
    {
        $account_id = $request->input('account_id');
        $coin_type = $request->input('coin_type');

        $wallet = Wallet::find($account_id);
        if (!empty($wallet) && $wallet->type == CO_WALLET) {
            return response()->json(['error' => __('Something went wrong')], 400);
        }

        Wallet::where(['user_id' => Auth::id(), 'coin_type' => $coin_type])->update(['is_primary' => 0]);
        Wallet::updateOrCreate(['id' => $account_id], ['is_primary' => 1]);

        return response()->json(['success' => __('Default set successfully')]);
    }

    public function createWallet(WalletCreateRequest $request)
    {
        if (!empty($request->wallet_name)) {
            $request->type = $request->type ?? PERSONAL_WALLET;
            $coin = Coin::where(['type' => strtoupper($request->coin_type)])->first();
            $alreadyWallet = Wallet::where(['coin_id' => $coin->id, 'user_id' => Auth::id(), 'type' => $request->type])->first();

            if ($alreadyWallet) {
                return response()->json(['success' => false, 'message' => __("You already have this type of wallet")], 400);
            }

            try {
                DB::beginTransaction();
                $wallet = new Wallet();
                $wallet->user_id = Auth::id();
                $wallet->type = $request->type ?? PERSONAL_WALLET;
                $wallet->name = $request->wallet_name;
                $wallet->coin_type = strtoupper($request->coin_type);
                $wallet->status = STATUS_SUCCESS;
                $wallet->balance = 0;
                $wallet->coin_id = $coin->id;

                if (co_wallet_feature_active() && $request->type == CO_WALLET) {
                    $key = Str::random(64);
                    while (Wallet::where(['key' => $key])->exists()) {
                        $key = Str::random(64);
                    }
                    $wallet->key = $key;
                }

                $wallet->save();

                if (co_wallet_feature_active() && $request->type == CO_WALLET) {
                    WalletCoUser::create([
                        'user_id' => Auth::id(),
                        'wallet_id' => $wallet->id
                    ]);
                }

                DB::commit();

                return response()->json(['success' => true, 'message' => __("Pocket created successfully")], 200);

            } catch (\Exception $e) {
                Log::alert($e->getMessage());
                DB::rollBack();
                return response()->json(['success' => false, 'message' => __("Something went wrong.")], 400);
            }
        }
        return response()->json(['success' => false, 'message' => __("Pocket name can't be empty")], 400);

    }

    public function importWallet(Request $request)
    {
        $key = $request->input('key');

        if (!empty($key)) {
            $wallet = Wallet::where(['key' => $key, 'status' => STATUS_ACTIVE])->first();
            if (empty($wallet)) {
                return response()->json(['error' => __('Invalid Key')], 400);
            }

            $alreadyCoUser = WalletCoUser::where(['user_id' => Auth::id(), 'wallet_id' => $wallet->id])->first();
            if (!empty($alreadyCoUser)) {
                return response()->json(['error' => __('Already imported')], 400);
            }

            $maxCoUser = settings(MAX_CO_WALLET_USER_SLUG);
            $maxCoUser = !empty($maxCoUser) ? $maxCoUser : 2;
            $coUserCount = WalletCoUser::where(['wallet_id' => $wallet->id])->count();
            if ($coUserCount >= $maxCoUser) {
                return response()->json(['success' => false, 'message' => __("Can't import this pocket. Max co user limit reached.")], 400);
            }

            try {
                WalletCoUser::create([
                    'user_id' => Auth::id(),
                    'wallet_id' => $wallet->id
                ]);
            } catch (\Exception $e) {
                Log::alert($e->getMessage());
                return response()->json(['success' => false, 'message' => __("Something went wrong.")], 400);
            }
            return response()->json(['success' => true, 'message' => __("Co Pocket imported successfully")], 200);
        }
        return response()->json(['success' => false, 'message' => __("Key can't be empty")], 400);
    }

    // wallet details
    public function walletDetails(Request $request, $id)
    {

        $tab = $request->has('ac_tab') ? $request->ac_tab : null;

            $wallet = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where(['wallets.user_id' => Auth::id(), 'coins.status' => STATUS_ACTIVE, 'wallets.id' => $id])
            ->select('wallets.*', 'coins.status as coin_status', 'coins.is_withdrawal', 'coins.minimum_withdrawal',
                'coins.maximum_withdrawal', 'coins.withdrawal_fees')
            ->first();

        // Check if it's a co-wallet
        if (co_wallet_feature_active() && empty($wallet)) {
            $wallet = Wallet::select('wallets.*')
                ->join('wallet_co_users', 'wallet_co_users.wallet_id', '=', 'wallets.id')
                ->join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->where(['wallets.id' => $id, 'wallets.type' => CO_WALLET, 'wallet_co_users.user_id' => Auth::id(), 'coins.status' => STATUS_ACTIVE])
                ->first();

            if (!$wallet) {
                return response()->json(['success' => false, 'message' => __("Wallet not found or access denied.")], 400);
            }
        }

        if (empty($wallet)) {
            return response()->json(['success' => false, 'message' => __("Wallet not found.")], 400);
        }

        $data = [];
        $data['histories'] = [];
        if($tab && $tab == 'deposits'){
            $history = DepositeTransaction::where('receiver_wallet_id', $id)->orderBy('id', 'desc');
        }elseif ($tab && $tab == 'withdraws'){
            $history = WithdrawHistory::where('wallet_id', $id)->orderBy('id', 'desc');
        }else {
            $exists = WalletAddressHistory::where('wallet_id',$id)->orderBy('created_at','desc')->first();
            $wal = Wallet::find($id);
            if ($wal->coin_type == DEFAULT_COIN_TYPE) {
                $repo = new WalletRepository();
                $repo->generateTokenAddress($wallet->id);
                $data['address'] = WalletAddressHistory::where('wallet_id', $id)->orderBy('created_at', 'desc')->first()->address ?? '';
            }else{
                $data['address'] = (!empty($exists)) ? $exists->address : get_coin_payment_address($wallet->coin_type);
                if (!empty($data['address'])) {
                    if (empty($exists)) {
                        $walletService = new \App\Services\wallet();
                        $walletService->AddWalletAddressHistory($id, $data['address'], $wallet->coin_type);
                    }
                    $data['address'] = WalletAddressHistory::where('wallet_id', $id)->first()->address ?? '' ;
                }
            }
        }
        if (isset($history)){
            $items = $this->applyFiltersAndSorting($history, $request);
            $records = $items->getCollection()->transform(function ($item) {
                $item->deposit_status = deposit_status($item->status);
//                $item->requested_amount = $item->requested_amount.' '.check_default_coin_type($item->from_coin_type);
//                $item->converted_amount = $item->converted_amount . ' ' . check_default_coin_type($item->to_coin_type);
                unset($item->status);
                return $item;
            });
            $data['histories'] = $records;
            $data['recordsTotal'] = $items->total();
            $data['recordsFiltered'] = $items->total();
            $data['draw'] = $request->input('draw');
        }

        $data = array_merge($data, [
            'wallet_id' => $id,
            'wallet' => $wallet,
            'tempWithdraws' => co_wallet_feature_active() ? TempWithdraw::where(['wallet_id' => $id, 'status' => STATUS_PENDING])->orderBy('id', 'desc')->get() : [],
            'active' => $tab,
            'ac_tab' => $tab,
            'title' => $tab,
            '2fa_enabled' => Auth::user()->google2fa_secret ? true : false,
        ]);

        return response()->json($data);
    }

    public function generateNewAddress(Request $request)
    {
        try {
            $walletService = new \App\Services\wallet();
            $myWallet = Wallet::where(['id' => $request->wallet_id, 'user_id' => Auth::id()])->first();

            if (!$myWallet) {
                return response()->json(['success' => false, 'message' => __("Wallet not found.")], 400);
            }

            if ($myWallet->coin_type == DEFAULT_COIN_TYPE) {
                $repo = new WalletRepository();
                $response = $repo->generateTokenAddress($myWallet->id);
                return response()->json(['success' => $response['success'], 'message' => $response['message']], $response['success'] ? 200 : 400);

            } else {
                $address = get_coin_payment_address($myWallet->coin_type);
                if (!empty($address)) {
                    $walletService->AddWalletAddressHistory($request->wallet_id, $address, $myWallet->coin_type);
                    return response()->json(['success' => true, 'message' =>  __('Address generated successfully')]);
                } else {
                    return response()->json(['success' => false, 'message' => __('Address not generated')], 400);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);

        }
    }

    public function qrCodeGenerate(Request $request)
    {
        $image = QRCode::text($request->input('address'))->png();
        return response($image)->header('Content-Type', 'image/png');
    }

    // withdraw balance
    public function withdrawBalance(withDrawRequest $request)
    {
        $transactionService = new TransactionService();
        $wallet = Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where(['wallets.id' => $request->wallet_id, 'wallets.user_id' => Auth::id()])
            ->select('wallets.*', 'coins.status as coin_status', 'coins.is_withdrawal', 'coins.minimum_withdrawal',
                'coins.maximum_withdrawal', 'coins.withdrawal_fees')
            ->first();

        // Check if it's a co-wallet
        if (co_wallet_feature_active() && empty($wallet)) {
            $wallet = Wallet::join('wallet_co_users', 'wallet_co_users.wallet_id', '=', 'wallets.id')
                ->join('coins', 'coins.id', '=', 'wallets.coin_id')
                ->select('wallets.*', 'coins.status as coin_status', 'coins.is_withdrawal', 'coins.minimum_withdrawal',
                    'coins.maximum_withdrawal', 'coins.withdrawal_fees')
                ->where(['wallets.id' => $request->wallet_id, 'wallets.type' => CO_WALLET, 'wallet_co_users.user_id' => Auth::id()])
                ->first();
        }

        if (!$wallet) {
            return response()->json(['success' => false, 'message' => __('Pocket not found.')], 404);
        }

        if ($wallet->balance < $request->amount) {
            return response()->json(['success' => false, 'message' => __('Wallet has no enough balance')], 400);
        }

        $checkValidate = $transactionService->checkWithdrawalValidation($request, Auth::user(), $wallet);
        if (!$checkValidate['success']) {
            return response()->json(['success' => false, 'message' => $checkValidate['message']], 400);
        }

        $checkKyc = $transactionService->kycValidationCheck(Auth::user()->id);
        if (!$checkKyc['success']) {
            return response()->json(['success' => false, 'message' => $checkKyc['message']], 400);
        }

//        $google2fa = new Google2FA();
//        if (empty($request->code)) {
//            return response()->json(['success' => false, 'message' => __('Verify code is required')], 300);
//        }
//
//        $valid = $google2fa->verifyKey(Auth::user()->google2fa_secret, $request->code);
//
//        if (!$valid) {
//            return response()->json(['success' => false, 'message' => __('Google two-factor authentication is invalid')], 300);
//        }

        try {
            if ($wallet->type == PERSONAL_WALLET) {
                dispatch(new Withdrawal($request->all()))->onQueue('withdrawal');
                return response()->json(['success' => true, 'message' => __('Withdrawal placed successfully')], 200);
            } elseif (co_wallet_feature_active() && $wallet->type == CO_WALLET) {
                DB::beginTransaction();
                $tempWithdraw = TempWithdraw::create([
                    'user_id' => Auth::user()->id,
                    'wallet_id' => $wallet->id,
                    'amount' => $request->amount,
                    'address' => $request->address,
                    'message' => $request->message
                ]);

                CoWalletWithdrawApproval::create([
                    'temp_withdraw_id' => $tempWithdraw->id,
                    'wallet_id' => $wallet->id,
                    'user_id' => Auth::user()->id
                ]);
                DB::commit();

                if ($transactionService->isAllApprovalDoneForCoWalletWithdraw($tempWithdraw)['success']) {
                    dispatch(new Withdrawal($tempWithdraw->toArray()))->onQueue('withdrawal');
                    return response()->json(['success' => true, 'message' => __('Withdrawal placed successfully')], 200);
                }
                return response()->json(['success' => true, 'message' => __('Process successful. Need other co-users approval.')], 200);
            } else {
                return response()->json(['success' => true, 'message' => __('Invalid Pocket type.')], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => __('Something went wrong.')], 400);
        }
    }

    //check internal address
    private function isInternalAddress($address)
    {
        $record = WalletAddressHistory::where('address', $address)->with('wallet')->first();
        return response()->json($record ? $record : ['error' => 'Address not found.'], $record ? 200 : 404);
    }

    // transaction history
    public function transactionHistories(Request $request)
    {
        if ($request->ajax()) {
            $tr = new TransactionService();
            $histories = $request->type == 'deposit'
                ? $tr->depositTransactionHistories(Auth::id())->get()
                : $tr->withdrawTransactionHistories(Auth::id())->get();

            return datatables($histories)
                ->addColumn('address', function ($item) {
                    return $item->address;
                })
                ->addColumn('amount', function ($item) {
                    return $item->amount;
                })
                ->addColumn('hashKey', function ($item) use ($request) {
                    return $request->type == 'deposit' ? $item->transaction_id : $item->transaction_hash;
                })
                ->addColumn('status', function ($item) {
                    return statusAction($item->status);
                })
                ->rawColumns(['user'])
                ->make(true);
        }
    }

    // withdraw rate
    public function withdrawCoinRate(Request $request)
    {
        if ($request->ajax()) {
            $amount = $request->amount ?? 0;
            $wallet = Wallet::find($request->wallet_id);
            $coinType = $wallet->coin_type;

            $coinPrice = bcmul(settings('coin_price'), $amount, 8);
            $coinpayment = new CoinPaymentsAPI();
            $apiRate = $coinpayment->GetRates('');

            $btcDlr = converts_currency($coinPrice, $coinType, $apiRate);
            $btcDlr = custom_number_format($btcDlr);

            return response()->json([
                'amount' => $amount,
                'coin_type' => $coinType,
                'coin_price' => $coinPrice,
                'btc_dlr' => $btcDlr
            ]);
        }
    }

    // coin swap history
    public function coinSwapHistory(Request $request)
    {
        $query = WalletSwapHistory::where(['user_id' => Auth::id()]);
        $items = $this->applyFiltersAndSorting($query, $request);
        $data = $items->getCollection()->transform(function ($item) {
            $item->from_wallet_id = $item->fromWallet->name;
            $item->to_wallet_id = $item->toWallet->name;
            $item->requested_amount = $item->requested_amount.' '.check_default_coin_type($item->from_coin_type);
            $item->converted_amount = $item->converted_amount . ' ' . check_default_coin_type($item->to_coin_type);
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


    // coin swap
    public function coinSwap()
    {
        $data['wallets'] = Wallet::where(['user_id' => Auth::id()])->where('coin_type', '<>', DEFAULT_COIN_TYPE)->get();

        return response()->json([
            'title' => __('Coin Swap'),
            'wallets' => $data['wallets']
        ]);
    }


    //co wallet users
    public function coWalletUsers(Request $request)
    {
        $wallet = Wallet::select('wallets.*')
            ->join('wallet_co_users', 'wallet_co_users.wallet_id', '=', 'wallets.id')
            ->where(['wallets.id' => $request->id, 'wallets.type' => CO_WALLET, 'wallet_co_users.user_id' => Auth::id()])
            ->first();

        if (empty($wallet)) {
            return response()->json(['error' => __('Wallet not found.')], 404);
        }

        return response()->json([
            'title' => __('Co Pocket Users'),
            'co_users' => $wallet->co_users
        ]);
    }

    //co wallet withdraw approval list
    public function coWalletApprovals(Request $request)
    {
        $tempWithdraw = TempWithdraw::where(['status' => STATUS_PENDING, 'id' => $request->id])->first();
        if (empty($tempWithdraw)) {
            return response()->json(['error' => __('Temp withdraw not found.')], 404);
        }

        $response = (new TransactionService())->approvalCounts($tempWithdraw);

        $wallet = Wallet::select('wallets.*')
            ->join('wallet_co_users', 'wallet_co_users.wallet_id', '=', 'wallets.id')
            ->where(['wallets.id' => $tempWithdraw->wallet_id, 'wallets.type' => CO_WALLET, 'wallet_co_users.user_id' => Auth::id()])
            ->first();
        if (empty($wallet)) {
            return response()->json(['error' => __('Wallet not found.')], 404);
        }

        $co_users = WalletCoUser::select(DB::raw('wallet_co_users.*,
        (CASE WHEN wallet_co_users.user_id=co_wallet_withdraw_approvals.user_id THEN ' . STATUS_ACCEPTED . ' ELSE ' . STATUS_PENDING . ' END) approved'))
            ->leftJoin('co_wallet_withdraw_approvals', function ($join) use ($tempWithdraw) {
                $join->on('wallet_co_users.wallet_id', '=', 'co_wallet_withdraw_approvals.wallet_id')
                    ->on('wallet_co_users.user_id', '=', 'co_wallet_withdraw_approvals.user_id')
                    ->on('co_wallet_withdraw_approvals.temp_withdraw_id', '=', DB::raw($tempWithdraw->id));
            })
            ->where('wallet_co_users.wallet_id', $wallet->id)
            ->get();

        return response()->json([
            'title' => __('Withdraw Approvals'),
            'total_required_approval' => $response['requiredUserApprovalCount'],
            'approved_count' => $response['alreadyApprovedUserCount'],
            'co_users' => $co_users
        ]);
    }

    //approve co wallet withdraw
    public function approveCoWalletWithdraw(Request $request)
    {
        $tempWithdraw = TempWithdraw::where(['status' => STATUS_PENDING, 'id' => $request->id])->first();
        if (empty($tempWithdraw)) {
            return response()->json(['error' => __('Invalid withdrawal.')], 404);
        }

        $userAlreadyApproved = CoWalletWithdrawApproval::where(['temp_withdraw_id' => $tempWithdraw->id, 'user_id' => Auth::id()])->first();
        if (!empty($userAlreadyApproved)) {
            return response()->json(['error' => __('You already approved.')], 400);
        }

        $wallet = Wallet::select('wallets.*')
            ->join('wallet_co_users', 'wallet_co_users.wallet_id', '=', 'wallets.id')
            ->where(['wallets.id' => $tempWithdraw->wallet_id, 'wallets.type' => CO_WALLET, 'wallet_co_users.user_id' => Auth::id()])
            ->first();
        if (empty($wallet)) {
            return response()->json(['error' => __('Invalid wallet.')], 404);
        }

        try {
            CoWalletWithdrawApproval::create([
                'temp_withdraw_id' => $tempWithdraw->id,
                'wallet_id' => $wallet->id,
                'user_id' => Auth::id()
            ]);

            $transactionService = new TransactionService();
            if ($transactionService->isAllApprovalDoneForCoWalletWithdraw($tempWithdraw)['success']) {
                dispatch(new Withdrawal($tempWithdraw->toArray()))->onQueue('withdrawal');
                return response()->json(['success' => __('All approval done and withdrawal placed successfully.')]);
            } else {
                return response()->json(['success' => __('Approved successfully.')]);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => __('Something went wrong.')], 500);
        }
    }

    //reject co wallet withdraw by withdraw requester
    public function rejectCoWalletWithdraw(Request $request)
    {
        $tempWithdraw = TempWithdraw::where(['status' => STATUS_PENDING, 'id' => $request->id, 'user_id' => Auth::id()])->first();
        if (empty($tempWithdraw)) {
            return response()->json(['error' => __('Invalid withdrawal.')], 404);
        }

        try {
            $tempWithdraw->status = STATUS_REJECTED;
            $tempWithdraw->save();
            return response()->json(['success' => __('Withdraw rejected successfully.')]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => __('Something went wrong.')], 500);
        }
    }


}
