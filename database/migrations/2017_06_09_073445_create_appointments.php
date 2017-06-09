<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table){
            $table->increments('id');
            $table->unsignedInteger('customer_user_id');
            $table->unsignedInteger('lawyer_user_id');
            $table->string('status', 255)->default('');
            $table->dateTime('for_date');
            $table->string('description', 255)->default('');
            $table->timestamps();

            $table->index('customer_user_id');
            $table->index('lawyer_user_id');

            $table->foreign('customer_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('lawyer_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
