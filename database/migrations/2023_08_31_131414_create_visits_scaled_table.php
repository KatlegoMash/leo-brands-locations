<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisitsScaledTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('visits_scaled', function (Blueprint $table) {
            $table->increments('id');
            $table->string('locationName')->nullable();
            $table->integer('brandId')->nullable();
            $table->date('date')->nullable();
            $table->integer('hour')->nullable();
            $table->string('device_vendor')->nullable();
            $table->string('device_model')->nullable();
            $table->string('browser_name')->nullable();
            $table->string('os_name')->nullable();
            $table->string('publisher')->nullable();
            $table->integer('visits')->nullable();
            $table->integer('visits_sample')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('visits_scaled');
    }
}
