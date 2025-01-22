<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'groups';
    protected $fillable = [
        'name',
        'parent_id',
        'manager_id'
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
