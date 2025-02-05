<?php

namespace App\Http\Controllers\Api\user;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Model\Wallet;
use App\Model\Plan;
use App\Model\UserPlans;
use App\Model\InvplanTransactions;
use App\Repository\PlanRepository;
use App\Services\MailService;
use Carbon\Carbon;

class PlanController extends Controller
{
    /**
     * Initialize product service
     *
     * ProductController constructor.
     */
    public function __construct()
    {
        // $this->clubRepo = new ClubRepository;
    }

    public function myInvestment()
    {
        try {

            $data = [];
            $data['plans'] = UserPlans::join('plans','user_plans.plan','=','plans.id')
            ->where('user', Auth::id())
            ->select('user_plans.*', 'plans.name as name')->get();

          $data['transactions'] = InvplanTransactions::join('user_plans', 'user_plans.id', '=', 'invplan_transactions.plan')
            ->join('plans','user_plans.plan','=','plans.id')
                ->where('invplan_transactions.user', Auth::id())
                ->select('invplan_transactions.*', 'plans.name as name')->get();

            $response = ['success' => true, 'data' => $data, 'message' => __('My Investments')];
        } catch (\Exception $e) {
            $response = ['success' => false, 'data' => [], 'message' => __($e->getMessage())];
        }
        return response()->json($response);
    }

    public function InvPlan()
    {
        try {

            $data['title'] = __('Investment Plans List');
            $data['menu'] = 'inv-plan';
            $data['sub_menu'] = 'plan_list';
            $data['free_plans'] = Plan::where(['mplan' => 1, 'status' => 1])->get();
            $data['premium_plans'] = Plan::where(['mplan' => 2, 'status' => 1])->get();
            $response = ['success' => true, 'data' => $data, 'message' => __('Membership Plan List ')];
        } catch (\Exception $e) {
            $response = ['success' => false, 'data' => [], 'message' => __($e->getMessage())];
        }
        return response()->json($response);
    }
    public function InvPlanDetails(Request $request)
    {
        try {

            $request->validate([
                'plan' => ['required'],
                'amount' => ['required'],
            ]);

            $plan = Plan::where('id', $request->plan)->first();
            if ($request->amount > $plan->max_price ||  $request->amount < $plan->min_price) {
                $response = ['status' => 301, 'message' => "Invalid amount", 'max' => $plan->max_price, 'min' => $plan->min_price];
                return response()->json($response);
            }

            $data['duration'] = $plan->expiration;
            $data['increment_interval'] = $plan->increment_interval;
            $data['ereturn'] = $plan->expected_return;
            $data['min_price'] = $plan->min_price;
            $data['max_price'] = $plan->max_price;
            $fees = 0;
            if ($plan->fees_type == 'Percentage' && $plan->fees != 0) {
                $fees = bcmul($plan->fees, bcdiv($request->amount, 100, 8), 3);
            } elseif ($plan->fees_type == 'Fixed' && $plan->fees != 0) {
                $fees = $plan->fees;
            }
            $expiration = explode(" ", $plan->expiration);
            $digit = $expiration[0];
            $frame = $expiration[1];

            $data['fees'] = $fees;
            $plan->increment_amount = bcmul($plan->increment_amount, bcdiv($request->amount, 100, 8), 3);
            if ($plan->increment_interval  == "Monthly") {
                // $plan->increment_amount /= 20;
                $data['daily_increment'] =  bcdiv($plan->increment_amount, 20, 3);
                $data['weekly_increment'] =  bcdiv($plan->increment_amount, 4, 3);
                $data['monthly_increment'] =  $plan->increment_amount;
            } elseif ($plan->increment_interval  == "Weekly") {
                $data['daily_increment'] =  bcdiv($plan->increment_amount, 5, 3);
                $data['weekly_increment'] =  $plan->increment_amount;
                $data['monthly_increment'] =  bcmul($plan->increment_amount, 4, 3);
            } else {
                $data['daily_increment'] =  $plan->increment_amount;
                $data['weekly_increment'] =  bcmul($plan->increment_amount, 4, 3);
                $data['monthly_increment'] =  bcmul($plan->increment_amount, 20, 3);
            }
            // $data['increment_amount'] = $plan->increment_amount ;
            $response = ['status' => 200, 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'data' => [], 'message' => __($e->getMessage())];
        }
        return response()->json($response);
    }

