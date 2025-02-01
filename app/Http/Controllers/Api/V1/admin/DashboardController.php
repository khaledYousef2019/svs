<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\resetPasswordRequest;
use App\Http\Requests\UserProfileUpdate;
use App\Http\Services\AuthService;
use App\Http\Services\CommonService;
use App\Jobs\SendMail;
use App\Model\BuyCoinHistory;
use App\Model\DepositeTransaction;
use App\Model\MembershipBonusDistributionHistory;
use App\Model\MembershipClub;
use App\Model\Wallet;
use App\Model\WithdrawHistory;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    // Admin dashboard
    public function adminDashboard()
    {
        try {
            $data = [
                'title' => __('Admin Dashboard'),
                'total_income' => WithdrawHistory::sum('fees'),
                'total_coin' => Wallet::sum('balance'),
                'total_sold_coin' => BuyCoinHistory::sum('coin'),
                'total_blocked_coin' => MembershipClub::sum('amount'),
                'total_member' => MembershipClub::where('status', STATUS_ACTIVE)->count(),
                'bonus_distribution' => MembershipBonusDistributionHistory::where('status', STATUS_ACTIVE)->sum('bonus_amount'),
                'total_user' => User::count(),
                'active_percentage' => User::where('status', STATUS_ACTIVE)->count() * 100 / User::count(),
                'inactive_percentage' => User::where('status', '<>', STATUS_ACTIVE)->count() * 100 / User::count(),
                'monthly_deposit' => $this->getMonthlyData(DepositeTransaction::class, 'amount'),
                'monthly_withdrawal' => $this->getMonthlyData(WithdrawHistory::class, 'amount'),
            ];

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Helper method to get monthly data
    private function getMonthlyData($model, $field)
    {
        $allMonths = all_months();
        $monthlyData = $model::select(DB::raw("SUM($field) as total"), DB::raw('MONTH(created_at) as months'))
            ->whereYear('created_at', Carbon::now()->year)
            ->where('status', STATUS_SUCCESS)
            ->groupBy('months')
            ->get()
            ->pluck('total', 'months')
            ->toArray();

        return array_map(function ($month) use ($monthlyData) {
            return $monthlyData[$month] ?? 0;
        }, $allMonths);
    }

    // Admin profile
    public function adminProfile(Request $request)
    {
        try {
            $data = [
                'title' => __('Profile'),
                'user' => User::find(Auth::id()),
                'settings' => allsetting(),
            ];

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Update user profile
    public function UserProfileUpdate(UserProfileUpdate $request)
    {
        if (strpos($request->phone, '+') !== false) {
            return response()->json(['success' => false, 'message' => __("Don't put plus sign with phone number")], 400);
        }
        if (!isset($request->email)) {
            return response()->json(['success' => false , 'message'=> __("Email must be required")], 400);
        }

        try {
            $data = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'phone_verified' => $request->phone !== Auth::user()->phone ? null : Auth::user()->phone_verified,
            ];

            $user = (!empty($request->id)) ? User::find(decrypt($request->id)) : Auth::user();
            $user->update($data);

            return response()->json(['success' => __('Profile updated successfully')]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Upload profile image
    public function uploadProfileImage(Request $request)
    {
        $rules = [
            'file_one' => 'required|image|max:2024|mimes:jpg,jpeg,png,gif,svg|dimensions:max_width=500,max_height=500',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first('file_one')], 400);
        }

        try {
            $img = $request->file('file_one');
            if ($img) {
                $photoPath = $img->store('images', 'public');
                $user = User::find(Auth::id());
                $user->photo = $photoPath;
                $user->save();

                return response()->json(['success' => __('Profile picture uploaded successfully')]);

            } else {
                return response()->json(['error' => __('Please input an image')], 400);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Change password
    public function changePasswordSave(resetPasswordRequest $request)
    {
        try {
            $service = new AuthService();
            $change = $service->changePassword($request);

            return response()->json(['success' => $change['message']]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Send email
    public function sendEmail()
    {
        return response()->json(['title' => __('Send Email')]);
    }

    // Send notification
    public function sendNotification()
    {
        return response()->json(['title' => __('Send Notification')]);
    }

    // Send mail process
    public function sendEmailProcess(Request $request)
    {
        $rules = [
            'subject' => 'required',
            'email_message' => 'required',
            'email_type' => 'required'
        ];
        $messages = [
            'subject.required' => __('Subject field cannot be empty'),
            'email_message.required' => __('Message field cannot be empty'),
            'email_type.required' => __('Email type field cannot be empty'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $data = [
                'subject' => $request->subject,
                'email_message' => $request->email_message,
                'type' => $request->email_type,
                'mailTemplate' => 'email.genericemail',
                'email_header' => $request->email_headers ?? null,
                'email_footer' => $request->footers ?? null,
            ];

            dispatch(new SendMail($data))->onQueue('send-email');

            return response()->json(['success' => __('Mail sent successfully')]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Send notification process
    public function sendNotificationProcess(Request $request)
    {
        $rules = [
            'title' => 'required',
            'notification_body' => 'required',
        ];

        $messages = [
            'title.required' => 'Notification title cannot be empty',
            'notification_body.required' => 'Notification body cannot be empty',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $service = new CommonService();
            $service->sendNotificationProcess($request);

            return response()->json(['success' => 'Notification sent successfully']);

        } catch (\Exception $exception) {
            return response()->json(['error' => 'Something went wrong. Please try again'], 500);
        }
    }

    // Test
    public function adminTest()
    {
        // Your test code here, if needed
        return response()->json(['message' => 'Test endpoint']);
    }
}
