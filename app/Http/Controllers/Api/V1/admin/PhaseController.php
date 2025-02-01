<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PhaseCreateRequest;
use App\Model\IcoPhase;
use App\Repository\PhaseRepository;
use Illuminate\Http\Request;

class PhaseController extends Controller
{
    // ICO phase list
    public function adminPhaseList(Request $request)
    {
        $phases = IcoPhase::where('status', '!=', STATUS_DELETED)->get();
        return response()->json(['phases' => $phases]);
    }

    // ICO phase add
    public function adminPhaseAdd()
    {
        return response()->json(['message' => 'Ready to add phase']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function adminPhaseAddProcess(PhaseCreateRequest $request)
    {
        $response = app(PhaseRepository::class)->phaseAddProcess($request);

        if ($response['success']) {
            return response()->json(['success' => true, 'message' => $response['message']]);
        }

        return response()->json(['success' => false, 'message' => $response['message']]);
    }

    // Phase edit
    public function phaseEdit($id)
    {
        $id = decrypt($id);
        $phase = IcoPhase::find($id);

        if ($phase) {
            return response()->json(['phase' => $phase]);
        } else {
            return response()->json(['success' => false, 'message' => __('Invalid phase')]);
        }
    }

    /**
     * Delete phase
     */
    public function phaseDelete($id)
    {
        $id = decrypt($id);
        try {
            $phase = IcoPhase::where('id', $id)->first();

            if (empty($phase)) {
                return response()->json(['success' => false, 'message' => __('Invalid phase')]);
            }

            $phase->status = STATUS_DELETED;
            $phase->save();

            return response()->json(['success' => true, 'message' => __('Phase deleted successfully.')]);
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'message' => __('Something went wrong.')]);
        }
    }

    public function phaseStatusChange($id)
    {
        $id = decrypt($id);
        try {
            $phase = IcoPhase::where('id', $id)->first();

            if (empty($phase)) {
                return response()->json(['success' => false, 'message' => __('Invalid phase')]);
            }

            if ($phase->status == STATUS_SUCCESS) {
                $phase->status = STATUS_PENDING;
                $phase->save();
                return response()->json(['success' => true, 'message' => __('Phase status deactivated successfully')]);
            } else {
                $phase->status = STATUS_SUCCESS;
                $phase->save();
                return response()->json(['success' => true, 'message' => __('Phase status activated successfully')]);
            }
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'message' => __('Something went wrong.')]);
        }
    }
}
