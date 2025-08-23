<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupDetailsView extends Model
{
        // Specify the name of the view
        protected $table = 'group_details_view';

        // Disable timestamps since views usually don't have timestamps like a table does
        public $timestamps = false;
    
        // Define the columns you want to be mass assignable (optional)
        protected $fillable = [
            'id',
            'user_id',
            'group_id',
            'user_name',
            'user_email',
            'group_name',
        ];

        public function groups()
        {
            return $this->belongsTo(Group::class, 'group_id');
        }

        public function users()
        {
            return $this->belongsTo(User::class, 'user_id');
        }
}
