<?php

namespace App\Jobs;

use App\Model\Chain;
use App\Repository\CustomTokenRepository;
use App\Services\Logger;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GetDepositBalanceFormUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 0;
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $logger = new Logger();
        $logger->log('GetDepositBalanceFormUserJob', 'called');
        try {
            $logger->log('GetDepositBalanceFormUserJob', 'process start');
            $repo = new CustomTokenRepository();
            $chains = Chain::join('real_wallets','real_wallets.chain_id','=','chains.id')
                ->join('tokens','tokens.chain_id','=','chains.id')
                ->where('chains.status',STATUS_ACTIVE)
                ->select('chains.*','real_wallets.*','tokens.*','chains.name as chain_name')
                ->get();
            foreach ($chains as $chain) {
                $repo->setCurrentChain($chain);
                $repo->getDepositTokenFromUser();
            }
        } catch (\Exception $e) {
            $logger->log('GetDepositBalanceFormUserJob', $e->getMessage());
        }
        $logger->log('GetDepositBalanceFormUserJob', 'end');
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::info(json_encode($exception));
        // Send user notification of failure, etc...
    }
}
