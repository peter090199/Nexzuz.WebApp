<?php
namespace App\Models\Jobs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AppliedJobs extends Model
{
     use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'applied_jobs'; 

    protected $fillable = [
        'applied_id',
        'role_code',
        'code',
        'transNo',
        'job_name',
        'email',
        'country_code',
        'phone_number',
        'fullname'
    ];



}

