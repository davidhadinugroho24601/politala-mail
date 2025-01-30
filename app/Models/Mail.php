<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
class Mail extends Model implements HasMedia
{
    use InteractsWithMedia;

        public function registerMediaCollections(): void
        {
            $this->addMediaCollection('attachments')
                ->useDisk('public'); // Specify the disk to store the files
        }

    

     // Specify the name of the view
     protected $table = 'mails';

    
 
     // Define the columns you want to be mass assignable (optional)
     protected $fillable = [
         'writer_id',
         'target_id',
         'final_id',
         'group_id',
         'content',
         'status',
         'is_staged',
         'subject',
        'created_at',
        'updated_at',

     ];

        public function finalTarget()
    {
        return $this->belongsTo(User::class, 'final_id');
    }

}
