<?php
namespace App\Models\Jobs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class JobPosting extends Model
{
     use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'jobPosting'; 
    public $timestamps = true;

    protected $fillable = [
        'id',
        'code',
        'role_code',
        'job_name',
        'job_position',
        'job_description',
        'job_about',
        'qualification',
        'work_type',
        'comp_name',
        'comp_description',
        'job_image',
        'fullname',
        'is_online'
    ];
}