    public function joinPlan(Request $request)
    {
        try {

            $request->validate([
                'investplan' => ['required'],
                'investamount' => ['required'],
            ]);

            $wallet = Wallet::where(['user_id' => Auth::user()->id, 'coin_type' => 'Default'])->first();
            //get plan
            $plan = Plan::where('id', $request['investplan'])->first();

            if ((isset($request['investamount']) && $request['investamount'] > 0) && ($request['investamount'] >= $plan->min_price && $request['investamount'] <= $plan->max_price)) {
                $plan_price = $request['investamount'];
            } else {
                return redirect()->back()->with('dismiss', "invalid invest amount.");
            }
            // claculate plan fees
            $fees = 0;
            if ($plan->fees_type == 'Percentage' && $plan->fees != 0) {
                $fees = $plan->fees * $request['investamount'] / 100;
            } elseif ($plan->fees_type == 'Fixed' && $plan->fees != 0) {
                $fees = $plan->fees;
            }

            // get or create roi wallet
            $roi_wallet = Wallet::where(['user_id' => Auth::id(), 'coin_type' => 'ROI', 'type' => 1, 'coin_id' => 7])->first();
            if (!$roi_wallet) {
                $roi = new Wallet();
                $roi->user_id = Auth::id();
                $roi->type = 1;
                $roi->name = "Earning Wallet";
                $roi->coin_type = 'ROI';
                $roi->status = STATUS_SUCCESS;
                $roi->balance = 0;
                $roi->coin_id = 7;
                $roi->save();
            }
            $user_amount = $plan_price;

            if (!$roi_wallet || $roi_wallet->balance < $fees) {
                $plan_price += $fees;
            } else {
                $roi_wallet->update([
                    'balance' => bcsub($roi_wallet->balance, $fees, 8),
                ]);
            }

            //check if the user account balance can buy this plan
            if ($wallet->balance < $plan_price) {
                //redirect to make deposit
                return redirect()->back()->with('dismiss', "Your account is insufficient to purchase this plan. Please make a deposit.");
            }

            $expiration = explode(" ", $plan->expiration);
            $digit = $expiration[0];
            $frame = $expiration[1];
            $toexpire =  "add" . $frame;
            $end_at = Carbon::now()->$toexpire($digit)->toDateTimeString();

            //debit user
            $wallet->update([
                'balance' => $wallet->balance - $plan_price,
            ]);
            $plan->increment_amount = bcmul($plan->increment_amount, bcdiv($user_amount, 100, 8), 8);

            $_data = (object) [
                'plan' => $plan->id,
                'user' => Auth::user()->id,
                'amount' => $user_amount,
                'expected_return' => $plan->expected_return * $user_amount / 100,
                'active' => 'no',
                'fees' => $fees,
                'inv_duration' => $plan->expiration,
                'expire_date' => $end_at,
                'increment_interval' => $plan->increment_interval,
                'increment_amount' => $plan->increment_amount,
            ];
            $response = app(PlanRepository::class)->planJoinProcess($_data);
            if ($response['success'] == true) {
                //send notification
                $mailService = new MailService();
                $userName = Auth::user()->first_name . ' ' . Auth::user()->last_name;
                $userEmail = Auth::user()->email;
                $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
                $subject = __('New investment plan Purchase | :companyName', ['companyName' => $companyName]);
                $data['data'] = Auth::user();
                $data['data']->plan = (object) ['amount' => $user_amount, 'plan' => $plan->name, 'coin' => settings('coin_name')];
                $data['key'] = [];
                $mailService->send('email.invest.new_invest', $data, $userEmail, $userName, $subject);
            }
            $response = ['status' => 200, 'data' => $data];
        } catch (\Exception $e) {
            $response = ['success' => false, 'data' => [], 'message' => __($e->getMessage())];
        }
        return response()->json($response);
    }
}
