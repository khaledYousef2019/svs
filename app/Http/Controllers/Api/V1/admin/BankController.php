<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BankRequest;
use App\Http\Services\CommonService;
use App\Model\Bank;
use App\Repository\BankRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BankController extends Controller
{
    /**
     * Display a listing of the banks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bankList(Request $request)
    {

        $query =  Bank::select('*')->where('status', '<>', 5);
        $items = $this->applyFiltersAndSorting($query, $request);
        $data = $items->getCollection()->transform(function ($item) {
            $item->status = status($item->status);
            $item->country_name = !empty($item->country) ? country($item->country) : '';
            $item->created_at = $item->created_at->toDateTimeString();
            $item->action = new \stdClass();
            $item->action->Edit = route('bankEdit', encrypt($item->id));
            $item->action->Delete = route('bankDelete', encrypt($item->id));
            return $item;
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

    /**
     * Show the form for creating a new bank.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function bankAdd()
    {
        return response()->json([
            'message' => __('Add new bank'),
            'data' => []
        ]);
    }

    /**
     * Show the form for editing the specified bank.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function bankEdit($id)
    {
        $id = app(CommonService::class)->checkValidId($id);
        if (is_array($id)) {
            return response()->json([
                'success' => false,
                'message' => __('Data not found.')
            ], 404);
        }

        $item = Bank::where('id', $id)->first();
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => __('Bank not found.')
            ], 404);
        }

        return response()->json([
            'message' => __('Update Bank'),
            'data' => $item
        ]);
    }

    /**
     * Store a newly created bank in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bankAddProcess(BankRequest $request)
    {
        if ($request->isMethod('post')) {
            $response = app(BankRepository::class)->bankSaveProcess($request);
            if ($response['success'] == true) {
                return response()->json([
                    'success' => true,
                    'message' => $response['message']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response['message']
            ], 400);
        }

        return response()->json([
            'success' => false,
            'message' => __('Invalid request method.')
        ], 405);
    }

    /**
     * Remove the specified bank from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function bankDelete($id)
    {
        if (isset($id)) {
            $id = app(CommonService::class)->checkValidId($id);
            if (is_array($id)) {
                return response()->json([
                    'success' => false,
                    'message' => __('Item not found.')
                ], 404);
            }

            $response = app(BankRepository::class)->deleteBank($id);
            if ($response['success'] == true) {
                return response()->json([
                    'success' => true,
                    'message' => $response['message']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response['message']
            ], 400);
        }

        return response()->json([
            'success' => false,
            'message' => __('Invalid ID.')
        ], 400);
    }
}
