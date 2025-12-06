<?php

namespace App\Models\Jobs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory;

    protected $table = 'jobPosting';  // your actual table name

    /**
     * Set primary key to job_id
     */
    protected $primaryKey = 'job_id';

    /**
     * If job_id in your database is AUTO_INCREMENT = YES → use this:
     */
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * If job_id is NOT auto increment (e.g., generated manually),
     * change to:
     *
     *   public $incrementing = false;
     *   protected $keyType = 'string';
     */

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
        'company',
        'job_image',
        'fullname',
        'is_online',
        'location',
        'benefits',
        'applied_status'
    ];
}
