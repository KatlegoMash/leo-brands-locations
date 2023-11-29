<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('seller_id');
            $table->string('name')->nullable();
            $table->string('domain')->nullable();
            $table->string('seller_type')->nullable();
            $table->longText('comment')->nullable();
            $table->boolean('is_confidential')->nullable();
            $table->boolean('is_passthrough')->nullable();
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
        Schema::dropIfExists('sellers');
    }
}
