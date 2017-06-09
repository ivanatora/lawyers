<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AppointmentsDateTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table){
            $table->dropColumn('for_date');
            $table->date('schedule_date')->nullable();
            $table->time('schedule_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function (Blueprint $table){
            $table->dateTime('for_date');
            $table->dropColumn('schedule_date');
            $table->dropColumn('schedule_time');
        });
    }
}
