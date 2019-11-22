<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->string('id');
            $table->unsignedBigInteger('sharing_user_id');
            $table->tinyInteger('status')->unsigned();
            $table->boolean('cancel_at_period_end')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('current_period_end_at')->nullable();
            $table->timestamps();

            $table->primary('id');
            $table->unique('sharing_user_id');
            $table->foreign('sharing_user_id')->on('sharing_user')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
