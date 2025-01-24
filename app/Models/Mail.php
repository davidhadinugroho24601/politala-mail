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
            ->useDisk('public') // Ensure you're using the public disk
            ->usePathGenerator(new class implements \Spatie\MediaLibrary\PathGenerator\PathGenerator {
                public function getPath(Media $media): string
                {
                    return 'attachments/';
                }

                public function getPathForConversions(Media $media): string
                {
                    return 'attachments/conversions/';
                }

                public function getPathForResponsiveImages(Media $media): string
                {
                    return 'attachments/responsive-images/';
                }
            });
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
