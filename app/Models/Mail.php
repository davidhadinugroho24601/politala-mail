<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use App\Models\Group;
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
         'template_id',
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
        return $this->belongsTo(Group::class, 'final_id');
    }
    

    public function AttachmentMail()
    {
        return $this->hasMany(Attachment::class);
    }

    public function getApprovalsAttribute()
    {
        return \App\Models\ApprovalChain::where('mail_id', $this->id)
            ->orderBy('created_at', 'asc') // Ensure chronological order
            ->get()
            ->map(function ($approval) {
                return [
                    'status' => $approval->status,
                    'name' => Group::where('id',$approval->group_id)->value('name'),
                    'color' => $this->getStatusColor($approval->status),
                ];
            })->toArray();
    }
    
    /**
     * Get the color associated with a given status.
     */
    private function getStatusColor($status)
    {
        return match ($status) {
            'waiting' => '#f39c12',
            'approved' => '#27ae60',
            'denied' => '#c0392b',
            default => '#7f8c8d', // Default grey for unknown statuses
        };
    }
    

}
