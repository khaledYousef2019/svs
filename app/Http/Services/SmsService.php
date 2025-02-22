<?php
/**
 * Created by PhpStorm.
 * User: khaled
 * Date: 30/10/24
 * Time: 1:34 PM
 */

namespace App\Http\Services;

use Aloha\Twilio\Twilio;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $twilio;

    public function __construct()
    {
        $sid = allsetting('twillo_secret_key');
        $token = allsetting('twillo_auth_token');
        $from = allsetting('twillo_number');

        $this->twilio = new Twilio($sid, $token, $from);
    }

    public function send($number, $message)
    {
        try {
            $this->twilio->message($number, $message);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return false;
        }
        return true;
    }
}
