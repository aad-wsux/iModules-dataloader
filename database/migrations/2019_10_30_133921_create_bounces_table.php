<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBouncesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imodules_bounces', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->string('type')->nullable();
            $table->string('reason', 1200)->nullable();
            $table->bigInteger('recipient_id');
            $table->bigInteger('timestamp');
            $table->bigInteger('date_added');
            $table->bigInteger('message_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imodules_bounces');
    }
}
