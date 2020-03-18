<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{

    protected $table = 'imodules_recipients';

    public $timestamps = false; //Disable Laravel's Eloquent timestamps

    public $incrementing = false; // The table does not have autoincrement column

    // Have to add all columns to fillable in order to allow saving through firstOrCreate method
    protected $fillable = ['id', 'email_address', 'first_name', 'last_name', 'class_year', 'member_id', 'constituent_id', 'date_added', 'last_updated', 'message_id'];  
}
