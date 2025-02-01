<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Http\Services\CommonService;
use App\Model\BuyCoinHistory;
use App\Model\DepositeTransaction;
use App\Model\Faq;
use App\Model\MembershipClub;
use App\Model\Notification;
use App\Model\WithdrawHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    public function userDashboard(Request $request)
    {
        $data = [];
        $data['title'] = __('Dashboard');
        $data['balance'] = getUserBalance(Auth::id());
        $data['total_buy_coin'] = BuyCoinHistory::where(['user_id' => Auth::id(), 'status' => STATUS_ACTIVE])->sum('coin');
        $data['blocked_coin'] = 0;
        $membership = MembershipClub::where(['user_id' => Auth::id(), 'status' => STATUS_ACTIVE])->first();
        if (isset($membership)) {
            $data['blocked_coin'] = $membership->amount;
        }
        $from = Carbon::now()->subMonth(6)->format('Y-m-d h:i:s');
        $to = Carbon::now()->format('Y-m-d h:i:s');

        $common_service = new CommonService();

        if (!$request->ajax()) {
            $sixmonth_diposites = $common_service->AuthUserDiposit($from, $to);
            $sixmonth_withdraws = $common_service->AuthUserWithdraw($from, $to);

            $data['sixmonth_diposites'] = [];
            $months = previousMonthName(5);
            $data['last_six_month'] = $months;

            foreach ($months as $key => $month) {
                $data['sixmonth_diposites'][$key]['country'] = $month;
                $data['sixmonth_diposites'][$key]['year2004'] = $sixmonth_diposites[$month] ?? 0;
                $data['sixmonth_diposites'][$key]['year2005'] = $sixmonth_withdraws[$month] ?? 0;
            }
        }

        $data['completed_withdraw'] = WithdrawHistory::join('wallets', 'wallets.id', 'withdraw_histories.wallet_id')
            ->where('withdraw_histories.status', STATUS_SUCCESS)
            ->where('wallets.user_id', Auth::id())
            ->sum('withdraw_histories.amount');

        $data['pending_withdraw'] = WithdrawHistory::join('wallets', 'wallets.id', 'withdraw_histories.wallet_id')
            ->where('withdraw_histories.status', STATUS_PENDING)
            ->where('wallets.user_id', Auth::id())
            ->sum('withdraw_histories.amount');

        $allMonths = all_months();

        $monthlyDeposits = DepositeTransaction::join('wallets', 'wallets.id', 'deposite_transactions.receiver_wallet_id')
            ->where('wallets.user_id', Auth::id())
            ->select(DB::raw('sum(deposite_transactions.amount) as totalDepo'), DB::raw('MONTH(deposite_transactions.created_at) as months'))
            ->whereYear('deposite_transactions.created_at', Carbon::now()->year)
            ->where('deposite_transactions.status', STATUS_SUCCESS)
            ->groupBy('months')
            ->get();

        $data['deposit'] = [];
        foreach ($monthlyDeposits as $deposit) {
            $data['deposit'][$deposit->months] = $deposit->totalDepo;
        }

        $data['monthly_deposit'] = array_map(function ($month) use ($data) {
            return $data['deposit'][$month] ?? 0;
        }, $allMonths);

        $monthlyWithdrawals = WithdrawHistory::join('wallets', 'wallets.id', 'withdraw_histories.wallet_id')
            ->select(DB::raw('sum(withdraw_histories.amount) as totalWithdraw'), DB::raw('MONTH(withdraw_histories.created_at) as months'))
            ->whereYear('withdraw_histories.created_at', Carbon::now()->year)
            ->where('withdraw_histories.status', STATUS_SUCCESS)
            ->groupBy('months')
            ->get();

        $data['withdrawal'] = [];
        foreach ($monthlyWithdrawals as $withdraw) {
            $data['withdrawal'][$withdraw->months] = $withdraw->totalWithdraw;
        }

        $data['monthly_withdrawal'] = array_map(function ($month) use ($data) {
            return $data['withdrawal'][$month] ?? 0;
        }, $allMonths);

        $monthlyBuyCoins = BuyCoinHistory::select(DB::raw('sum(coin) as totalCoin'), DB::raw('MONTH(created_at) as months'))
            ->whereYear('created_at', Carbon::now()->year)
            ->where(['user_id' => Auth::id(), 'status' => STATUS_SUCCESS])
            ->groupBy('months')
            ->get();

        $data['coin'] = [];
        foreach ($monthlyBuyCoins as $coin) {
            $data['coin'][$coin->months] = $coin->totalCoin;
        }

        $data['monthly_buy_coin'] = array_map(function ($month) use ($data) {
            return $data['coin'][$month] ?? 0;
        }, $allMonths);

        return response()->json($data);
    }
    // user faq list
    public function userFaq()
    {
        $data['title'] = __('FAQ');
        $data['items'] = Faq::where('status', STATUS_ACTIVE)->get();

        return response()->json($data);
    }


    // show notification
    public function showNotification(Request $request)
    {
        $notification = Notification::where('id', $request->id)->first();
        if (!$notification) {
            return response()->json(['success' => false, 'message' => __('Notification not found.')], 404);
        }

        $data = [
            'title' => $notification->title,
            'notice' => $notification->notification_body,
            'date' => date('d M y', strtotime($notification->created_at)),
        ];

        $notification->update(['status' => 1]);

        $notifications = Notification::where(['user_id' => $notification->user_id, 'status' => STATUS_PENDING])
            ->orderBy('id', 'desc')
            ->get();

        $data['notifications'] = $notifications;

        return response()->json(['data' => $data]);
    }



    // get notification
    public function getNotification(Request $request)
    {
        $notifications = Notification::where(['user_id' => $request->user_id, 'status' => STATUS_PENDING])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['notifications' => $notifications]);
    }

}
