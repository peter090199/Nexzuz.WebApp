<?php

namespace App\Models\Jobs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class AppliedAnswers extends Model
{
      use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'applied_answers'; 
    public $timestamps = true;

    protected $fillable = [
        'answer_text',
        'code',
        'role_code',
        'transNo',
        'job_name',
        'company',
        'updated_at',
        'create_at',
        'status'
    ];

   

} 

