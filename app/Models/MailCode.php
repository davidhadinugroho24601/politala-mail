<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailCode extends Model
{
   
     // Specify the name of the view
     protected $table = 'mail_code';

      // Define the columns you want to be mass assignable (optional)
      protected $fillable = [
        'code_name',
        'status',
        'section_qty',
       'created_at',
         'code',
         'updated_at',

    ];

    public function codeDetails()
    {
     // Corrected the relationship definition
     return $this->hasMany(MailCodeDetail::class, 'code_id', 'id');
    }
}
