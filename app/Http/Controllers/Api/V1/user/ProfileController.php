<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\Http\Requests\driveingVerification;
use App\Http\Requests\passportVerification;
use App\Http\Requests\resetPasswordRequest;
use App\Http\Requests\UserProfileUpdate;
use App\Http\Requests\verificationNid;
use App\Http\Services\AuthService;
use App\Http\Services\SmsService;
use App\Model\ActivityLog;
use App\Model\User\Wallet;
use App\Model\VerificationDetails;
use App\User;
use Clickatell\Rest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use PragmaRX\Google2FA\Google2FA;

class ProfileController extends Controller
{
    //my profile
    public function userProfile(Request $request)
    {


        $data = [
            'title' => __('My Profile'),
            'user' => User::find(Auth::id()),
            'clubInfos' => get_plan_info(Auth::id()),
            'nid_front' => tap(VerificationDetails::where('user_id', Auth::id())->where('field_name', 'nid_front')->first(), function ($item) {
                if ($item) {
                    $item->photo = asset(IMG_USER_PATH.$item->photo);
                    $item->deposit_status = deposit_status($item->status);
                    unset($item->status);
                }
            }),
            'nid_back' => tap(VerificationDetails::where('user_id', Auth::id())->where('field_name', 'nid_back')->first(), function ($item) {
                if ($item) {
                    $item->photo = asset(IMG_USER_PATH.$item->photo);
                    $item->deposit_status = deposit_status($item->status);
                    unset($item->status);
                }
            }),
            'pass_front' => tap(VerificationDetails::select()->where('user_id', Auth::id())->where('field_name', 'pass_front')->first(), function ($item) {
                if ($item) {
                    $item->photo = asset(IMG_USER_PATH.$item->photo);
                    $item->deposit_status = deposit_status($item->status);
                    unset($item->status);
                }
            }),
            'pass_back' => tap(VerificationDetails::where('user_id', Auth::id())->where('field_name', 'pass_back')->first(), function ($item) {
                if ($item) {
                    $item->photo = asset(IMG_USER_PATH.$item->photo);
                    $item->deposit_status = deposit_status($item->status);
                    unset($item->status);
                }
            }),
            'drive_front' => tap(VerificationDetails::where('user_id', Auth::id())->where('field_name', 'drive_front')->first(), function ($item) {
                if ($item) {
                    $item->photo = asset(IMG_USER_PATH.$item->photo);
                    $item->deposit_status = deposit_status($item->status);
                    unset($item->status);
                }
            }),
            'drive_back' => tap(VerificationDetails::where('user_id', Auth::id())->where('field_name', 'drive_back')->first(), function ($item) {
                if ($item) {
                    $item->photo = asset(IMG_USER_PATH.$item->photo);
                    $item->deposit_status = deposit_status($item->status);
                    unset($item->status);
                }
            }),
            'countries' => country(),
            'qr' => $request->get('qr', 'profile-tab')
        ];
        $google2fa = new Google2FA();
        $google2fa->setAllowInsecureCallToGoogleApis(true);
        $data['google2fa_secret'] = $google2fa->generateSecretKey();

        if ($request->ajax()) {
            $activities = ActivityLog::where('user_id', Auth::id())->select('*');
            return response()->json([
                'activities' => $activities->get()->map(function ($item) {
                    return [
                        'action' => userActivity($item->action),
                        'details' => $item
                    ];
                })
            ]);
        }

        return response()->json($data);
    }



