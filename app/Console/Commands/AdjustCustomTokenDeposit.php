<?php

namespace App\Console\Commands;

use App\Jobs\GetDepositBalanceFormUserJob;
use App\Model\Chain;
use App\Repository\CustomTokenRepository;
use App\Services\Logger;
use Illuminate\Console\Command;

class AdjustCustomTokenDeposit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adjust-token-deposit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adjust token deposit';

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
        storeDetailsException('adjust token deposit command', 'Called');
        $repo = new CustomTokenRepository();
        $chains = Chain::join('real_wallets','real_wallets.chain_id','=','chains.id')
            ->join('tokens','tokens.chain_id','=','chains.id')
            ->where('chains.status',STATUS_ACTIVE)
            ->select('chains.*','real_wallets.*','tokens.*','chains.name as chain_name')
            ->get();
        foreach ($chains as $chain) {
            $repo->setCurrentChain($chain);
            $repo->getDepositTokenFromUser();
            storeDetailsException($chain->chain_name.' adjust token deposit command', 'executed');
        }
    }
}
