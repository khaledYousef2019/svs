<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Model\AdminSetting;
use App\Model\ContactUs;
use App\Model\Faq;
use App\Repository\SettingRepository;
use App\Services\ERC20TokenApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    private $settingRepo;

    public function __construct()
    {
        $this->settingRepo = new SettingRepository();
    }

    // Get admin settings
    public function adminSettings(Request $request)
    {
            $data['settings'] =allsetting();
            $data['token_api_type'] = token_api_type();
            return response()->json([
                'success' => true,
                'settings' => $data
            ]);
    }

    // Get feature settings
    public function adminFeatureSettings(Request $request)
    {
        if ($request->ajax()) {
            $settings = allsetting();
            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid request']);
    }

    // Save feature settings
    public function saveAdminFeatureSettings(Request $request)
    {
        $rules = [];
        if ($request->max_co_wallet_user) {
            $rules[MAX_CO_WALLET_USER_SLUG] = 'integer';
        }
        if ($request->co_wallet_withdrawal_user_approval_percentage) {
            $rules[CO_WALLET_WITHDRAWAL_USER_APPROVAL_PERCENTAGE_SLUG] = 'numeric';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $coWalletActive = $request->co_wallet_feature_active ?? 0;
        $recapchaActive = $request->google_recapcha ?? 0;
        $swap_enabled = $request->swap_enabled ?? 0;

        AdminSetting::updateOrCreate(['slug' => CO_WALLET_FEATURE_ACTIVE_SLUG], ['value' => $coWalletActive]);
        AdminSetting::updateOrCreate(['slug' => 'google_recapcha'], ['value' => $recapchaActive]);
        AdminSetting::updateOrCreate(['slug' => 'swap_enabled'], ['value' => $swap_enabled]);
        AdminSetting::updateOrCreate(['slug' => 'NOCAPTCHA_SECRET'], ['value' => $request->NOCAPTCHA_SECRET]);
        AdminSetting::updateOrCreate(['slug' => 'NOCAPTCHA_SITEKEY'], ['value' => $request->NOCAPTCHA_SITEKEY]);

        $response = $this->saveAllAdminSettingsDynamicallyFromRequest($request, ['_token', 'itech', CO_WALLET_FEATURE_ACTIVE_SLUG]);

        return response()->json($response);
    }

    private function saveAllAdminSettingsDynamicallyFromRequest($request, $except): array
    {
        try {
            DB::beginTransaction();
            foreach ($request->except($except) as $key => $value) {
                AdminSetting::updateOrCreate(['slug'=>$key], ['value'=>$value]);
            }
            DB::commit();
            return ['success'=> true, 'message' => __('Saved successfully.')];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['success'=> false, 'message'=>__('Something went wrong.')];
        }

    }


    // Save KYC settings
    public function adminSaveKycSettings(Request $request)
    {
        if ($request->post()) {
            try {
                if (($request->kyc_enable_for_withdrawal == STATUS_ACTIVE) &&
                    ($request->kyc_nid_enable_for_withdrawal != STATUS_ACTIVE && $request->kyc_driving_enable_for_withdrawal != STATUS_ACTIVE && $request->kyc_passport_enable_for_withdrawal != STATUS_ACTIVE)) {
                    return response()->json(['success' => false, 'message' => __('Minimum one type of verification should be enabled for withdrawal')]);
                }
                if (($request->kyc_enable_for_trade == STATUS_ACTIVE) &&
                    ($request->kyc_nid_enable_for_trade != STATUS_ACTIVE && $request->kyc_passport_enable_for_trade != STATUS_ACTIVE && $request->kyc_driving_enable_for_trade != STATUS_ACTIVE)) {
                    return response()->json(['success' => false, 'message' => __('Minimum one type of verification should be enabled for trade')]);
                }
                $response = $this->settingRepo->saveAdminSetting($request);
                return response()->json($response);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }

    // Save common settings
    public function adminCommonSettings(Request $request)
    {
        $rules = [];
        if (!empty($request->logo)) {
            $rules['logo'] = 'image|mimes:jpg,jpeg,png|max:2000';
        }
        if (!empty($request->favicon)) {
            $rules['favicon'] = 'image|mimes:jpg,jpeg,png|max:2000';
        }
        if (!empty($request->login_logo)) {
            $rules['login_logo'] = 'image|mimes:jpg,jpeg,png|max:2000';
        }
        if (!empty($request->coin_price)) {
            $rules['coin_price'] = 'numeric';
        }
        if (!empty($request->number_of_confirmation)) {
            $rules['number_of_confirmation'] = 'integer';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            if ($request->post()) {
                if (isset($request->company_name)) {
                    AdminSetting::where('slug', 'company_name')->update(['value' => $request->company_name]);
                    AdminSetting::where('slug', 'app_title')->update(['value' => $request->company_name]);
                }
                $response = $this->settingRepo->saveAdminSetting($request);
                return response()->json($response);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid request']);
    }

    // Save email settings
    public function adminSaveEmailSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [
                'mail_host' => 'required',
                'mail_port' => 'required',
                'mail_username' => 'required',
                'mail_password' => 'required',
                'mail_encryption' => 'required',
                'mail_from_address' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
            }

            try {
                $response = $this->settingRepo->saveEmailSetting($request);
                return response()->json($response);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }

    // Save SMS settings
    public function adminSaveSmsSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [
                'twillo_secret_key' => 'required',
                'twillo_auth_token' => 'required',
                'twillo_number' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
            }

            try {
                $response = $this->settingRepo->saveTwilloSetting($request);
                return response()->json($response);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }

    // Save referral settings
    public function adminReferralFeesSettings(Request $request)
    {
        if ($request->post()) {
            $rules = [
                'referral_signup_reward' => 'required|numeric',
            ];
            if ($request->fees_level1) {
                $rules['fees_level1'] = 'numeric|min:0|max:100';
            }
            if ($request->fees_level2) {
                $rules['fees_level2'] = 'numeric|min:0|max:100';
            }
            if ($request->fees_level3) {
                $rules['fees_level3'] = 'numeric|min:0|max:100';
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
            }

            try {
                $response = $this->settingRepo->saveReferralSetting($request);
                return response()->json($response);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }

    // admin withdrawal setting save
    public function adminWithdrawalSettings(Request $request)
    {
        if ($request->isMethod('post')) {
            $rules = [
                'coin_name' => 'required',
                'coin_price' => 'required|numeric',
                'contract_coin_name' => 'required',
//                'network_type' => 'required',
                'max_send_limit' => 'required|numeric',
//                'previous_block_count' => 'required|numeric',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 422);
            }

            try {
                $response = $this->settingRepo->saveWithdrawSetting($request);
                if ($response['success']) {
                    return response()->json(['message' => $response['message']], 200);
                } else {
                    return response()->json(['message' => $response['message']], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
        }
    }
    //Contact Us Email List
    public function contactEmailList(Request $request)
    {
        $items = ContactUs::select('*');
        return datatables($items)
            ->addColumn('details', function ($item) {
                return '<button class="btn btn-info show_details" data-id="'.$item->id.'">Details</button>';
            })
            ->removeColumn(['created_at', 'updated_at'])
            ->rawColumns(['details'])
            ->toJson();
    }
    public function getDescriptionByID(Request $request)
    {
        $response = ContactUs::where('id', $request->id)->first();
        return response()->json($response);
    }


    // Faq List
    public function adminFaqList(Request $request)
    {
        if ($request->ajax()) {
            $items = Faq::orderBy('id', 'desc');
            return datatables()->of($items)
                ->addColumn('status', function ($item) {
                    return status($item->status);
                })
                ->addColumn('actions', function ($item) {
                    return '<ul class="d-flex activity-menu">
                    <li class="viewuser"><a href="' . route('adminFaqEdit', $item->id) . '"><i class="fa fa-pencil"></i></a></li>
                    <li class="deleteuser"><a href="' . route('adminFaqDelete', $item->id) . '"><i class="fa fa-trash"></i></a></li>
                    </ul>';
                })
                ->rawColumns(['actions'])
                ->toJson();
        }
        return response()->json(['title' => __('FAQs')]);
    }

    // View Add new faq page
    public function adminFaqAdd()
    {
        return response()->json(['title' => __('Add FAQs')]);
    }

    public function adminFaqSave(Request $request)
    {
        $rules = [
            'question' => 'required',
            'answer' => 'required',
            'status' => 'required',
        ];

        $messages = [
            'question.required' => __('Question field cannot be empty'),
            'answer.required' => __('Answer field cannot be empty'),
            'status.required' => __('Status field cannot be empty'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()], 422);
        }

        $data = [
            'question' => $request->question,
            'answer' => $request->answer,
            'status' => $request->status,
            'author' => Auth::id()
        ];

        try {
            if (!empty($request->edit_id)) {
                Faq::where(['id' => $request->edit_id])->update($data);
                return response()->json(['message' => __('Faq Updated Successfully!')], 200);
            } else {
                Faq::create($data);
                return response()->json(['message' => __('Faq Added Successfully!')], 201);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function adminFaqEdit($id)
    {
        $item = Faq::findOrFail($id);
        return response()->json(['title' => __('Update FAQs'), 'item' => $item]);
    }

    public function adminFaqDelete($id)
    {
        try {
            Faq::where(['id' => $id])->delete();
            return response()->json(['message' => __('Deleted Successfully!')], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    // admin payment setting
    public function adminPaymentSetting()
    {
        $settings = allsetting();
        $paymentMethods = paymentMethods();

        return response()->json([
            'title' => __('Payment Method'),
            'settings' => $settings,
            'payment_methods' => $paymentMethods
        ]);
    }
    // admin referral setting save
    public function adminSavePaymentSettings(Request $request)
    {
        if ($request->isMethod('post')) {
            $rules = [
                'COIN_PAYMENT_PUBLIC_KEY' => 'required',
                'COIN_PAYMENT_PRIVATE_KEY' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()], 422);
            }

            try {
                $response = $this->settingRepo->savePaymentSetting($request);
                if ($response['success']) {
                    return response()->json(['message' => $response['message']], 200);
                } else {
                    return response()->json(['message' => $response['message']], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
        }
    }
    public function adminWithdrawalSetting()
    {
        $settings = allsetting();
        $paymentMethods = paymentMethods();

        return response()->json([
            'title' => __('Withdrawal Settings'),
            'settings' => $settings,
            'payment_methods' => $paymentMethods
        ]);
    }

    // chnage payment method status
    public function changePaymentMethodStatus(Request $request)
    {
        $settings = allsetting();
        foreach ($request->active_id as  $key => $value){
            if(isset($settings[$key]))
                AdminSetting::updateOrCreate(['slug' => $key], ['value' => $value]);
        }

        return response()->json(['message' => __('Status changed successfully')]);
    }

    public function check_wallet_address(Request $request)
    {
        try {
            if (!isset($request->wallet_key)) {
                return response()->json(['success' => false, 'message' => __('Wallet key is missing!')], 400);
            }

            $api = new ERC20TokenApi();
            $requestData = ['contracts' => $request->wallet_key];
            $result = $api->getAddressFromPK($requestData);

            if ($result['success']) {
                return response()->json(['success' => true, 'message' => __('Your wallet address: ') . $result['data']->address], 200);
            } else {
                return response()->json(['success' => false, 'message' => __('Invalid Request')], 400);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
