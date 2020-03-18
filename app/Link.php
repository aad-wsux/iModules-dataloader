<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $table = 'imodules_links';

    public $timestamps = false; //Disable Laravel's Eloquent timestamps

    public $incrementing = false; // The table does not have autoincrement column
}
