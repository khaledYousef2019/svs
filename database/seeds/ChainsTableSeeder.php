<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChainsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('chains')->insert([
            [
                'name' => 'Ethereum',
                'network_type' => '4',
                'chain_links' => json_encode(['https://binance.llamarpc.com']),
                'chain_id' => '56',
                'previous_block_count' => 5000,
                'gas_limit' => 43000,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Binance Smart Chain',
                'network_type' => '5',
                'chain_links' => json_encode(['https://binance.llamarpc.com']),
                'chain_id' => '56',
                'previous_block_count' => 5000,
                'gas_limit' => 43000,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Polygon Mainnet',
                'network_type' => '4',
                'chain_links' => json_encode(['https://binance.llamarpc.com']),
                'chain_id' => '56',
                'previous_block_count' => 5000,
                'gas_limit' => 43000,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tron',
                'network_type' => '6',
                'chain_links' => json_encode(['https://binance.llamarpc.com']),
                'chain_id' => '56',
                'previous_block_count' => 5000,
                'gas_limit' => 43000,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
