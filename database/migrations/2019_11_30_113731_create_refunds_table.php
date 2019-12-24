<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('stripe_id')->nullable();
            $table->string('payment_intent')->unique()->index();
            $table->tinyInteger('internal_status')->unsigned()->default(\App\Enums\RefundApplicationStatus::Pending);
            $table->tinyInteger('status')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('payment_intent')->on('invoices')->references('payment_intent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refunds');
    }
}
