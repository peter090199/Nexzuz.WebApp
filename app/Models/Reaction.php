<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    use HasFactory;

    protected $table = 'reactionPost'; 

    // Disable default timestamps since table doesn't have created_at / updated_at
    public $timestamps = false;  

    protected $fillable = [
        'post_id',
        'post_uuidOrUind',
        'reaction',
        'code',
    ];
}
