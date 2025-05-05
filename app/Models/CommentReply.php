<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class CommentReply extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'commentreplies';
    protected $fillable = [
        'comment_uuid',
        // 'replies_uuid',
        'status',
        'code',
        'comment',
        'date_comment',
        'created_by',
        'updated_by'
    ];

}
