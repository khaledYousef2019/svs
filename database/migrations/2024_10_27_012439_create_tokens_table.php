<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('chain_id')->unsigned();
            $table->foreign('chain_id')->references('id')->on('chains')->onDelete('cascade');
            $table->string('name', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('symbol', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->text('contract_address')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->string('holder_address', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->bigInteger('cap')->nullable();
            $table->bigInteger('supply')->nullable();
            $table->bigInteger('decimals')->nullable();
            $table->decimal('price', 18, 8)->nullable();
            $table->string('base_pair', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->string('network', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->bigInteger('withdraw_fee')->nullable();
            $table->decimal('withdraw_max', 18, 8)->nullable();
            $table->decimal('withdraw_min', 18, 8)->nullable();
            $table->string('coingecko_id', 50)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->string('coincodex_id', 50)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tokens');
    }
}
