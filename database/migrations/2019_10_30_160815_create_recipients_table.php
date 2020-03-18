<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imodules_recipients', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->string('email_address');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->smallInteger('class_year')->nullable();
            $table->bigInteger('member_id')->nullable();
            $table->string('constituent_id', 10)->nullable();
            $table->bigInteger('date_added');  
            $table->bigInteger('last_updated');
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
        Schema::dropIfExists('imodules_recipients');
    }
}
