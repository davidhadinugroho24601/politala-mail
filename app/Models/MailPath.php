<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailPath extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        // 'order',
        // 'authority',
        
    ];


    public function sender()
    {
        return $this->belongsTo(Group::class, 'sender_id');
    }


    public function receiver()
    {
        return $this->belongsTo(Group::class, 'receiver_id');
    }
    

    public function pathDetail(){
        return $this->hasMany(PathDetail::class, 'path_id');
    }
    // public function mail()
    // {
    //     return $this->belongsTo(Mail::class, 'mail_id');
    // }

    // public function group()
    // {
    //     return $this->hasMany(Group::class, 'group_id');
    // }
}
