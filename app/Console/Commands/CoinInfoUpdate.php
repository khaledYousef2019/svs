<?php

namespace App\Console\Commands;

use App\Model\Coin;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CoinInfoUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coininfo:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update coin information using the CoinGecko API';

    /**
     * Guzzle HTTP client instance.
     *
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * Create a new command instance.
     *
     * @param \GuzzleHttp\Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $apiBaseUrl = config('services.coingecko.base_url', 'https://api.coingecko.com/api/v3/coins');
        $sleepTime = config('services.coingecko.sleep_time', 30);

        Coin::where('coingecko_id', '<>', '')
            ->select('id', 'coingecko_id')
            ->chunk(50, function ($coins) use ($apiBaseUrl, $sleepTime) {
                foreach ($coins as $coin) {
                    $this->updateCoinInfo($coin, $apiBaseUrl);
                    sleep($sleepTime);
                }
            });

        Log::info('Coin information update completed successfully.');
        return 0;
    }

    /**
     * Update coin information from the CoinGecko API.
     *
     * @param \App\Model\Coin $coin
     * @param string $apiBaseUrl
     */
    private function updateCoinInfo(Coin $coin, string $apiBaseUrl)
    {
        $url = "$apiBaseUrl/{$coin->coingecko_id}?localization=false&tickers=false&market_data=true&community_data=false&developer_data=false&sparkline=false";

        try {
            $response = $this->client->get($url);
            $result = json_decode($response->getBody(), true);

            if ($result) {
                $updateData = $this->mapCoinData($result);

                Coin::updateOrCreate(['coingecko_id' => $coin->coingecko_id], $updateData);

                Log::info("Coin '{$coin->coingecko_id}' updated successfully.");
            } else {
                Log::warning("No data returned for coin '{$coin->coingecko_id}'.");
            }
        } catch (Exception $e) {
            Log::error("Error updating coin '{$coin->coingecko_id}': {$e->getMessage()}");
        }
    }

    /**
     * Map API response data to the database structure.
     *
     * @param array $data
     * @return array
     */
    private function mapCoinData(array $data): array
    {
        return [
            'description' => $data['description']['en'] ?? null,
            'website' => $data['links']['homepage'][0] ?? null,
            'coin_icon' => $data['image']['large'] ?? null,
            'coin_rank' => $data['market_cap_rank'] ?? null,
            'subbly' => $data['market_data']['total_supply'] ?? null,
        ];
    }
}
