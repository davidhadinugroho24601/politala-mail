<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mail extends Model
{
     // Specify the name of the view
     protected $table = 'mails';

     // Disable timestamps since views usually don't have timestamps like a table does
     public $timestamps = false;
 
     // Define the columns you want to be mass assignable (optional)
     protected $fillable = [
         'writer_id',
         'target_id',
         'final_id',
         'group_id',
         'content',
         'status',
         'subject',
     ];
}
