<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;



class Userskill extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'userskills'; 

    protected $fillable = [
        'code',
        'transNo',
        'skills'
    ];
 
   
}
