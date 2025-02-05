<?php

namespace App\Services;

use App\Model\UserPlans;
use App\Model\Wallet;
use App\Services\MailService;
use App\Helpers\PlanHelper;
use Carbon\Carbon;

class PlanService
{
    protected $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function handlePlanStatusChange($planId, $status, $emailTemplate)
    {
        $plan = UserPlans::find($planId);
        
        if (!$plan) {
            return false;
        }

        $end_at = PlanHelper::getPlanExpirationDate($plan->inv_duration);
        
        $plan->update([
            'active' => $status,
            'expire_date' => $end_at,
            'activated_at' => now(),
        ]);

        $this->sendPlanStatusEmail($plan, $emailTemplate);

        return true;
    }

    public function rejectPlan($planId)
    {
        $plan = UserPlans::find($planId);
        
        if (!$plan) {
            return false;
        }

        if ($plan->active === "no") {
            $this->refundPlanAmount($plan);
        }

        $plan->delete();
        return true;
    }

    protected function refundPlanAmount($plan)
    {
        $roi_wallet = Wallet::where([
            'user_id' => $plan->user,
            'coin_type' => 'ROI',
            'type' => 1,
            'coin_id' => 7
        ])->first();
        
        $roi_wallet->update([
            'balance' => bcadd($roi_wallet->balance, $plan->amount, 8),
        ]);
        
        $this->sendPlanStatusEmail($plan, 'reject_invest');
    }

    protected function sendPlanStatusEmail($plan, $template)
    {
        $user = $plan->duser;
        $companyName = allsetting()['app_title'] ?? __('Company Name');
        
        $data = [
            'data' => $user,
            'data->plan' => (object) [
                'amount' => $plan->amount,
                'plan' => $plan->name,
                'coin' => settings('coin_name')
            ],
            'key' => []
        ];

        $subject = __('investment plan :status | :companyName', [
            'status' => $template === 'accept_invest' ? 'Accepted' : 'Rejected',
            'companyName' => $companyName
        ]);

        $this->mailService->send("email.invest.$template", $data, $user->email, "$user->first_name $user->last_name", $subject);
    }
} 