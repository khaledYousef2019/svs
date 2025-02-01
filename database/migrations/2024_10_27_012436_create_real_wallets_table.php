<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRealWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('real_wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('chain_id')->unsigned();
            $table->foreign('chain_id')->references('id')->on('chains')->onDelete('cascade');
            $table->text('mnemonic')->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->text('xpub')->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->text('private_key')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->text('address')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->timestamps();
        });
    }

    public function down()
    {
//        Schema::table('real_wallets', function (Blueprint $table) {
//            $table->dropForeign(['chain_id']); // Drop the foreign key first
//        });
//        Schema::dropIfExists('real_wallets');

        Schema::dropIfExists('real_wallets');

        Schema::table('real_wallets', function (Blueprint $table) {
            //
        });
    }
}
