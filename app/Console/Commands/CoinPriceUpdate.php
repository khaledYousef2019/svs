<?php

namespace App\Console\Commands;

use App\Model\Coin;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use stdClass;

class CoinPriceUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coinprice:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update coins prices job';

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
        $currencies = Coin::where('coingecko_id','<>','')->select('coingecko_id')->get();
        $list = [];
        foreach($currencies as $cur){
            if($cur->coingecko_id){
                $list[] = $cur->coingecko_id;
            }
        }
        $currency_list = implode(',',$list);
        $url = "https://api.coingecko.com/api/v3/simple/price?ids=$currency_list&vs_currencies=usd&include_market_cap=false&include_24hr_vol=true&include_24hr_change=true&include_last_updated_at=true&precision=8";
        $client = new \GuzzleHttp\Client();
        $request = $client->get($url);
        $result = $request->getBody();
        $result = \GuzzleHttp\json_decode($result, true);
        try{
            if($result){
                foreach($result as $key => $res){
//                    $res['coingecko_id'] = $key;
                    Coin::updateOrCreate(['coingecko_id'=>$key],$res);
//                    if($key = 'wfdp'){
//                        $request = new stdClass();
//                        $request->coin_price = $res['usd'];
//                    }
                }
            }
            Log::info('coin price update cron job run successful');
        } catch (Exception $e) {
            Log::error('coin price update cron job run null');
        }
        $this->updateSVS();
    }

    private function updateSVS()
    {
        $url = "https://coincodex.com/api/coincodex/get_coin/svs";
        $client = new \GuzzleHttp\Client();
        $request = $client->get($url);
        $result = $request->getBody();
        $result = \GuzzleHttp\json_decode($result, true);
        if($result){
            $data = [
                'usd' => $result['last_price_usd'],
                'usd_24h_change' => $result['price_change_1D_percent'],
                'coin_rank' => $result['market_cap_rank'],
                'description' => $result['description'],
                'website' => $result['website']
            ];
            Coin::where('type', DEFAULT_COIN_TYPE)->update($data);
            Log::info('svs price update cron job run successful');
        }
    }
}
