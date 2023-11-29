<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWidgetCategoriesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('widget_categories', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('categoriesName');
			$table->string('icons');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('geofence');
			$table->boolean('use_on_nearme')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('widget_categories');
	}

}
