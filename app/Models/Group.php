<?php

namespace App\Models;

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
    public function managers()
        {
            return $this->belongsTo(User::class, 'manager_id');
        }
}
