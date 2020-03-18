<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bounce extends Model
{

    protected $table = 'imodules_bounces';

    public $timestamps = false; //Disable Laravel's Eloquent timestamps

    public $incrementing = false; // The table does not have autoincrement column
}
