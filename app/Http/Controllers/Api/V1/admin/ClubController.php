<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlanSaveRequest;
use App\Model\Coin;
use App\Model\MembershipBonusDistributionHistory;
use App\Model\MembershipClub;
use App\Model\MembershipPlan;
use App\Model\MembershipTransactionHistory;
use App\Repository\ClubRepository;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    // Member List
    public function membershipList(Request $request)
    {
        $query =  MembershipClub::join('users', 'users.id', '=', 'membership_clubs.user_id')
            ->select('membership_clubs.*', 'users.email as email');
        $items = $this->applyFiltersAndSorting($query, $request);
        $data = $items->getCollection()->transform(function ($item) {
            $item->plan_name = !empty($item->plan_id) ? $item->plan->plan_name : 'N/A';
            $item->status = status($item->status);
            $item->bonus = user_plan_bonus($item->user_id);
            return $item;

        });

        // Return JSON response for React
        return response()->json([
            'title' => __('Member List'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
    }

    // Coin Transfer History
    public function coinTransactionHistory(Request $request)
    {
        $query =  MembershipTransactionHistory::join('users', 'users.id', '=', 'membership_transaction_histories.user_id')
            ->select('membership_transaction_histories.*', 'users.email as email');
        $items = $this->applyFiltersAndSorting($query, $request);
        $data = $items->getCollection()->transform(function ($item) {
            $item->wallet_id = !empty($item->wallet->name) ? $item->wallet->name : 'N/A';
            $item->type = $item->type == CREDIT ? __('CREDIT') : __('DEBIT');
            $item->status = status($item->status);
            return $item;

        });

        // Return JSON response for React
        return response()->json([
            'title' => __('Member List'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
    }

    // Club Bonus Distribution History
    public function clubBonusDistribution(Request $request)
    {
        $query =  MembershipBonusDistributionHistory::join('users', 'users.id', '=', 'membership_bonus_distribution_histories.user_id')
            ->select('membership_bonus_distribution_histories.*', 'users.email as email');
        $items = $this->applyFiltersAndSorting($query, $request);
        $data = $items->getCollection()->transform(function ($item) {
            $item->plan_id =  !empty($item->plan->plan_name) ? $item->plan->plan_name : 'N/A';
            $item->wallet_id = !empty($item->wallet->name) ? $item->wallet->name : 'N/A';
            $item->status = status($item->status);
            $item->bonus_coin_type = check_default_coin_type($item->bonus_coin_type);
            unset($item->plan,$item->wallet);
            return $item;
        });

        // Return JSON response for React
        return response()->json([
            'title' => __('Bonus Distribution'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
    }

    // Plan List
    public function planList(Request $request)
    {

        $query =  MembershipPlan::select('membership_plans.*');
        $items = $this->applyFiltersAndSorting($query, $request);
        $data = $items->getCollection()->transform(function ($item) {
            $item->bonus_type = sendFeesType($item->bonus_type);
            $item->status = status($item->status);
            $item->bonus_coin_type = check_default_coin_type(DEFAULT_COIN_TYPE);
            $item->action = new \stdClass();
            $item->action->edit_url = route('planEdit', $item->id);
            return $item;

        });

        // Return JSON response for React
        return response()->json([
            'title' => __('Plan List'),
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);

    }

    // Plan Add
    public function planAdd()
    {
        return response()->json([
            'success' => true,
            'title' => __('Add New Plan'),
            'coins' => Coin::where(['status' => STATUS_ACTIVE, 'type' => DEFAULT_COIN_TYPE])->get(),
            'fees_types' => sendFeesType()
        ]);
    }

    // Plan Edit
    public function planEdit($id)
    {
        return response()->json([
            'success' => true,
            'title' => __('Update Plan'),
            'item' => MembershipPlan::find($id),
            'coins' => Coin::where(['status' => STATUS_ACTIVE, 'type' => DEFAULT_COIN_TYPE])->get(),
            'fees_types' => sendFeesType()
        ]);
    }

    // Plan Save
    public function planSave(PlanSaveRequest $request)
    {
        try {
            $response = app(ClubRepository::class)->saveClubPlan($request);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Club Bonus Distribution Process
    public function adminClubBonusDistribution()
    {
        try {
            $response = app(ClubRepository::class)->clubBonusDistributionProcess();
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Membership Settings
    public function adminMembershipSettings()
    {
        return response()->json([
            'success' => true,
            'title' => __('Membership Settings'),
            'settings' => allsetting(),
        ]);
    }
}
