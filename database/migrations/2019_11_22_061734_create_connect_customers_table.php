<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConnectCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('connect_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_pl_account_id');
            $table->string('user_pl_customer_id');
            $table->timestamps();

            $table->foreign('user_pl_customer_id')->on('users')->references('pl_customer_id');
            $table->unique(['user_pl_account_id', 'user_pl_customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('connect_customers');
    }
}
