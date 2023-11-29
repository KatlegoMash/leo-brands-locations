<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBrandLocationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('brand_locations', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('brandId')->unsigned()->index('brandId');
			$table->string('locationName', 128)->index('locationName_index');
			$table->float('latitude', 10, 8)->index();
			$table->float('longitude', 11, 8)->index();
			$table->float('maxGeofence')->default(5000.00)->index('brand_locations_maxgeofence_index');
			$table->string('storeName', 128);
			$table->string('storeCode', 32)->nullable();
			$table->string('addressLine1', 256)->nullable();
			$table->string('addressLine2', 256)->nullable();
			$table->string('postalZipCode', 6)->nullable();
			$table->string('city', 64)->nullable()->index('brand_locations_1');
			$table->string('regionCode', 64)->nullable()->default('');
			$table->string('countryCode', 4)->nullable()->default('ZA');
			$table->string('phone', 16)->nullable();
			$table->string('homePage', 1024)->nullable();
			$table->boolean('approved')->default(1);
			$table->timestamps();
			$table->softDeletes();
			$table->string('_token', 100)->nullable();
			$table->string('locationBankId')->nullable();
			$table->string('locationBankCategory')->nullable();
			$table->enum('locationBankStatus', array('OPEN_FOR_BUSINESS_UNSPECIFIED','OPEN','CLOSED_TEMPORARILY','CLOSED_PERMANENTLY','LOCATIONBANK_DELETED'))->nullable();
			$table->string('google_place_id')->nullable()->unique();
			$table->string('country')->nullable();
			$table->string('province')->nullable();
			$table->string('suburb')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('brand_locations');
	}

}
