<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('stripe_id');
            $table->string('customer_id');
            $table->unsignedBigInteger('user_id');
            $table->string('subscription_id');
            $table->string('payment_intent')->index();
            $table->string('service_name');
            $table->decimal('total', 10, 2);
            $table->decimal('total_less_fee', 10, 2);
            $table->decimal('fee', 10, 2);
            $table->char('currency', 3);
            $table->char('last4', 4);
            $table->boolean('transfered')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->on('users')->references('id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
