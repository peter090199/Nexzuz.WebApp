<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostMessage extends Model
{
    use HasFactory;
    protected $table = 'postmessage'; 
    protected $fillable = ['title','author'];
    public $timestamps = false;
}
