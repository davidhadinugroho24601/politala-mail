<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $fillable = [
        'name',
        'acronym',
        'division_code',
        
    ];


    public function children()
    {
        return $this->hasMany(Group::class);
    }

    public function availableTemplates(): HasMany
    {
        return $this->hasMany(TemplateAvailability::class, 'division_id');
    }
    
}
