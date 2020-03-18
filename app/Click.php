<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Click extends Model
{

    protected $table = 'imodules_clicks';

    public $timestamps = false; //Disable Laravel's Eloquent timestamps

    public $incrementing = false; // The table does not have autoincrement column
}
