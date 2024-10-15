<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransferCoinRequest;
use App\Model\MembershipBonusDistributionHistory;
use App\Model\MembershipClub;
use App\Model\MembershipPlan;
use App\Model\Wallet;
use App\Repository\ClubRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubController extends Controller
{
    /**
     * Initialize product service
     *
     * ProductController constructor.
     */
    public function __construct()
    {
        $this->clubRepo = new ClubRepository;
    }

    // club membership plan
    public function membershipClubPlan()
    {
        try {
            $plans = MembershipPlan::where(['status' => STATUS_ACTIVE])->get();
            $wallets = Wallet::where(['user_id' => Auth::id(), 'coin_type' => 'Default'])->get();
            $smallPlan = MembershipPlan::where(['status' => STATUS_ACTIVE])->orderBy('amount', 'asc')->first();

            if ($plans->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No plan found'], 404);
            }
            foreach ($plans as $plan) {
                $plan->image = asset(IMG_VIEW_PATH . $plan->image);
            }

            return response()->json([
                'success' => true,
                'title' => __('Membership Plans'),
                'plans' => $plans,
                'wallets' => $wallets,
                'small_plan' => $smallPlan
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    // my membership details
    public function myMembership(Request $request)
    {
        try {
            $club = MembershipClub::where(['user_id' => Auth::id(), 'status' => STATUS_ACTIVE])->first();
            if ($club){
                $club->plan_bonus = user_plan_bonus($club->user_id);
                $club->plan = $club->plan ?? [];
                $club->amount = number_format($club->amount,2)." ".settings('coin_name');
            }

            $query = MembershipBonusDistributionHistory::join('users', 'users.id', '=', 'membership_bonus_distribution_histories.user_id')
                ->where('membership_bonus_distribution_histories.user_id', Auth::id())
                ->select('membership_bonus_distribution_histories.*', 'users.email as email');
            $items = $this->applyFiltersAndSorting($query, $request);
            $data = $items->getCollection()->transform(function ($item) {
                $item->plan_id = !empty($item->plan->plan_name) ? $item->plan->plan_name : 'N/A';
                $item->wallet_id = !empty($item->wallet->name) ? $item->wallet->name : 'N/A';
                $item->status = status($item->status);
//                $item->email = $item->email;
                return $item;
            });
            // Return JSON response for React
            $MembershipBonusDistributionHistory = [
                'data' => $data,
                'recordsTotal' => $items->total(),
                'recordsFiltered' => $items->total(), // Adjust if there are filters
                'draw' => $request->input('draw'), // Include draw for DataTables
            ];

            return response()->json([
                'success' => true,
                'title' => __('My Membership Details'),
                'data' => $club,
                'MembershipBonusDistributionHistory' => $MembershipBonusDistributionHistory
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // transfer coin to club
    public function transferCoinToClub(TransferCoinRequest $request)
    {
        try {
            $response = $this->clubRepo->transferCoinToMembershipClub($request);

            return response()->json([
                'success' => $response['success'],
                'message' => $response['message']
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // transfer coin to my wallet
    public function transferCoinToWallet(TransferCoinRequest $request)
    {
        try {
            $response = $this->clubRepo->transferCoinToMyWallet($request);

            return response()->json([
                'success' => $response['success'],
                'message' => $response['message']
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

}
