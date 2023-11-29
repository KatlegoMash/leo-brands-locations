<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateContentCategoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('content_category', function(Blueprint $table)
		{
			$table->integer('id')->unsigned()->primary();
			$table->string('name', 128)->default('');
			$table->integer('parent_id')->unsigned()->nullable()->index('content_category_parent_id_foreign');
			$table->text('tracking', 65535)->nullable();
			$table->string('dv360_audience_id')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('content_category');
	}

}
