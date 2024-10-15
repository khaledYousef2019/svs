<?php

namespace App\Console\Commands;

use App\Model\AdminSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SvsPriceUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SvsPriceUpdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get svs price update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url = "https://coincodex.com/api/coincodex/get_coin/svs";
        $client = new \GuzzleHttp\Client();
        $request = $client->get($url);// Url of your choosing
        $result = $request->getBody();
        $result = \GuzzleHttp\json_decode($result, true);
        if($result){
            AdminSetting::where('slug', 'coin_price')->update(['value' => $result['last_price_usd']]);
            Log::info('svs price update cron job run successful');
        }
    }
}
