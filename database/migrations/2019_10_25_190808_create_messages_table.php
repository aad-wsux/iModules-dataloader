<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imodules_messages', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->smallInteger('subcommunity_id')->nullable();
            $table->string('email_name');
            $table->string('from_name')->nullable();
            $table->string('from_address')->nullable();
            $table->string('subject_line');
            $table->string('pre_header')->nullable();
            $table->string('category_name')->nullable();
            $table->bigInteger('sent_count')->nullable();
            $table->bigInteger('scheduled_date_timestamp');
            $table->bigInteger('actual_send_timestamp');
            $table->bigInteger('date_added');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imodules_messages');
    }
}
