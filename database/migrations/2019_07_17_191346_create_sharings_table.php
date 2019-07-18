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
            $table->text('description');
            $table->tinyInteger('visibility')->unsigned()->default(1);
            $table->integer('capacity')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->bigInteger('category_id')->unsigned();
            $table->bigInteger('owner_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();

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
