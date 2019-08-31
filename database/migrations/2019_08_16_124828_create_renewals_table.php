<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRenewalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('renewals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('status')->unsigned()->default(\App\Enums\RenewalStatus::Pending);
            $table->bigInteger('sharing_user_id')->unsigned();
            $table->dateTime('starts_at');
            $table->dateTime('expires_at');
            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('renewals');
    }
}
