<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupDetail extends Model
{
     // Define the columns you want to be mass assignable (optional)
     protected $fillable = [
        // 'id',
        'user_id',
        'group_id',
        
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); 
    }
}
