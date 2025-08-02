<?php

namespace App\Models\CV\DAL;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class UserLanguages extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usercapabilities'; 
    public $timestamps = true;

    protected $fillable = [
        'id',
        'code',
        'transNo',
        'language',
    ];
 
   
}
