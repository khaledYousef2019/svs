<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChainIdToWalletAddressHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallet_address_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('chain_id')->nullable()->after('coin_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallet_address_histories', function (Blueprint $table) {
            $table->dropColumn('chain_id');
        });
    }
}
