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
        'division_id',
    ];

    public function groupDetailsView()
    {
    return $this->hasMany(GroupDetailsView::class);
    }


    public function children()
    {
        return $this->hasManyRecursive(Group::class);
    }

    public function ancestors()
    {
        return $this->belongsTo(Group::class, 'parent_id')->with('ancestors');
    }
    

    public function isAncestor(): bool
    {
        $group = Group::find($this->group_id);
    
        if (!$group) {
            return false;
        }
    
        // Get all ancestor IDs
        $ancestorIds = $group->ancestors->pluck('id')->toArray();
    
        return in_array($this->target_id, $ancestorIds);
    }
    
    public function parent()
    {
        return $this->belongsTo(Group::class, 'parent_id');
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
