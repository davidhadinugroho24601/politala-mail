<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalChain extends Model
{
    protected $table = 'approval_chain';

    protected $fillable = [
        'mail_id',
        'group_id',
        'notes',
        'status'
    ];
}
