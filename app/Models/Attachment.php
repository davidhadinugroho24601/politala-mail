<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'description',
        'path',
        
    ];
    protected $casts = [
        'path' => 'array',
    ];
 

    public function parent()
    {
        return $this->belongsTo(Mail::class);
    }
}
