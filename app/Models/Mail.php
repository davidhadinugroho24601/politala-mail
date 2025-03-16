<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;
use App\Models\Group;
use App\Models\MailTemplate;
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
         'notes',
         'disposition_id',
        'created_at',
        'updated_at',
        'google_doc_link',
        'pdf_path',
        'direct_id',
        'hidden_code',

     ];

    public function recipient()
    {
        return $this->belongsTo(User::class, 'direct_id');
    }

        public function finalTarget()
    {
        return $this->belongsTo(Group::class, 'final_id');
    }
   
    // public function Disposition()
    // {
    //     return $this->belongsTo(Disposition::class, 'disposition_id');
    // }
    public function isAncestor(): bool
    {
        $group = Group::find($this->group_id);
        $finalGroup = Group::find($this->target_id);
    
        if (!$group || !$finalGroup) {
            return false;
        }
    
        return $group->ancestors()->where('id', $this->target_id)->exists();
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
                    'status' => $this->getStatusName($approval->status),
                    'name' => Group::where('id',$approval->group_id)->value('name'),
                    // 'mail_id' => $approval->mail_id,
                    // 'id' => $approval->status,
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
            'waiting' => '#f39c12',  // Orange (pending action)
            'approved' => '#2ecc71', // Green (success)
            'trashed' => '#e74c3c',  // Red (deleted)
            'finished' => '#3498db',  // Blue 
            'denied' => '#9b59b6',   // Purple (rejected but distinct from trash)
            default => '#95a5a6',    // Light grey (unknown status)
        };
        
    }

    private function getStatusName($status)
    {
        return match ($status) {
            'waiting' => 'Menunggu',
            'approved' => 'Disetujui',
            'finished' => 'Selesai',
            'trashed' => 'Ditolak',
            'denied' => 'Revisi',
            default => 'Menunggu', // Default grey for unknown statuses
        };
    }

    public function approvalChains()
    {
        return $this->hasMany(ApprovalChain::class, 'mail_id');
    }


    public function rejecter()
    {
        return \App\Models\ApprovalChain::where('mail_id', $this->id)->where('status', 'denied')
        ->orderBy('created_at', 'asc') // Ensure chronological order
        ->get()
        ->map(function ($approval) {
            return [
                // 'status' => $approval->status,
                'name' => Group::where('id',$approval->group_id)->value('name'),
                // 'color' => $this->getStatusColor($approval->status),
            ];
        })->toArray();
    }
 
    public function template()
    {
        return $this->belongsTo(MailTemplate::class);
    }

    public function disposition()
    {
        return $this->belongsTo(Disposition::class);
    }
}
