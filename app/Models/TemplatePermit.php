<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplatePermit extends Model
{
    protected $fillable = [
        'template_id',
        'group_id',
    ];
}
