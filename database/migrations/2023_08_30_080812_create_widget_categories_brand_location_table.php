<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWidgetCategoriesBrandLocationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('widget_categories_brand_location', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('categoriesId');
			$table->integer('brand_location_id')->index('widget_categories_brand_location_brandId');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('widget_categories_brand_location');
	}

}
