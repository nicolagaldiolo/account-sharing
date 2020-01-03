<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSharingUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sharing_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sharing_id');
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('status')->unsigned()->default(\App\Enums\SharingStatus::Pending);
            $table->timestamp('credential_updated_at')->nullable();
            $table->timestamps();
            $table->unique( ['sharing_id','user_id'] );
            $table->foreign('sharing_id')->on('sharings')->references('id');
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
        Schema::dropIfExists('sharing_user');
    }
}
