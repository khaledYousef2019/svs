<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chains', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->string('network_type', 55)->nullable();
            $table->text('chain_links')->nullable();
            $table->string('chain_id', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->integer('previous_block_count')->nullable();
            $table->bigInteger('gas_limit')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chains');
    }
}
