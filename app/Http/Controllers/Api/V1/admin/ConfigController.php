<?php

namespace App\Http\Controllers\Api\V1\admin;

use App\Http\Controllers\Controller;
use App\Model\AdminSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ConfigController extends Controller
{
    // Admin configuration
    public function adminConfiguration()
    {
        // Provide any necessary data for the configuration page
        return response()->json([
            'success' => true,
            'title' => __("Configuration")
        ]);
    }

    // Run command
    public function adminRunCommand(Request $request, $type)
    {
        $message = __('Nothing to execute');
        try {
            switch ($type) {
                case COMMAND_TYPE_WALLET:
                    Artisan::call('adjust-wallet-coin');
                    $message = __('Coin wallet command executed');
                    break;
                case COMMAND_TYPE_MIGRATE:
                    Artisan::call('migrate');
                    $message = __('Migrate successfully');
                    break;
                case COMMAND_TYPE_CACHE:
                    Artisan::call('cache:clear');
                    $message = __('Application cache cleared successfully');
                    break;
                case COMMAND_TYPE_CONFIG:
                    Artisan::call('config:clear');
                    $message = __('Application config cleared successfully');
                    break;
                case COMMAND_TYPE_VIEW:
                case COMMAND_TYPE_ROUTE:
                    Artisan::call('view:clear');
                    Artisan::call('route:clear');
                    $message = __('Application view cleared successfully');
                    break;
                case COMMAND_TYPE_PASSPORT_INSTALL:
                    Artisan::call('passport:install');
                    $message = __('Personal access client created successfully');
                    break;
                case COMMAND_TYPE_TRADE_FEES:
                    $this->adjustTradeFeesSettings();
                    $message = __('Trade fees setting configured successfully');
                    break;
                case COMMAND_TYPE_TOKEN_DEPOSIT:
                    Artisan::call('custom-token-deposit');
                    $message = __('Custom token deposit command run once successfully');
                    break;
                case COMMAND_TYPE_ADJUST_TOKEN_DEPOSIT:
                    Artisan::call('adjust-token-deposit');
                    $message = __('Adjust custom token deposit command run once successfully');
                    break;
                case COMMAND_TYPE_DISTRIBUTE_MEMBERSHIP_BONUS:
                    Artisan::call('command:membershipbonus');
                    $message = __('Membership bonus distribution command run once successfully');
                    break;
                default:
                    $message = __('Invalid command type');
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Command exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Adjust trade fees settings
    public function adjustTradeFeesSettings()
    {
        try {
            AdminSetting::updateOrCreate(['slug' => 'trade_limit_1'], ['value' => 0]);
            AdminSetting::updateOrCreate(['slug' => 'maker_1'], ['value' => 0]);
            AdminSetting::updateOrCreate(['slug' => 'taker_1'], ['value' => 0]);

            return response()->json([
                'success' => true,
                'message' => __('Trade fees settings adjusted successfully')
            ]);

        } catch (\Exception $e) {
            Log::error('Adjust trade fees settings exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