    // profile upload image
    public function uploadProfileImage(Request $request)
    {
        $validator = \Validator::make($request->all(), [
//            'file_one' => 'required|image|max:2048|mimes:jpg,jpeg,png,gif,svg|dimensions:max_width=500,max_height=500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $img = $request->file('file_one');
            $user = $request->has('id') ? User::find(decrypt($request->id)) : Auth::user();

            if ($img) {
                $photo = uploadFile($img, IMG_USER_PATH, $user->photo ?? '');
                $user->photo = $photo;
                $user->save();
                return response()->json(['success' => true, 'message' => __('Profile picture uploaded successfully'),'data'=>['image' => show_image($user->id,'user')]]);
            }

            return response()->json(['success' => false, 'message' => __('Please input an image')], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }



    // update user profile
    public function userProfileUpdate(UserProfileUpdate $request)
    {
        if (strpos($request->phone, '+') !== false) {
            return response()->json(['success' => false, 'message' => __("Don't put plus sign with phone number")], 400);
        }

        $user = $request->has('id') ? User::find(decrypt($request->id)) : Auth::user();
        $data = $request->only(['first_name', 'last_name', 'country', 'gender']);

        if ($user->phone != $request->phone) {
            $data['phone'] = $request->phone;
            $data['phone_verified'] = 0;
        }

        $user->update($data);

        return response()->json(['success' => true, 'message' => __('Profile updated successfully')]);
    }


    // send sms
    public function sendSMS()
    {
        $user = Auth::user();

        if (!empty($user->phone)) {
            $key = Cookie::get('code') ?? randomNumber(8);
            Cookie::queue(Cookie::make('code', $key, 100 * 60));

            try {
                $text = __('Your verification code is ') . $key;
                $number = $user->phone;

                if (settings('sms_getway_name') == 'twillo') {
                    app(SmsService::class)->send("+".$number, $text);
                }

                return response()->json(['success' => true, 'message' => __('We sent a verification code to your phone')]);
            } catch (\Exception $exception) {
                Cookie::queue(Cookie::forget('code'));
                return response()->json(['success' => false, 'message' => __('Something went wrong. Please contact your system admin.')], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'You should add your phone number first.'], 400);
    }


    // phone verification process
    public function phoneVerify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $cookie = Cookie::get('code');
        if ($cookie) {
            if ($request->code == $cookie) {
                $user = User::find(Auth::id());
                $user->phone_verified = 1;
                $user->save();
                Cookie::queue(Cookie::forget('code'));

                return response()->json(['success' => true, 'message' => __('Phone verified successfully.')]);
            }

            return response()->json(['success' => false, 'message' => __('You entered the wrong OTP.')], 400);
        }

        return response()->json(['success' => false, 'message' => __('Your OTP has expired.')], 400);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function nidUpload(verificationNid $request)
    {
        $img = $request->file('file_two');
        $img2 = $request->file('file_three');

        if ($img) {
            $details = VerificationDetails::firstOrNew([
                'user_id' => Auth::id(),
                'field_name' => 'nid_front',
                'type' => 'nid'
            ]);

            $details->photo = uploadFile($img, IMG_USER_PATH, $details->photo ?? '');
            $details->status = STATUS_PENDING;
            $details->save();
        }

        if ($img2) {
            $details = VerificationDetails::firstOrNew([
                'user_id' => Auth::id(),
                'field_name' => 'nid_back',
                'type' => 'nid'
            ]);

            $details->photo = uploadFile($img2, IMG_USER_PATH, $details->photo ?? '');
            $details->status = STATUS_PENDING;
            $details->save();
        }

        return response()->json(['success' => true, 'message' => __('NID photo uploaded successfully')]);
    }


    // upload passport
    public function passUpload(passportVerification $request)
    {
        $img = $request->file('file_two');
        $img2 = $request->file('file_three');

        if ($img) {
            $details = VerificationDetails::firstOrNew([
                'user_id' => Auth::id(),
                'field_name' => 'pass_front',
                'type' => 'passport'
            ]);

            $details->photo = uploadFile($img, IMG_USER_PATH, $details->photo ?? '');
            $details->status = STATUS_PENDING;
            $details->save();
        }

        if ($img2) {
            $details = VerificationDetails::firstOrNew([
                'user_id' => Auth::id(),
                'field_name' => 'pass_back',
                'type' => 'passport'
            ]);

            $details->photo = uploadFile($img2, IMG_USER_PATH, $details->photo ?? '');
            $details->status = STATUS_PENDING;
            $details->save();
        }

        return response()->json(['success' => true, 'message' => __('Passport photo uploaded successfully')]);
    }


    // driving licence upload
    public function driveUpload(driveingVerification $request)
    {
        $img = $request->file('file_two');
        $img2 = $request->file('file_three');

        if ($img) {
            $details = VerificationDetails::firstOrNew([
                'user_id' => Auth::id(),
                'field_name' => 'drive_front',
                'type' => 'driving'
            ]);

            $details->photo = uploadFile($img, IMG_USER_PATH, $details->photo ?? '');
            $details->status = STATUS_PENDING;
            $details->save();
        }

        if ($img2) {
            $details = VerificationDetails::firstOrNew([
                'user_id' => Auth::id(),
                'field_name' => 'drive_back',
                'type' => 'driving'
            ]);

            $details->photo = uploadFile($img2, IMG_USER_PATH, $details->photo ?? '');
            $details->status = STATUS_PENDING;
            $details->save();
        }

        return response()->json(['success' => true, 'message' => __('Driving license photo uploaded successfully')]);
    }

    public function changePasswordSave(resetPasswordRequest $request)
    {
        $service = new AuthService();
        $change = $service->changePassword($request);
        if ($change['success']) {
            return response()->json(['success' => true, 'message' => $change['message']]);
        } else {
            return response()->json(['success' => false, 'message' => $change['message']], 400);
        }
    }

}
