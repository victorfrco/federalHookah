<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartialOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partial_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->double('total')->default(0);
	        $table->smallInteger('status')->nullable();
	        $table->boolean('associated')->nullable();
	        $table->smallInteger('pay_method')->nullable();
            $table->timestamps();

	        $table->unsignedInteger('order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partial_orders');
    }
}
