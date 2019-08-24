<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSharingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sharings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->tinyInteger('visibility')->unsigned()->default(1);
            $table->integer('capacity')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('image')->nullable();
            $table->bigInteger('renewal_frequency_id')->unsigned();
            $table->bigInteger('category_id')->unsigned();
            $table->bigInteger('owner_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();

            //$table->unique( ['category_id','owner_id'] ); // Non imposto nessun vincolo in quanto posso creare infinite condivisioni di categoria custom
            $table->foreign('renewal_frequency_id')->on('renewal_frequencies')->references('id');
            $table->foreign('category_id')->on('categories')->references('id');
            $table->foreign('owner_id')->on('users')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sharings');
    }
}
