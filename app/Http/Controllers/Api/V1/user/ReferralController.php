<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Model\AffiliationCode;
use App\Model\AffiliationHistory;
use App\Repository\AffiliateRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    protected $affiliateRepository;

    public function __construct(AffiliateRepository $affiliateRepository)
    {
        $this->affiliateRepository = $affiliateRepository;
        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();
    }

    /*
     * myReferral
     *
     */

    public function myReferral()
    {
        try {
            $user = Auth::user();

            $referrals_3 = DB::table('referral_users as ru1')
                ->where('ru1.parent_id', $user->id)
                ->join('referral_users as ru2', 'ru2.parent_id', '=', 'ru1.user_id')
                ->join('referral_users as ru3', 'ru3.parent_id', '=', 'ru2.user_id')
                ->join('users', 'users.id', '=', 'ru3.user_id')
                ->select('ru3.user_id as level_3', 'users.email', 'users.first_name as full_name', 'users.created_at as joining_date')
                ->get();

            $referrals_2 = DB::table('referral_users as ru1')
                ->where('ru1.parent_id', $user->id)
                ->join('referral_users as ru2', 'ru2.parent_id', '=', 'ru1.user_id')
                ->join('users', 'users.id', '=', 'ru2.user_id')
                ->select('ru2.user_id as level_2', 'users.email', 'users.first_name as full_name', 'users.created_at as joining_date')
                ->get();

            $referrals_1 = DB::table('referral_users as ru1')
                ->where('ru1.parent_id', $user->id)
                ->join('users', 'users.id', '=', 'ru1.user_id')
                ->select('ru1.user_id as level_1', 'users.email', 'users.first_name as full_name', 'users.created_at as joining_date')
                ->get();

            $referralUsers = collect([$referrals_1, $referrals_2, $referrals_3])
                ->flatten(1)
                ->map(function ($item) {
                    return [
                        'id' => $item->level_1 ?? $item->level_2 ?? $item->level_3,
                        'full_name' => $item->full_name,
                        'email' => $item->email,
                        'joining_date' => $item->joining_date,
                        'level' => $item->level_1 ? __('Level 1') : ($item->level_2 ? __('Level 2') : __('Level 3'))
                    ];
                });

            if (!$user->Affiliate) {
                $created = $this->affiliateRepository->create($user->id);
                if ($created < 1) {
                    return response()->json(['success' => false, 'message' => __('Failed to generate new referral code.')], 500);
                }
            }

            $referralCodeUrl = url('') . '/referral-reg?ref_code=' . $user->affiliate->code;

            $maxReferralLevel = max_level();
            $referralQuery = $this->affiliateRepository->childrenReferralQuery($maxReferralLevel);

            $referralAll = $referralQuery['referral_all']
                ->where('ru1.parent_id', $user->id)
                ->select('ru1.parent_id', DB::raw($referralQuery['select_query']))
                ->first();

            $referralLevels = [];
            for ($i = 0; $i < $maxReferralLevel; $i++) {
                $level = 'level' . ($i + 1);
                $referralLevels[$i + 1] = $referralAll->{$level};
            }

            $monthlyEarnings = AffiliationHistory::select(
                DB::raw('DATE_FORMAT(`created_at`,\'%Y-%m\') as "year_month"'),
                DB::raw('SUM(amount) AS total_amount'),
                DB::raw('COUNT(DISTINCT(child_id)) AS total_child'))
                ->where('user_id', $user->id)
                ->where('status', 1)
                ->groupBy('year_month')
                ->get();

            $monthlyEarningData = $monthlyEarnings->mapWithKeys(function ($earning) {
                return [
                    $earning->year_month => [
                        'year_month' => $earning->year_month,
                        'total_amount' => $earning->total_amount
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'title' => __('My Referral'),
                'user' => $user,
                'referrals' => $referralUsers,
                'url' => $referralCodeUrl,
                'referralLevels' => $referralLevels,
                'max_referral_level' => $maxReferralLevel,
                'monthlyEarningHistories' => $monthlyEarningData,
                'monthArray' => array_keys($monthlyEarningData->toArray())
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function __destruct()
    {
        config()->set('database.connections.mysql.strict', true);
        DB::reconnect();
    }

    /*
     * signup
     *
     * It's for referral signup.
     *
     *
     *
     *
     */

    public function signup(Request $request)
    {
        $code = $request->get('ref_code');

        if ($code) {
            $parentUser = AffiliationCode::where('code', $code)->first();
            if ($parentUser) {
                return response()->json(['success' => true, 'view' => 'auth.signup']);
            } else {
                return response()->json(['success' => false, 'message' => __('Invalid referral code.')], 400);
            }
        }

        return response()->json(['success' => false, 'message' => __('Invalid referral code.')], 400);
    }


    // my referral earning
    public function myReferralEarning(Request $request)
    {
        try {
                $items = AffiliationHistory::where(['user_id' => Auth::id(), 'status' => STATUS_ACTIVE])->select('*')->get();

                return response()->json([
                    'data' => $items->map(function ($item) {
                        $item->coin_type = find_coin_type($item->coin_type);
                        $item->deposit_status = deposit_status($item->status);
                    }),
                    'success' => true
                ],200);


        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

}
