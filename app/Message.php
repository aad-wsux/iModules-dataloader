<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{

    protected $table = 'imodules_messages';

    public $timestamps = false; //Disable Laravel's Eloquent timestamps

    public $incrementing = false; // The table does not have autoincrement column
}
