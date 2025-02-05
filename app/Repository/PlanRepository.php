<?php
namespace App\Repository;
use App\Model\Plan;
use App\Model\UserPlans;
use App\Model\InvplanTransactions;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PlanRepository
{
// phase  save process
    public function planAddProcess($request)
    {
        $response = ['success' => false, 'message' => __('Invalid request')];

        DB::beginTransaction();
        try {
            $data = $this->preparePlanData($request);
            
            if (!empty($request->edit_id)) {
                Plan::updateOrCreate(['id' => decrypt($request->edit_id)], $data);
                $response = ['success' => true, 'message' => __('Investment Plan updated successfully')];
            } else {
                Plan::create($data);
                $response = ['success' => true, 'message' => __('New Investment Plan created successfully')];
            }

            DB::commit();
            return $response;

        } catch (\Exception $exception) {
            DB::rollBack();
            logger()->error('Plan Add Process Error: ' . $exception->getMessage());
            return ['success' => false, 'message' => __('Something went wrong')];
        }
    }

    protected function preparePlanData($request): array
    {
        return [
            'mplan' => $request->mplan,
            'name' => $request->name,
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
            'expected_return' => $request->return ?? 0,
            'increment_interval' => $request->increment_interval,
            'increment_amount' => $request->increment_amount,
            'fees_type' => $request->fees_type,
            'fees' => $request->fees ?? 0,
            'type' => settings('coin_name'),
            'expiration' => $request->expiration,
            'status' => $request->status
        ];
    }

// delete bank
    public function planJoinProcess($_data)
    {
        $response = ['success' => false, 'message' => __('Invalid request')];

        DB::beginTransaction();
        try {
            $data = [
                'plan' => $_data->plan,
                'user' => $_data->user,
                'amount' => $_data->amount,
                'expected_return' => $_data->expected_return ,
                'active' => $_data->active,
                'fees' => $_data->fees,
                'inv_duration' => $_data->inv_duration ? $_data->inv_duration : 0,
                'increment_interval' => $_data->increment_interval,
                'increment_amount' => $_data->increment_amount,
                'expire_date' => $_data->expire_date,
                'last_fees' => \Carbon\Carbon::now(),
                'activated_at' => \Carbon\Carbon::now(),
                'last_growth' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];
            if (!empty($_data->edit_id)) {
                $plan_id = UserPlans::updateOrcreate(['id' => decrypt($_data->edit_id)], $data);
                $response = ['success' => true, 'message' => __('User Investment updated successfully')];
            } else {
                $plan_id = UserPlans::create($data);
                $response = ['success' => true, 'message' => __('You successfully purchased a plan and your plan is Waitting for Confirmation.')];
     
                //create history
                InvplanTransactions::create([
                    'user' => $_data->user,
                    'plan' => $plan_id->id,
                    'amount' => $_data->amount,
                    'status' => 'Debit',
                    'type' => "Plan purchase",
                ],[
                    'user' => $_data->user,
                    'plan' => $plan_id->id,
                    'amount' => $_data->fees,
                    'status' => 'Debit',
                    'type' => "Plan Fees",
                ]);
            }


        } catch (\Exception $exception) {
            var_dump($exception->getMessage());exit();
            DB::rollback();
            $response = ['success' => false, 'message' => __('Something went wrong')];
            return $response;
        }
        DB::commit();
        return $response;
    }
}
