<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Requests\Admin\PlanCreateRequest;
use App\Repository\PlanRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Plan;
use App\User;
use App\Model\UserPlans;
use App\Model\Wallet;
use App\Model\InvplanTransactions;
use App\Model\MembershipPlan;
use App\Services\MailService;
use Illuminate\Support\Facades\DB;


class PlanController extends Controller
{
    public function adminInvPlan(Request $request)
    {
        $query = Plan::where('status', '!=', STATUS_DELETED);
        
        $fieldTableMap = [
            // 'increment_amount' => 'plan',
            // 'first_name.last_name' => 'users',
        ];
        
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);

        $data = $items->getCollection()->transform(function ($plan) {
            $plan->increment_amount = $plan->increment_type == 'Fixed' ? $plan->increment_amount . ' WFDP Coin' : $plan->increment_amount . ' %';
            $plan->mplan = $plan->mplan == 1 ? "Free" : "Premium";
            $plan->action = new \stdClass();
            // $plan->action->Edit = route('adminInvPlanEdit', encrypt($plan->id));
            $plan->action->View = route('adminViewPlan', $plan->id);
            $plan->action->Delete = route('adminInvPlanDelete', encrypt($plan->id));
            return $plan;
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
    public function adminViewPlan(Request $request)
    {
        $query = UserPlans::where('plan', '=', $request->id);

        $fieldTableMap = [
            'user' => ['duser' => 'first_name.last_name'],
            'plan' => ['dplan' => 'name'],
            'mplan' => 'membership_plans',
        ];
        
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);

        $data = $items->getCollection()->transform(function ($plan) {
            $plan->increment_amount = $plan->increment_type == 'Fixed' ? $plan->increment_amount . ' WFDP Coin' : $plan->increment_amount . ' %';
            $plan->mplan = $plan->mplan == 1 ? "Free" : "Premium";
            $plan->plan = !empty($plan->plan) ? $plan->dplan->name : 'N/A';
            $plan->user = !empty($plan->user) ? $plan->duser->first_name . " " . $plan->duser->last_name : 'N/A';
            $plan->action = new \stdClass();
            $plan->action->Accept = route('adminInvPlanActivate', encrypt($plan->id));
            $plan->action->Delete = route('adminInvPlanReject', encrypt($plan->id));
            return $plan;
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
    public function adminShowPendingInvestment(Request $request)
    {
        $query = UserPlans::where('active', 'no');
        $fieldTableMap = [
            'user' => ['duser' => 'first_name.last_name'],
            'plan' => ['dplan' => 'name'],
            'mplan' => 'membership_plans',
        ];
        
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);

        $data = $items->getCollection()->transform(function ($plan) {
            // $plan->increment_amount = $plan->increment_type == 'Fixed' ? $plan->increment_amount . ' WFDP Coin' : $plan->increment_amount . ' %';
            $plan->mplan = $plan->mplan == 1 ? "Free" : "Premium";
            $plan->plan = !empty($plan->plan) ? $plan->dplan->name : 'N/A';
            $plan->user = !empty($plan->user) ? $plan->duser->first_name . " " . $plan->duser->last_name : 'N/A';
            $plan->action = new \stdClass();
            $plan->action->Edit = route('adminUserPlanEdit', encrypt($plan->id));
            $plan->action->Accept = route('adminInvPlanActivate', encrypt($plan->id));
            $plan->action->Reject = route('adminInvPlanReject', encrypt($plan->id));

            return $plan;
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
    public function adminShowActiveInvestment(Request $request)
    {
        $query = UserPlans::where('active', 'yes');
        $fieldTableMap = [
            'user' => ['duser' => 'first_name.last_name'],
            'plan' => ['dplan' => 'name'],
            'mplan' => 'membership_plans',
        ];
        
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);

        $data = $items->getCollection()->transform(function ($plan) {
            // $plan->increment_amount = $plan->increment_type == 'Fixed' ? $plan->increment_amount . ' WFDP Coin' : $plan->increment_amount . ' %';
            $plan->mplan = $plan->mplan == 1 ? "Free" : "Premium";
            $plan->plan = !empty($plan->plan) ? $plan->dplan->name : 'N/A';
            $plan->user = !empty($plan->user) ? $plan->duser->first_name . " " . $plan->duser->last_name : 'N/A';
            $plan->action = new \stdClass();
            $plan->action->Edit = route('adminUserPlanEdit', encrypt($plan->id));
            $plan->action->View = route('adminViewInvest', encrypt($plan->id));
            $plan->action->Reject = route('adminInvPlanReject', encrypt($plan->id));

            return $plan;
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

    public function adminInvPlanDelete($id)
    {
        $id = decrypt($id);
        $plan = Plan::where('id', $id)->first();
        if ($plan) {
            $plan->delete();

            $message = 'Plan Deleted .';
        }
        return redirect()->back()->withInput()->with('dismiss', $message);
    }
    public function adminInvPlanReject($id)
    {
        $id = decrypt($id);
        $plan = UserPlans::where('id', $id)->first();
        if ($plan) {
            if ($plan->active == "no") {
                $roi_wallet = Wallet::where(['user_id' => $plan->user, 'coin_type' => 'ROI', 'type' => 1, 'coin_id' => 7])->first();
                $roi_wallet->update([
                    'balance' => bcadd($roi_wallet->balance, $plan->amount, 8),
                ]);
                $mailService = new MailService();
                $userName = $plan->duser->first_name . ' ' . $plan->duser->last_name;
                $userEmail = $plan->duser->email;
                $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
                $subject = __('investment plan Rejected | :companyName', ['companyName' => $companyName]);
                $data['data'] = $plan->duser;
                $data['data']->plan = (object) ['amount' => $plan->amount, 'plan' => $plan->name, 'coin' => settings('coin_name')];
                $data['key'] = [];
                $mailService->send('email.invest.reject_invest', $data, $userEmail, $userName, $subject);
            }
            $plan->delete();

            $message = 'Plan Deleted .';
            $message .= $plan->active == "no" ? $plan->amount . ' coins added to user roi wallet' : '';
        } else {
            $message = "somthing went wrong";
        }

        return redirect()->back()->withInput()->with('dismiss', $message);
    }
    public function adminInvPlanActivate($id)
    {
        $id = decrypt($id);
        $plan = UserPlans::where('id', $id)->first();
        $end_at = getPlanExpirationDate($plan->inv_duration);
        $plan->update([
            'active' => 'yes',
            'expire_date' => $end_at,
            'activated_at' => \Carbon\Carbon::now(),
        ]);

        $mailService = new MailService();
        $userName = $plan->duser->first_name . ' ' . $plan->duser->last_name;
        $userEmail = $plan->duser->email;
        $companyName = isset(allsetting()['app_title']) && !empty(allsetting()['app_title']) ? allsetting()['app_title'] : __('Company Name');
        $subject = __('investment plan Accepted | :companyName', ['companyName' => $companyName]);
        $data['data'] = $plan->duser;
        $data['data']->plan = (object) ['amount' => $plan->amount, 'plan' => $plan->name, 'coin' => settings('coin_name')];
        $data['key'] = [];
        $mailService->send('email.invest.accept_invest', $data, $userEmail, $userName, $subject);

        $message = $plan ? 'Plan Activated successfully' : "somthing went wrong";
        return redirect()->back()->withInput()->with('dismiss', $message);
    }
    public function adminInvPlanDeactivate($id)
    {
        $id = decrypt($id);
        $plan = UserPlans::where('id', $id)->first();
        $end_at = getPlanExpirationDate($plan->inv_duration);
        if($plan){

            $plan->update([
                'active' => 'yes',
                'expire_date' => $end_at,
                'activated_at' => \Carbon\Carbon::now(),
            ]);
            return redirect()->back()->with('success','investment temporary Deactiveated');
        }
        return redirect()->back()->withInput()->with('dismiss', "something went wrong");

    }

    /**
     * adminPlanAddProcess
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function adminPlanAddProcess(PlanCreateRequest $request)
    {
        if ($request->isMethod('post')) {
            $response = app(PlanRepository::class)->planAddProcess($request);
            if ($response['success'] == true) {
                return redirect()->route('adminInvPlan')->with('success', $response['message']);
            }

            return redirect()->back()->withInput()->with('dismiss', $response['message']);
        }
        return redirect()->back();
    }
    public function adminUserPlanEditProcess(Request $request)
    {
        if ($request->isMethod('post')) {
            // $response = app(PlanRepository::class)->planAddProcess($request);
            $plan = UserPlans::where('id', $request->id)->first();
            // $end_at = getPlanExpirationDate($plan->inv_duration);
            $activated_at = date('Y-m-d H:i:s', strtotime($request->activated_at));
            $expire_date = date('Y-m-d H:i:s', strtotime($request->expire_date));

            $plan->update([
                'increment_interval' => $request->increment_interval,
                'expected_return' => $request->expected_return,
                'fees' => $request->fees,
                'increment_amount' => $request->increment_amount,
                'activated_at' => $activated_at,
                'expire_date' => $expire_date,
                'active' => $request->active
            ]);
            if ($plan->wasChanged()) {
                return redirect()->route('adminViewInvestor', encrypt($plan->user))->withInput()->with('success', "Plan Updated Successfully");
            }

            return redirect()->back()->withInput()->with('dismiss', "something went wrong");
        }
        return redirect()->back();
    }
    // public function adminInvPlanEdit($id)
    // {
    //     $data['title'] = __('Investment Plan');
    //     $data['menu'] = 'plan';
    //     $data['sub_menu'] = 'Plan_add';
    //     $data['clubs'] = MembershipPlan::get();
    //     $id = decrypt($id);
    //     $data['item'] = Plan::find($id);
    //     if (isset($data['item'])) {
    //         return view('admin.plan.plan_add', $data);
    //     } else {
    //         return redirect()->back()->with(['dismiss' => __('Invalid Investment Plan')]);
    //     }
    // }
    public function adminGetInvestors(Request $request)
    {
        $query = UserPlans::select(
            'user',
            DB::raw('COUNT(user_plans.plan) as plans'),
            DB::raw('SUM(user_plans.amount) as total_invested'),
            DB::raw('SUM(user_plans.expected_return) as expected_return')
        )
            ->groupBy('user');
        $fieldTableMap = [
            'user' => ['duser' => 'first_name.last_name'],
            'plan' => ['dplan' => 'name'],
            'mplan' => 'membership_plans',
        ];
        
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);

        $data = $items->getCollection()->transform(function ($plan) {
            $plan->mplan = $plan->mplan == 1 ? "Free" : "Premium";
            $plan->plan = !empty($plan->plan) ? $plan->dplan->name : 'N/A';
            $plan->user = !empty($plan->user) ? $plan->duser->first_name . " " . $plan->duser->last_name : 'N/A';
            $plan->current_return = !empty($item->dtransations) ? $item->dtransations->sum('amount') : 0;
            $plan->trade_mode = isset($item->duser->trade_mode) && $item->duser->trade_mode ? 1 : 0;
            $plan->action = new \stdClass();
            $plan->action->View = route('adminViewInvestor', encrypt($plan->id));

            return $plan;
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
    public function adminViewInvestor(Request $request)
    {
        $data['investor'] = User::findOrFail(decryptId($request->id));

        $query = UserPlans::where('user', '=', decryptId($request->id));
        $fieldTableMap = [
            // 'user' => ['duser' => 'first_name.last_name'],
            'plan' => ['dplan' => 'name'],
            // 'mplan' => 'membership_plans',
        ];
        
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);

        $data = $items->getCollection()->transform(function ($plan) {
            $plan->plan = !empty($plan->plan) ? $plan->dplan->name : 'N/A';
            $plan->action = new \stdClass();
            $plan->action->Edit = route('adminUserPlanEdit', encrypt($plan->id));
            $plan->action->View = route('adminViewInvest', encrypt($plan->id));

            return $plan;
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
    public function adminUserPlanEdit($id)
    {
        $data['title'] = __('Investor Plan');
        $data['menu'] = 'plan';
        $data['sub_menu'] = 'Plan_add';
        $id = decrypt($id);
        $data['item'] = UserPlans::findOrFail($id);
        if (isset($data['item'])) {
            return view('admin.plan.edit_user_plan', $data);
        } else {
            return redirect()->back()->with(['dismiss' => __('Invalid Investment Plan')]);
        }
    }
    public function adminViewInvest(Request $request)
    {
        $data['investment'] = UserPlans::findOrFail(decryptId($request->id));

        $query = InvplanTransactions::where('plan', '=', decryptId($request->id));
        $fieldTableMap = [
            // 'user' => ['duser' => 'first_name.last_name'],
            // 'plan' => ['dplan' => 'name'],
            // 'mplan' => 'membership_plans',
        ];
        
        $items = $this->applyFiltersAndSorting($query, $request, $fieldTableMap);

        $data = $items->getCollection()->transform(function ($plan) {
            $plan->plan = !empty($plan->plan) ? UserPlans::find($plan->plan)->dplan->name : 'N/A';
            $plan->user = !empty($plan->user) ? $plan->duser->first_name . " " . $plan->duser->last_name : 'N/A';

            return $plan;
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
}
