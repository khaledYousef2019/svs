<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

//use App\Model\CoinHistory;
use Illuminate\Http\Request;


class HomeController extends Controller
{

//    public function index(Request $request){
//        header('Access-Control-Allow-Origin: *');
//        header('Access-Control-Allow-Methods: *');
//        header('Access-Control-Allow-Headers: *');
//        // Log::info('payment notifier called');
//        $request = $request->all();
//        $history = CoinHistory::select('name','coingecko_id','price','price','time');
//        // $where = ['time','>',time()-60*60*24*30];
//        if(isset($request['id'])){
//            $history->where('coingecko_id',$request['id']);
//            // $where['coingecko_id'] = $request['id'];
//        }
//        if(isset($request['time_from']) && isset($request['time_to'])){
//            if($request['time_from'] == 'max'){
//                $request['time_from'] = 0;
//            }
//            if($request['time_to'] == 'max'){
//                $request['time_to'] = time()*1000;
//            }
//            $history->whereBetween('time',[$request['time_from'],$request['time_to']]);
//        }else{
//            $history->where('time','>',(time()-60*60*24*30)*1000);
//        }
//        // dd($where);
//        $history= $history->get();
//        // json_encode($history);
//        // $history=json_decode($history);
//        // echo "<pre>";
//        // $response = ['status'=>1,'data'=>$history];
//        $data = ['success' => true, 'data' => $history, 'message' => __('Coin data'),'timestamp of now'=>time()*1000,'timestamp of search' =>(time()-60*60*24*30)*1000];
//        return response()->json($data);
//
//    }

    public function slideShow(Request $request){
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');

        $_data =[];
        $_data['heading'] = 'WFDP Coin is a unique cryptocurrency that has been developed specifically for members of WFDP';
        $_data['desc'] = 'an online community that focuses on personal development and self-improvement. This digital currency allows members to trade and utilize it on the WFDP personal website, providing them with a convenient and secure way to exchange value within the community. The CEO of WFDP Coin is Amb. Ekramy El Zaghat, a seasoned entrepreneur and blockchain expert with years of experience in the tech industry. He has been instrumental in the development and growth of WFDP Coin, leveraging his expertise to create a robust and reliable cryptocurrency platform that meets the needs of WFDP members. With WFDP Coin, members can enjoy faster, cheaper, and more secure transactions, as well as access to a range of exclusive benefits and rewards. This innovative digital currency is poised to revolutionize the way WFDP members interact with each other, providing a powerful tool for community building and personal growth.';
        $_data['logo'] = 'https://wfdpcoin.org/images/coin-logo-big.png';
        $_data['links'] =[];
        $_data['links'][0]['text'] = 'Buy from Dex-Trade';
        $_data['links'][0]['logo'] = 'https://wfdpcoin.org/images/Dex-Trade.png';
        $_data['links'][0]['links'][0]['link'] = 'https://dex-trade.com/spot/trading/WFDPUSDT';
        $_data['links'][0]['links'][0]['logo'] = 'https://wfdpcoin.org/images/coins/tether.svg';
        $_data['links'][0]['links'][1]['link'] = 'https://dex-trade.com/spot/trading/WFDPTRX';
        $_data['links'][0]['links'][1]['logo'] = 'https://wfdpcoin.org/images/tron-trx-logo.png';
        $_data['links'][1]['text'] = 'Buy from Azbit';
        $_data['links'][1]['logo'] = 'https://wfdpcoin.org/images/Azbit.png';
        $_data['links'][1]['links'][0]['link'] = 'https://dashboard.azbit.com/exchange/WFDP_USDT';
        $_data['links'][1]['links'][0]['logo'] = 'https://wfdpcoin.org/images/coins/tether.svg';
        $_data['links'][1]['links'][1]['link'] = 'https://dashboard.azbit.com/exchange/WFDP_TRX';
        $_data['links'][1]['links'][1]['logo'] = 'https://wfdpcoin.org/images/tron-trx-logo.png';
        $_data['links'][2]['text'] = 'Buy from Bankcex';
        $_data['links'][2]['logo'] = 'https://wfdpcoin.org/images/BankCEX-Logo.png';
        $_data['links'][2]['links'][0]['link'] = 'https://bankcex.com/exchange-base.html?symbol=WFDP_USDT';
        $_data['links'][2]['links'][0]['logo'] = 'https://wfdpcoin.org/images/coins/tether.svg';
        $_data['links'][2]['links'][1]['link'] = 'https://bankcex.com/exchange-base.html?symbol=WFDP_ETH';
        $_data['links'][2]['links'][1]['logo'] = 'https://wfdpcoin.org/images/coins/ethereum.svg';
        $_data['links'][2]['links'][2]['link'] = 'https://bankcex.com/exchange-base.html?symbol=WFDP_BTC';
        $_data['links'][2]['links'][2]['logo'] = 'https://wfdpcoin.org/images/coins/bitcoin.svg';
        $_data['links'][3]['text'] = 'Buy from Pancakeswap';
        $_data['links'][3]['logo'] = 'https://pancakeswap.finance/logo.png';
        $_data['links'][3]['links'][0]['link'] = 'https://pancakeswap.finance/swap?chain=bsc&outputCurrency=BNB&inputCurrency=0x8cd29D79F9376F353c493A7f2Ff9D27dF8d372dE';
        $_data['links'][3]['links'][0]['logo'] = 'https://wfdpcoin.org/images/coins/tether.svg';


        $data = ['success' => true, 'data' => $_data, 'message' => __('success')];
        return response()->json($data);
    }

}
