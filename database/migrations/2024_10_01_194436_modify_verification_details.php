<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyVerificationDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('verification_details', function (Blueprint $table) {
            $table->enum('type', ['nid', 'passport', 'driving'])->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('verification_details', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
