<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCreateUser;
use App\Http\Services\CommonService;
use App\Model\Coin;
use App\Model\UserVerificationCode;
use App\Model\VerificationDetails;
use App\Model\Wallet;
use App\Traits\Filterable;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    use Filterable;
    // user list
    public function adminUsers(Request $request)
    {
        $request->type = $request->input('type', 'active_users');

        $query = User::query()->where('id', '<>', Auth::user()->id);
        switch ($request->type) {
            case 'active_users':
                $query->where('status', STATUS_SUCCESS);
                break;
            case 'suspend_user':
                $query->where('status', STATUS_SUSPENDED);
                break;
            case 'deleted_user':
                $query->where('status', STATUS_DELETED);
                break;
            case 'email_pending':
                $query->where('is_verified', '!=', STATUS_SUCCESS);
                break;
            case 'phone_pending':
                $query->where('phone_verified', '!=', STATUS_SUCCESS);
                break;
        }
        $items = $this->applyFiltersAndSorting($query, $request);
        $data = $items->getCollection()->transform(function ($item) use ($request) {
            $item->status = statusAction($item->status);
            $item->type = userRole($item->role);
//            $item->created_at = $item->created_at ? with(new Carbon($item->created_at))->format('d M Y') : '';
            $item->action = new \stdClass();
            if($request->type == 'active_users') {
                $item->action->View = route('adminUserProfile').'?id='.encrypt($item->id).'&type=view';
                $item->action->Edit = route('admin.UserEdit').'?id='.encrypt($item->id).'&type=edit';
                $item->action->Suspend = route('admin.user.suspend',encrypt($item->id));
                if(!empty($item->google2fa_secret))
                    $item->action->gauth = route('admin.user.remove.gauth',encrypt($item->id));
                $item->action->Delete = route('admin.user.delete',encrypt($item->id));

            }elseif($request->type == 'suspend_user' || $request->type == 'deleted_user') {
                $item->action->View = route('admin.UserEdit') . '?id=' . encrypt($item->id) . '&type=view"';
                $item->action->Active = route('admin.user.active', encrypt($item->id));
            }elseif($request->type == 'email_pending'){
                $item->action->Email_verify = route('admin.user.email.verify',encrypt($item->id));
            }elseif($request->type == 'phone_pending')
                $item->action->Phone_verify = route('admin.user.phone.verify',encrypt($item->id));
            return $item;
        });

        // Return JSON response for React
        return response()->json([
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
    }

    // create and edit user
    public function UserAddEdit(AdminCreateUser $request)
    {
        DB::beginTransaction();
        try {
            if (!filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
                return redirect()->back()->withInput()->with('dismiss', __('Invalid email address'));
            }
            $mail_key = $this->generate_email_verification_key();
            $user = User::create([
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'role' => $request->role,
                'phone' => $request->phone,
                'status' => STATUS_SUCCESS,
                'password' => Hash::make(randomString(8)),
            ]);

            $coin = Coin::where('type', DEFAULT_COIN_TYPE)->first();
            Wallet::create([
                'user_id' => $user->id,
                'name' => DEFAULT_COIN_TYPE.' wallet',
                'status' => STATUS_SUCCESS,
                'is_primary' => STATUS_SUCCESS,
                'balance' => 0.0000000,
                'coin_id' => $coin->id,
                'coin_type' => $coin->type,
            ]);

            app(CommonService::class)->generateNewCoinWallet($user->id);

            $key = randomNumber(6);
            $existsToken = User::join('user_verification_codes', 'user_verification_codes.user_id', 'users.id')
                ->where('user_verification_codes.user_id', $user->id)
                ->whereDate('user_verification_codes.expired_at', '>=', Carbon::now()->format('Y-m-d'))
                ->first();

            if ( !empty($existsToken) ) {
                $token = $existsToken->code;
            } else {
                $s = UserVerificationCode::create(['user_id' => $user->id, 'code' => $key, 'expired_at' => date('Y-m-d', strtotime('+15 days')), 'status' => STATUS_PENDING]);
                $token = $key;
            }

            $user_data = [
                'email' => $user->email,
                'token' => $token,
            ];

            DB::commit();
            try {
                Mail::send('email.password_reset', $user_data, function ($message) use ($user) {
                    $message->to($user->email, $user->username)->from(settings('mail_from'), env('APP_NAME'))->subject('Change password');
                });
                $data['message'] = 'Mail sent Successfully to ' . $user->email . ' with Password reset Code.';
                $data['success'] = true;
                Session::put(['resend_email' => $user->email]);

                return response()->json(['success' => true , 'message' => $data['message']], 200);
            } catch (\Exception $e) {
                return response()->json(['success' => true , 'message' => __('New user created successfully but Mail not sent')], 200);
            }
            // all good
        } catch (\Exception $e) {
            DB::rollback();
        }

        if ( !empty($user) ) {
            $userName = $user->first_name . ' ' . $user->last_name;
            $userEmail = $user->email;
            $subject = __('Email Verification | :companyName', ['companyName' => env('APP_NAME')]);
            $data['data'] = $user;
            $data['key'] = $mail_key;

            Mail::send('email.verifyWeb', $data, function ($message) use ($user) {
                $message->to($user->email, $user->username)->from('noreply@monttra.com', env('APP_NAME'))->subject('Email confirmation');
            });

            return response()->json(['success' => true ,'message' => 'Mail sent successfully to ' . $user->email . ' with Password reset code.'], 200);
        } else {
            return response()->json(['success' => false ,'message' => __('Something went wrong')], 500);
        }
    }

    // generate verification key
    private function generate_email_verification_key()
    {
        $key = randomNumber(6);
        return $key;
    }
    // user edit page
    public function adminUserProfile(Request $request)
    {
        $user = User::find(decrypt($request->id));
        $user->plan_info = get_plan_info($user->id);

        if ($user) {
            return response()->json(['user' => $user, 'type' => $request->type], 200);
        }

        return response()->json(['message' => __('User not found')], 404);
    }

    // user edit page
    public function UserEdit(Request $request)
    {

        $user = User::find(decrypt($request->id));

        if ($user) {
            $user->enc_id = encrypt($user->id);
            return response()->json(['user' => $user, 'type' => $request->type], 200);
        }

        return response()->json(['message' => __('User not found')], 404);
    }

    // verify user phone
    public function adminUserPhoneVerified($id)
    {
        $user = User::find(decrypt($id));

        if (!$user || empty($user->phone)) {
            return response()->json(['message' => __('User phone number is empty')], 400);
        }

        $user->phone_verified = STATUS_SUCCESS;
        $user->save();

        return response()->json(['message' => __('Phone verified successfully')], 200);
    }

    // delete user
    public function adminUserDelete($id)
    {
        $user = User::find(decrypt($id));
        if ($user) {
            $user->status = STATUS_DELETED;
            $user->save();
            return response()->json(['message' => 'User deleted successfully'], 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    // suspend user
    public function adminUserSuspend($id)
    {
        $user = User::find(decrypt($id));
        if ($user) {
            $user->status = STATUS_SUSPENDED;
            $user->save();
            return response()->json(['message' => 'User suspended successfully'], 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    // remove user gauth
    public function adminUserRemoveGauth($id)
    {
        $user = User::find(decrypt($id));
        if ($user) {
            $user->google2fa_secret = '';
            $user->g2f_enabled  = '0';
            $user->save();
            return response()->json(['message' => 'User gauth removed successfully'], 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    // activate user
    public function adminUserActive($id)
    {
        $user = User::find(decrypt($id));
        if ($user) {
            $user->status = STATUS_SUCCESS;
            $user->save();
            return response()->json(['message' => 'User activated successfully'], 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    // verify user email
    public function adminUserEmailVerified($id)
    {
        $user = User::find(decrypt($id));
        if ($user) {
            $user->is_verified = STATUS_SUCCESS;
            $user->save();
            return response()->json(['message' => 'Email verified successfully'], 200);
        }

        return response()->json(['message' => 'User not found'], 404);
    }

    //ID Verification
    public function adminUserIdVerificationPending(Request $request)
    {
        $query = VerificationDetails::join('users', 'users.id', 'verification_details.user_id')
            ->select('users.id', 'users.updated_at', 'users.first_name', 'users.last_name', 'users.email','verification_details.status as deposit_status','verification_details.created_at')
            ->groupBy('user_id');
        $items = $this->applyFiltersAndSorting($query, $request);
        $data = $items->getCollection()->transform(function ($item) {
            $item->deposit_status = deposit_status($item->deposit_status);
            $item->action = new \stdClass();
            $item->action->Details = route('adminUserDetails', encrypt($item->id)) . '?tab=photo_id';
            return $item;
        });


        return response()->json([
            'data' => $data,
            'recordsTotal' => $items->total(),
            'recordsFiltered' => $items->total(), // Adjust if there are filters
            'draw' => $request->input('draw'), // Include draw for DataTables
        ]);
    }

    // verification details
    public function VerificationDetails($id)
    {
        $userId = decrypt($id);
        $pending = VerificationDetails::where('user_id', $userId)
            ->where('status', STATUS_PENDING)
            ->get();

        $groupedFiles = [];

        foreach ($pending as $file) {
            $file->photo = asset(IMG_USER_PATH . $file->photo);
            $file->status = deposit_status($file->status);
            $type = '';

            // Determine document type
            if ($file->field_name == 'nid_front' || $file->field_name == 'nid_back') {
                $type = 'nid';
            } elseif ($file->field_name == 'pass_back' || $file->field_name == 'pass_front') {
                $type = 'passport';
            } else {
                $type = 'driving';
            }

            // Create action URLs
            $action = new \stdClass();
            $action->Accept = route('adminUserVerificationActive', [encrypt($file->user_id), $type]);
            $action->Reject = route('verificationReject', [encrypt($file->user_id), $type]);

            // Group files by type
            if (!isset($groupedFiles[$type])) {
                $groupedFiles[$type] = [
                    'type' => $type,
                    'status' => $file->status,  // Set a single status for the type
                    'action' => $action,        // Set a single action object for the type
                    'files' => []
                ];
            }

            $groupedFiles[$type]['files'][] = [
                'id' => $file->id,
                'field_name' => $file->field_name,
                'photo' => $file->photo,
                'created_at' => $file->created_at,
                'updated_at' => $file->updated_at
            ];
        }

        // Retrieve field names and IDs
        $fieldsName = VerificationDetails::where('user_id', $userId)
            ->where('status', STATUS_PENDING)
            ->get()
            ->pluck('id', 'field_name')
            ->toArray();

        if (!empty($groupedFiles)) {
            return response()->json([
                'grouped_files' => $groupedFiles,
                'fields_name' => $fieldsName
            ]);
        }

        return response()->json(['error' => 'No verification details found'], 404);
    }


    // activate user verification
    public function adminUserVerificationActive(Request $request, $id, $type)
    {
        try {
            $userId = decrypt($id);
            $verified = [];

            switch ($type) {
                case 'nid':
                    $verified = ['nid_front', 'nid_back'];
                    break;
                case 'driving':
                    $verified = ['drive_front', 'drive_back'];
                    break;
                case 'passport':
                    $verified = ['pass_front', 'pass_back'];
                    break;
                default:
                    return response()->json(['error' => 'Invalid type'], 400);
            }

            VerificationDetails::where('user_id', $userId)
                ->whereIn('field_name', $verified)
                ->update(['status' => STATUS_SUCCESS]);

            return response()->json(['success' => 'Successfully Updated']);
        } catch (\Exception $exception) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }


    // verification reject process
    public function verificationReject(Request $request)
    {
        try {
            $companyName = env('APP_NAME');
            $userId = decrypt($request->user_id);
            $user = User::find($userId);
            $cause = $request->couse;
            $email = $user->email;

            $data = [
                'data' => $user,
                'cause' => $cause,
                'email' => $email
            ];

            Mail::send('email.verification_fields', $data, function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->from(settings('mail_from'), env('APP_NAME'))
                    ->subject('Id Verification');
            });

            $docs = VerificationDetails::where('type', $request->type);
                foreach ($docs as $value) {
                    deleteFile(IMG_USER_PATH, $value->photo);
                }


            $docs->update(['status' => STATUS_REJECTED, 'photo' => '']);

            return response()->json(['success' => 'Rejected successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sendIdVerificationEmail($data, $user)
    {
        Mail::send('email.verification_fields', $data, function ($message) use ($user) {
            $message->to($user->email, $user->name)
                ->from(settings('mail_from'), env('APP_NAME'))
                ->subject('Id Verification');
        });
        return response()->json(['success' => 'Email sent successfully']);
    }

}
