<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table){
            $table->dropColumn('name');
            $table->string('last_name', 255)->default('')->after('id');
            $table->string('first_name', 255)->default('')->after('id');
            $table->char('password_reset_key', 32)->default('');
            $table->dateTime('password_reset_expires')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table){
            $table->string('name', 255)->default('')->after('id');
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('password_reset_key');
            $table->dropColumn('password_reset_expires');
        });
    }
}
