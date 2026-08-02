<?php
namespace App\Models\Jobs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;
    protected $table = 'jobPosting';  // your actual table name
    protected $primaryKey = 'job_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'job_id',
        'transNo',
        'code',
        'role_code',
        'job_name',
        'job_position',
        'job_description',
        'job_about',
        'qualification',
        'work_type',
        'answer_type',
        'company',
        'job_image',
        'fullname',
        'is_online',
        'location',
        'benefits',
        'applied_status',
        'currency',
        'max_salary',
        'min_salary'
    ];
}
