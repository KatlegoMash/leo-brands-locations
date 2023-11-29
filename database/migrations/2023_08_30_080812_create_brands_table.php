<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBrandsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('brands', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('brandName', 64);
			$table->string('brandUrl', 1056)->nullable();
			$table->integer('advertiserId')->unsigned();
			$table->string('imageURL')->nullable();
			$table->timestamps();
			$table->string('_token', 100)->nullable();
			$table->softDeletes();
			$table->string('locationBankClientId')->nullable();
			$table->integer('geofence');
			$table->integer('maximum_capacity');
			$table->float('visit_score', 8, 3);
			$table->integer('visits_yn');
			$table->boolean('use_nearme_yn')->default(0);
			$table->boolean('broadsign_yn')->default(0);
			$table->bigInteger('broadsignId')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('brands');
	}

}
