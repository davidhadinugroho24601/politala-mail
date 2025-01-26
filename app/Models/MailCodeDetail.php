<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailCodeDetail extends Model
{
    protected $table = 'mail_code_details';

    protected $fillable = [
        'text',
        'number',
        'type',
       'created_at',
       'updated_at',

    ];

    public function codeDetails()
    {
     // Corrected the relationship definition
     return $this->hasOne(MailCodeDetail::class, 'id', 'code_id');
    }
}
