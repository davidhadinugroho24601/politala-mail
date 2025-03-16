<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateAvailability extends Model
{
    protected $table = 'template_availabilities';

    protected $fillable = [
        'template_id',
        'division_id',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
