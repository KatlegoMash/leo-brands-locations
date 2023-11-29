<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTemplatePinsSettingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('template_pins_settings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('template_pins_id');
			$table->integer('banner_creative_id');
			$table->string('field');
			$table->text('value', 65535);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('template_pins_settings');
	}

}
