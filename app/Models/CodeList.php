<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodeList extends Model
{
    protected $fillable = [
        'mail_id',
        'code'
    ];

    public function mail()
    {
        return $this->belongsTo(Mail::class, 'mail_id');
    }
}
