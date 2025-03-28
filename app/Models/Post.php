<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Post extends Model
{
    use HasApiTokens, HasFactory, Notifiable;



    protected $fillable = [
        'posts_uuid',
        'transNo',
        'posts_uuind',
        'caption',
        'post',
        'status',
        'code',
        'created_by',
        'updated_by'
    ];

}
