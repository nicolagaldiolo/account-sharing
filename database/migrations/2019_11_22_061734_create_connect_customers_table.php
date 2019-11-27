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
            $table->string('customer_id');
            $table->bigInteger('user_pl_account_id')->unsigned();
            $table->bigInteger('user_pl_customer_id')->unsigned();
            $table->timestamps();

            $table->foreign('user_pl_account_id')->on('users')->references('id');
            $table->foreign('user_pl_customer_id')->on('users')->references('id');
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
