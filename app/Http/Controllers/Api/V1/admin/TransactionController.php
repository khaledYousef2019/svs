<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Jobs\DistributeWithdrawalReferralBonus;
use App\Jobs\PendingDepositAcceptJob;
use App\Jobs\PendingDepositRejectJob;
use App\Model\AdminReceiveTokenTransactionHistory;
use App\Model\CoinRequest;
use App\Model\DepositeTransaction;
use App\Model\EstimateGasFeesTransactionHistory;
use App\Model\Wallet;
use App\Model\WithdrawHistory;
use App\Services\CoinPaymentsAPI;
use App\Services\ERC20TokenApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    // all personal wallet list
    public function adminWalletList(Request $request)
    {
        $query =  Wallet::join('users', 'users.id', '=', 'wallets.user_id')
            ->join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where(['wallets.type' => PERSONAL_WALLET, 'coins.status' => STATUS_ACTIVE])
            ->select(
                'wallets.name',
                'wallets.coin_type',
                'wallets.balance',
                'wallets.referral_balance',
                'wallets.created_at',
                'users.first_name',
                'users.last_name',
                'users.email'
            );
        $fieldTableMap = [
            'name' => 'wallets',
            'first_name.last_name' => 'users',
        ];
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);
        $data = $items->getCollection()->transform(function ($item) {
            $item->coin_type = check_default_coin_type($item->coin_type);
            return $item;
        });

        // Return JSON response for React
        return response()->json([
            'title' => __('Pocket List'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
    }

    // all co wallet list
    public function adminCoWallets(Request $request)
    {
        $query =  Wallet::join('coins', 'coins.id', '=', 'wallets.coin_id')
            ->where(['wallets.type' => CO_WALLET, 'coins.status' => STATUS_ACTIVE])
            ->select('wallets.id','wallets.name','wallets.coin_type','wallets.balance','wallets.updated_at','wallets.created_at');
        $fieldTableMap = [
            'type' => 'wallets',
            'status' => 'coins',
            'created_at' => 'wallets',
            'updated_at' => 'wallets',
            'name' => 'wallets',
            'coin_type' => 'wallets',
        ];
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);
        $data = $items->getCollection()->transform(function ($item) {
            $item->coin_type = check_default_coin_type($item->coin_type);
            $item->action = new \stdClass();
            $item->action->Co_Users = route('adminCoWalletUsers', $item->id);
            return $item;
        });

        // Return JSON response for React
        return response()->json([
            'title' => __('Pocket List'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
    }

    // co wallet users
    public function adminCoWalletUsers(Request $request)
    {
        $wallet = Wallet::where(['id' => $request->id, 'type' => CO_WALLET])->first();
        if (empty($wallet)) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }
        $co_users = $wallet->co_users;

        return response()->json([
            'title' => __('Co Pocket Users'),
            'co_users' => $co_users
        ]);
    }


    //admin Default Coin  transaction  history
    public function adminDefaultCoinTransactionHistory(Request $request)
    {
        $query =  CoinRequest::select('*');
        $fieldTableMap = [
            'sender_user_id' => ['sender' => 'first_name'],
            'receiver_user_id' => ['receiver' => 'first_name'],
        ];
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);
        $data = $items->getCollection()->transform(function ($item) {
            $item->deposit_status = deposit_status($item->status);
            $item->sender_user_id = isset($transaction->sender) ? $transaction->sender->first_name . ' ' . $transaction->sender->last_name : 'N/A';
            $item->receiver_user_id = isset($transaction->receiver) ? $transaction->receiver->first_name . ' ' . $transaction->receiver->last_name : 'N/A';
            return $item;
        });

        // Return JSON response for React
        return response()->json([
            'title' => __('Pocket List'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);

    }
    // transaction  history
    public function adminTransactionHistory(Request $request)
    {
        $query =  DepositeTransaction::select(
            'deposite_transactions.address',
            'deposite_transactions.amount',
            'deposite_transactions.fees',
            'deposite_transactions.transaction_id',
            'deposite_transactions.confirmations',
            'deposite_transactions.address_type as addr_type',
            'deposite_transactions.created_at',
            'deposite_transactions.sender_wallet_id',
            'deposite_transactions.receiver_wallet_id',
            'deposite_transactions.status',
            'deposite_transactions.type'
        );

        $fieldTableMap = [
            'sender' => ['sender' => 'first_name'],
            'receiver' => ['receiver' => 'first_name'],
            'address' => 'deposite_transactions'
        ];
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);
        $data = $items->getCollection()->transform(function ($item) {
            return [
                'address' => $item->address,
                'amount' => $item->amount,
                'fees' => $item->fees,
                'deposit_status' => deposit_status($item->status),
                'address_type' => $item->addr_type == 'internal_address' ? 'External' : addressType($item->addr_type),
                'type' => find_coin_type($item->type),
                'sender' => !empty($item->senderWallet) && $item->senderWallet->type == CO_WALLET
                    ? 'Multi-signature Pocket: ' . $item->senderWallet->name
                    : (isset($item->senderWallet->user) ? $item->senderWallet->user->first_name . ' ' . $item->senderWallet->user->last_name : 'N/A'),
                'receiver' => !empty($item->receiverWallet) && $item->receiverWallet->type == CO_WALLET
                    ? 'Multi-signature Pocket: ' . $item->receiverWallet->name
                    : (isset($item->receiverWallet->user) ? $item->receiverWallet->user->first_name . ' ' . $item->receiverWallet->user->last_name : 'N/A'),
                'transaction_id' => $item->transaction_id,
                'created_at' => $item->created_at,
            ];
        });

        // Return JSON response for React
        return response()->json([
            'title' => __('All Deposit History'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
      }
    // withdrawal history
    public function adminWithdrawalHistory(Request $request)
    {
            $query = WithdrawHistory::select(
                'withdraw_histories.address',
                'withdraw_histories.amount',
                'withdraw_histories.user_id',
                'withdraw_histories.fees',
                'withdraw_histories.transaction_hash',
                'withdraw_histories.confirmations',
                'withdraw_histories.address_type as addr_type',
                'withdraw_histories.created_at',
                'withdraw_histories.wallet_id',
                'withdraw_histories.coin_type',
                'withdraw_histories.receiver_wallet_id',
                'withdraw_histories.status'
            )->orderBy('withdraw_histories.id', 'desc');


            $fieldTableMap = [
                'sender' => ['sender' => 'first_name'],
                'receiver' => ['receiver' => 'first_name'],
                'status' => 'withdraw_histories'
            ];
            $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);
            $data = $items->getCollection()->transform(function ($wdrl) {
                return [
                    'address' => $wdrl->address,
                    'amount' => $wdrl->amount,
                    'fees' => $wdrl->fees,
                    'deposit_status' => deposit_status($wdrl->status),
                    'address_type' =>  addressType($wdrl->addr_type),
                    'coin_type' => find_coin_type($wdrl->type),
                    'sender' =>$wdrl->user ?? ($wdrl->senderWallet->user ?? null)
                        ? ($wdrl->user ?? ($wdrl->senderWallet->user))->first_name . ' ' . ($wdrl->user ?? ($wdrl->senderWallet->user))->last_name
                        : 'N/A',
                    'receiver' =>$wdrl->user ?? ($wdrl->receiverWallet->user ?? null)
                        ? ($wdrl->user ?? ($wdrl->receiverWallet->user))->first_name . ' ' . ($wdrl->user ?? ($wdrl->receiverWallet->user))->last_name
                        : 'N/A',
                    'transaction_hash' => $wdrl->transaction_hash,
                    'confirmations' => $wdrl->confirmations,
                    'created_at' => $wdrl->created_at->toDateTimeString(),
                ];
            });

            // Return JSON response for React
            return response()->json([
                'title' => __('All Deposit History'),
                'data' => $data,
                'recordsTotal' => $items->total(),
                'recordsFiltered' => $items->total(), // Adjust if there are filters
                'draw' => $request->input('draw'), // Include draw for DataTables
            ]);
    }

    // pending withdrawal list
    public function adminPendingWithdrawal(Request $request)
    {
        $data['title'] = __('Pending Withdrawal');
        $query = WithdrawHistory::select(
                'withdraw_histories.id',
                'withdraw_histories.address',
                'withdraw_histories.amount',
                'withdraw_histories.user_id',
                'withdraw_histories.fees',
                'withdraw_histories.status',
                'withdraw_histories.transaction_hash',
                'withdraw_histories.confirmations',
                'withdraw_histories.address_type as addr_type',
                'withdraw_histories.updated_at',
                'withdraw_histories.wallet_id',
                'withdraw_histories.coin_type',
                'withdraw_histories.receiver_wallet_id'
            );

        $fieldTableMap = [
            'sender' => ['sender' => 'first_name'],
            'receiver' => ['receiver' => 'first_name'],
            'address' => 'deposite_transactions'
        ];
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);
        $data = $items->getCollection()->transform(function ($item) {
            $item->address_type = addressType($item->addr_type);
            $item->coin_type = find_coin_type($item->coin_type);
            $item->deposit_status = deposit_status($item->status);

            $item->sender = $item->user ?? ($item->senderWallet->user ?? null)
                ? ($item->user ?? ($item->senderWallet->user))->first_name . ' ' . ($item->user ?? ($item->senderWallet->user))->last_name
                : 'N/A';

            $item->receiver = $item->user ?? ($item->receiverWallet->user ?? null)
                ? ($item->user ?? ($item->receiverWallet->user))->first_name . ' ' . ($item->user ?? ($item->receiverWallet->user))->last_name
                : 'N/A';
            $item->action = new \stdClass();
            $item->action->Accept = route('adminAcceptPendingWithdrawal',encrypt($item->id));
            $item->action->Reject    = route('adminRejectPendingWithdrawal',encrypt($item->id));

            unset($item->status);
            unset($item->receiverWallet);
            unset($item->senderWallet); // Unset inside the closure

            return $item;
        });

        // Return JSON response for React
        return response()->json([
            'title' => __('All Deposit And Withdrawal History'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
//
//            return response()->json([
//                'data' => datatables()->of($withdrawal)
//                    ->addColumn('address_type', fn($item) => addressType($wdrl->addr_type))
//                    ->addColumn('coin_type', fn($wdrl) => find_coin_type($wdrl->coin_type))
//                    ->addColumn('sender', function ($wdrl) {
//                        $user = $wdrl->user ?? ($wdrl->senderWallet->user ?? null);
//                        return $user ? $user->first_name . ' ' . $user->last_name : 'N/A';
//                    })
//                    ->addColumn('receiver', function ($wdrl) {
//                        if (!empty($wdrl->receiverWallet)) {
//                            return $wdrl->receiverWallet->type == CO_WALLET
//                                ? 'Multi-signature Pocket: ' . $wdrl->receiverWallet->name
//                                : $wdrl->receiverWallet->user->first_name . ' ' . $wdrl->receiverWallet->user->last_name;
//                        }
//                        return 'N/A';
//                    })
//                    ->addColumn('actions', function ($wdrl) {
//                        return [
//                            'accept' => route('adminAcceptPendingWithdrawal', encrypt($wdrl->id)),
//                            'reject' => route('adminRejectPendingWithdrawal', encrypt($wdrl->id))
//                        ];
//                    })
//                    ->make(true)
//            ]);

    }

    // rejected withdrawal list
    public function adminRejectedWithdrawal(Request $request)
    {
        $data['title'] = __('Rejected Withdrawal');
        if ($request->ajax()) {
            $withdrawal = WithdrawHistory::select(
                'withdraw_histories.address',
                'withdraw_histories.amount',
                'withdraw_histories.user_id',
                'withdraw_histories.fees',
                'withdraw_histories.transaction_hash',
                'withdraw_histories.confirmations',
                'withdraw_histories.address_type as addr_type',
                'withdraw_histories.updated_at',
                'withdraw_histories.wallet_id',
                'withdraw_histories.coin_type',
                'withdraw_histories.receiver_wallet_id'
            )->where(['withdraw_histories.status' => STATUS_REJECTED])
                ->orderBy('withdraw_histories.id', 'desc');

            return response()->json([
                'data' => datatables()->of($withdrawal)
                    ->addColumn('address_type', fn($wdrl) => addressType($wdrl->addr_type))
                    ->addColumn('coin_type', fn($wdrl) => find_coin_type($wdrl->coin_type))
                    ->addColumn('sender', function ($wdrl) {
                        $user = $wdrl->user ?? ($wdrl->senderWallet->user ?? null);
                        return $user ? $user->first_name . ' ' . $user->last_name : 'N/A';
                    })
                    ->addColumn('receiver', function ($wdrl) {
                        if (!empty($wdrl->receiverWallet)) {
                            return $wdrl->receiverWallet->type == CO_WALLET
                                ? 'Multi-signature Pocket: ' . $wdrl->receiverWallet->name
                                : $wdrl->receiverWallet->user->first_name . ' ' . $wdrl->receiverWallet->user->last_name;
                        }
                        return 'N/A';
                    })
                    ->make(true)
            ]);
        }

        return response()->json(['message' => 'This endpoint is for AJAX requests only']);
    }

    // active withdrawal list
    public function adminActiveWithdrawal(Request $request)
    {
        if ($request->ajax()) {
            $withdrawal = WithdrawHistory::select(
                'withdraw_histories.address',
                'withdraw_histories.amount',
                'withdraw_histories.user_id',
                'withdraw_histories.fees',
                'withdraw_histories.transaction_hash',
                'withdraw_histories.confirmations',
                'withdraw_histories.address_type as addr_type',
                'withdraw_histories.updated_at',
                'withdraw_histories.wallet_id',
                'withdraw_histories.coin_type',
                'withdraw_histories.receiver_wallet_id'
            )->where(['withdraw_histories.status' => STATUS_SUCCESS])
                ->orderBy('withdraw_histories.id', 'desc');

            return response()->json([
                'data' => datatables()->of($withdrawal)
                    ->addColumn('address_type', function ($wdrl) {
                        return addressType($wdrl->addr_type);
                    })
                    ->addColumn('coin_type', function ($wdrl) {
                        return find_coin_type($wdrl->coin_type);
                    })
                    ->addColumn('sender', function ($wdrl) {
                        if (!empty($wdrl->user)) $user = $wdrl->user;
                        else $user = isset($wdrl->senderWallet) ? $wdrl->senderWallet->user : null;
                        return isset($user) ? $user->first_name . ' ' . $user->last_name : 'N/A';
                    })
                    ->addColumn('receiver', function ($wdrl) {
                        if (!empty($wdrl->receiverWallet) && $wdrl->receiverWallet->type == CO_WALLET) return 'Multi-signature Pocket: ' . $wdrl->receiverWallet->name;
                        else
                            return isset($wdrl->receiverWallet->user) ? $wdrl->receiverWallet->user->first_name . ' ' . $wdrl->receiverWallet->user->last_name : 'N/A';
                    })
                    ->toJson()
            ]);
        }

        // Return a basic view if needed for initial page load
        return response()->json(['message' => 'Initial load']);
    }

    // accept process of pending withdrawal
    public function adminAcceptPendingWithdrawal(Request $request, $id)
    {
        try {
            $wdrl_id = decrypt($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $transaction = WithdrawHistory::with('wallet')->with('users')->where(['id' => $wdrl_id, 'status' => STATUS_PENDING])->first();

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        try {
            if ($transaction->address_type == ADDRESS_TYPE_INTERNAL) {
                $deposit = DepositeTransaction::where(['transaction_id' => $transaction->transaction_hash, 'address' => $transaction->address])->update(['status' => STATUS_SUCCESS]);
                Wallet::where(['id' => $transaction->receiver_wallet_id])->increment('balance', $transaction->amount);
                $transaction->status = STATUS_SUCCESS;
                $transaction->save();
                if($transaction){
                    sendTransactionEmail('transactions.withdraw-confirmed',$transaction);
                }
            } elseif ($transaction->address_type == ADDRESS_TYPE_EXTERNAL) {
                // Handle external transactions
                if ($transaction->coin_type == DEFAULT_COIN_TYPE) {
//                    $settings = allsetting();
//                    $coinApi = new ERC20TokenApi();
//                    $requestData = [
//                        "amount_value" => (float)$transaction->amount,
//                        "from_address" => $settings['wallet_address'] ?? '',
//                        "to_address" => $transaction->address,
//                        "contracts" => $settings['private_key'] ?? ''
//                    ];
//                    $result = $coinApi->sendCustomToken($requestData);
//                    if ($result['success'] == true) {
//                        $transaction->transaction_hash = $result['data']->hash;
//                        $transaction->used_gas = $result['data']->used_gas;
                        $transaction->status = STATUS_SUCCESS;
                        $transaction->update();
                        if($transaction->wasChanged()){
                            sendTransactionEmail('transactions.withdraw-confirmed',$transaction);
                        }
                        dispatch(new DistributeWithdrawalReferralBonus($transaction))->onQueue('referral');
//                    } else {
//                        return response()->json(['error' => $result['message']], 400);
//                    }
                } else {
                    $currency = $transaction->coin_type;
                    $coinpayment = new CoinPaymentsAPI();
                    $response = $coinpayment->CreateWithdrawal($transaction->amount, $currency, $transaction->address);
                    if (is_array($response) && isset($response['error']) && ($response['error'] == 'ok')) {
                        $transaction->transaction_hash = $response['result']['id'];
                        $transaction->status = STATUS_SUCCESS;
                        $transaction->update();
                        if($transaction->wasChanged()){
                            sendTransactionEmail('transactions.withdraw-confirmed',$transaction);
                        }
                        dispatch(new DistributeWithdrawalReferralBonus($transaction))->onQueue('referral');
                    } else {
                        return response()->json(['error' => $response['error']], 400);
                    }
                }
            }
            return response()->json(['success' => 'Pending withdrawal accepted Successfully.']);
        } catch (\Exception $e) {
            Log::error('adminAcceptPendingWithdrawal Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // pending withdrawal reject process
    public function adminRejectPendingWithdrawal(Request $request, $id)
    {
        try {
            $wdrl_id = decrypt($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $transaction = WithdrawHistory::where(['id' => $wdrl_id, 'status' => STATUS_PENDING])->first();

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        try {
            if ($transaction->address_type == ADDRESS_TYPE_INTERNAL) {
                Wallet::where(['id' => $transaction->wallet_id])->increment('balance', $transaction->amount);
                $transaction->status = STATUS_REJECTED;
                $transaction->update();
                $deposit = DepositeTransaction::where(['transaction_id' => $transaction->transaction_hash, 'address' => $transaction->address])->update(['status' => STATUS_REJECTED]);
            } elseif ($transaction->address_type == ADDRESS_TYPE_EXTERNAL) {
                $amount = $transaction->amount + $transaction->fees;
                Wallet::where(['id' => $transaction->wallet_id])->increment('balance', $amount);
                $transaction->status = STATUS_REJECTED;
                $transaction->update();
            }
            if($transaction->wasChanged()){
                sendTransactionEmail('transactions.withdraw-rejected',$transaction);
            }
            return response()->json(['success' => 'Pending withdrawal rejected Successfully.']);
        } catch (\Exception $e) {
            Log::error('adminRejectPendingWithdrawal Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // gas send history
    public function adminGasSendHistory(Request $request)
    {
        $data['title'] = __('Pending Withdrawal');
        $query = EstimateGasFeesTransactionHistory::select('*');

        $fieldTableMap = [
            'sender' => ['sender' => 'first_name'],
            'receiver' => ['receiver' => 'first_name'],
            'address' => 'deposite_transactions'
        ];
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);
        $data = $items->getCollection()->transform(function ($item) {
            $item->created_at = $item->created_at->toDateTimeString();
            $item->deposit_status = deposit_status($item->status);
            return $item;
        });
        return response()->json([
            'title' => __('All Deposit And Withdrawal History'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
    }

    // token receive history
    public function adminTokenReceiveHistory(Request $request)
    {
        $query = AdminReceiveTokenTransactionHistory::select('*');

        $fieldTableMap = [
            'sender' => ['sender' => 'first_name'],
            'receiver' => ['receiver' => 'first_name'],
            'address' => 'deposite_transactions'
        ];
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);
        $data = $items->getCollection()->transform(function ($item) {
            $item->created_at = $item->created_at->toDateTimeString();
            $item->deposit_status = deposit_status($item->status);
            return $item;
        });
        return response()->json([
            'title' => __('All Deposit And Withdrawal History'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
    }

    // token pending deposit history
    public function adminPendingDepositHistory(Request $request)
    {
        $query =  DepositeTransaction::where(['address_type' => ADDRESS_TYPE_EXTERNAL, 'is_admin_receive' => STATUS_PENDING])
            ->where(['type' => DEFAULT_COIN_TYPE])
            ->select('*');
        $fieldTableMap = [
            'name' => 'wallets',
            'first_name' => 'users',
            'last_name' => 'users',
        ];
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);
        $data = $items->getCollection()->transform(function ($item) {
            $item->coin_type = check_default_coin_type($item->coin_type);
            $item->created_at = $item->created_at->toDateTimeString();
            $item->deposit_status = deposit_status($item->status);
            $item->action = new \stdClass();
            $item->action->Accept = route('adminPendingDepositAccept', encrypt($item->id));
            return $item;
        });

        // Return JSON response for React
        return response()->json([
            'title' => __('Pending Token Deposit History'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);

    }

    // pending deposit reject process
    public function adminPendingDepositReject(Request $request, $id)
    {
        try {
            $wdrl_id = decrypt($id);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $transaction = DepositeTransaction::where(['id' => $wdrl_id, 'status' => STATUS_PENDING, 'address_type' => ADDRESS_TYPE_EXTERNAL])->first();

        if (!$transaction) {
            return response()->json(['error' => 'Pending deposit not found'], 404);
        }

        dispatch(new PendingDepositRejectJob($transaction, Auth::id()))->onQueue('deposit');
        return response()->json(['success' => 'Pending deposit reject process goes to queue. Please wait some time']);
}


    // pending deposit accept process
    public function adminPendingDepositAccept(Request $request, $id)
    {
        try {
            $wdrl_id = decrypt($id);
        } catch (\Exception $e) {
            storeException('adminPendingDepositAccept decrypt', $e->getMessage());
            return response()->json(['error' => 'Invalid ID'], 400);
        }

        $transactions = DepositeTransaction::where(['id' => $wdrl_id, 'is_admin_receive' => STATUS_PENDING, 'address_type' => ADDRESS_TYPE_EXTERNAL])->first();

        if (!$transactions) {
            return response()->json(['error' => 'Pending deposit not found'], 404);
        }

        dispatch(new PendingDepositAcceptJob($transactions, Auth::id()))->onQueue('deposit');
        return response()->json(['success' => 'Pending deposit accept process goes to queue. Please wait some time']);
    }

}
