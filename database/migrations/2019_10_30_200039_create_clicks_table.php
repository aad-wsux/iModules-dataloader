<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClicksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imodules_clicks', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->bigInteger('recipient_id');
            $table->string('user_agent', 500)->nullable();
            $table->string('ip_address')->nullable();
            $table->bigInteger('link_id');
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
        Schema::dropIfExists('imodules_clicks');
    }
}
