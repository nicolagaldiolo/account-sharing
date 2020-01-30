<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credentials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('credentiable_id');
            $table->string('credentiable_type');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->timestamp('credential_updated_at')->nullable();
            $table->timestamps();

            $table->unique( ['credentiable_id','credentiable_type'] );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credentials');
    }
}
