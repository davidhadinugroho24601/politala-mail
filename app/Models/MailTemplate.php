<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class MailTemplate extends Model implements HasMedia
{
    use InteractsWithMedia;
    public function registerMediaCollections(): void
        {
            $this->addMediaCollection('attachments')
                ->useDisk('public'); // Specify the disk to store the files
        }

    protected $fillable = [
        'template',
        'name',
        'google_doc_link',
    ];

    public function templateAvailability(){
     return $this->hasMany(TemplateAvailability::class, 'template_id');
    }

    public function mailPath(){
        return $this->hasMany(MailPath::class, 'template_id');
       }

}
