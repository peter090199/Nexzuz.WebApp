<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    use HasFactory;

    protected $table = 'reactionPost'; 
    public $timestamps = true;

    protected $fillable = [
        'id',
        'code',
        'post_uuidOrUind',
        'reaction',
        'updated_at',
        'create_at',
    ];

} 


