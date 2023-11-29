<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBrandLocationsOperatingHoursTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('brand_locations_operating_hours', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('brandLocationId');
			$table->boolean('closed_yn')->default(1);
			$table->enum('day_type', array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Holiday','Exception'));
			$table->time('start_time')->nullable();
			$table->time('end_time')->nullable();
			$table->date('exception_startdate')->nullable();
			$table->date('exception_enddate')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('brand_locations_operating_hours');
	}

}
