<?php

namespace App\Models\Jobs;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Jobs\JobPosting;

class SavedJob extends Model
{
    use HasFactory;

    protected $table = 'saved_jobs';

    protected $fillable = [
        'code',
        'job_id',
    ];

    /**
     * The job that was saved.
     */
    public function job()
    {
        return $this->belongsTo(JobPosting::class, 'job_id', 'id');
    }

    /**
     * The user who saved the job.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'code', 'code');
    }
}