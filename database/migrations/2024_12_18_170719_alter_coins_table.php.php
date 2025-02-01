<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coins', function (Blueprint $table) {
            $table->string('coingecko_id', 50)->nullable()->after('is_base');
            $table->string('coincodex_id', 50)->nullable()->after('coingecko_id');
            $table->integer('coin_rank')->nullable()->after('is_base');
            $table->decimal('usd', 19, 8)->nullable()->after('is_base');
            $table->string('subbly', 50)->nullable()->after('is_base');
            $table->text('description')->nullable()->after('is_base');
            $table->string('website', 255)->nullable()->after('is_base');
            $table->decimal('usd_24h_vol', 19, 8)->nullable()->after('coin_icon');
            $table->decimal('usd_24h_change', 19, 8)->nullable()->after('usd_24h_vol');
            $table->decimal('last_updated_at', 19, 8)->nullable()->after('usd_24h_change');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
