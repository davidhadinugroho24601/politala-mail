<?php

namespace App\Models;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';
    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'manager_id',
        'acronym',
    ];

    public function groupDetailsView()
    {
    return $this->hasMany(GroupDetailsView::class);
    }


    public function children()
    {
        return $this->hasManyRecursive(Group::class);
    }

    public function parent()
    {
        return $this->belongsTo(Group::class, 'parent_id');
    }

    
}
