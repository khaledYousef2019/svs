<?php

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PragmaRX\Google2FA\Google2FA;

class SettingController extends Controller
{
    // user setting
    public function userSetting()
    {
        $data['title'] = __('Settings');
//        $default = $data['adm_setting'] = allsetting();
        $google2fa = new Google2FA();
        $google2fa->setAllowInsecureCallToGoogleApis(true);
        $data['google2fa_secret'] = $google2fa->generateSecretKey();

        $google2fa_url = $google2fa->getQRCodeGoogleUrl(
            isset($default['app_title']) && !empty($default['app_title']) ? $default['app_title'] : 'cPoket',
            isset(Auth::user()->email) && !empty(Auth::user()->email) ? Auth::user()->email : 'cpoket@email.com',
            $data['google2fa_secret']
        );

        return response()->json([
            'title' => $data['title'],
            'google2fa_secret' => $data['google2fa_secret'],
            'qrcode' => $google2fa_url,
//            'settings' => $data['adm_setting']
        ]);
    }

    // google 2fa secret save
    public function g2fSecretSave(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'google2fa_secret' => 'required|string',
            'remove' => 'sometimes|boolean'
        ]);

        $user = User::find(Auth::id());
        $google2fa = new Google2FA();

        if ($request->remove) {
            if (!empty($user->google2fa_secret)) {
                $valid = $google2fa->verifyKey($user->google2fa_secret, $request->code);
                if ($valid) {
                    $user->google2fa_secret = null;
                    $user->g2f_enabled = '0';
                    $user->save();
                    return response()->json(['success' => true, 'message' => __('Google authentication code removed successfully')]);
                }
            }
            return response()->json(['success' => false, 'message' => __('Google authentication code is invalid')], 400);
        } else {
            $valid = $google2fa->verifyKey($request->google2fa_secret, $request->code);
            if ($valid) {
                $user->google2fa_secret = $request->google2fa_secret;
                $user->g2f_enabled = 1;
                $user->save();
                return response()->json(['success' => true, 'message' => __('Google authentication code added successfully')]);
            }
            return response()->json(['success' => false, 'message' => __('Google authentication code is invalid')], 400);
        }
    }

    // enable google login
    public function googleLoginEnable(Request $request)
    {
        $user = Auth::user();

        if (!empty($user->google2fa_secret)) {
            $user->g2f_enabled = $user->g2f_enabled == 1 ? '0' : '1';
            Session::put('g2f_checked', $user->g2f_enabled == 1);
            $user->update();

            return response()->json([
                'success' => true,
                'message' => $user->g2f_enabled == 1
                    ? __('Google two-factor authentication is enabled')
                    : __('Google two-factor authentication is disabled')
            ]);
        }

        return response()->json(['success' => false, 'message' => __('For using Google two-factor authentication, please set up your authentication')], 400);
    }


    // save preference
    public function savePreference(Request $request)
    {
        $request->validate([
            'lang' => 'required|string'
        ]);

        try {
            User::where('id', Auth::id())->update(['language' => $request->lang]);
            return response()->json(['success' => true, 'message' => __('Language changed successfully')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => __('Something went wrong.')], 500);
        }
    }

}
