<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentMails extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mails_user_view';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'writer_id',
        'writer_name',
        'target_id',
        'target_name',
        'final_id',
        'final_target_name',
        'current_manager',
        'group_id',
        'user_name',
        'user_email',
        'group_name',
        'created_at',
        'approval_id',
        'content',
        'status',
        'subject',
        'updated_at',
    ];

    /**
     * Define the relationship with the `User` model for the writer.
     */
    public function writer()
    {
        return $this->belongsTo(User::class, 'writer_id');
    }

    /**
     * Define the relationship with the `User` model for the target.
     */
    public function target()
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    /**
     * Define the relationship with the `User` model for the final target.
     */
    public function finalTarget()
    {
        return $this->belongsTo(User::class, 'final_id');
    }

    /**
     * Define the relationship with the `Group` model.
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * Define the relationship with the `ApprovalChain` model.
     */
    public function approvalChain()
    {
        return $this->hasOne(ApprovalChain::class, 'id', 'approval_id');
    }
}
