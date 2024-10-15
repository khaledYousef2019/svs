<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Model\AffiliationHistory;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    // admin referral bonus history
    public function adminReferralBonusHistory(Request $request)
    {
        if ($request->ajax()) {
            $items = AffiliationHistory::select('*');
            return datatables()->of($items)
                ->addColumn('status', function ($item) {
                    return status($item->status);
                })
                ->make(true);
        }

        // For non-AJAX requests or when you want to render a specific page,
        // you can return a basic message or handle it as needed.
        return response()->json(['message' => 'This endpoint is for AJAX requests only.']);
    }
}
