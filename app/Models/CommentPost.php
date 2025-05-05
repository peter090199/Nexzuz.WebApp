<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class CommentPost extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'commentposts';
    protected $fillable = [
        'comment_uuid',
        'post_uuidOrUind',
        'status',
        'code',
        'comment',
        'date_comment',
        'created_by',
        'updated_by'
    ];
}
