<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PathDetail extends Model
{
    protected $fillable = [
        'path_id',
        'group_id',
        'order',
    ];

    public function group(){
        return $this->belongsTo(Group::class);
       }
}
