<?php

namespace App\Models\Jobs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppliedResumes extends Model
{
   use HasFactory;

    protected $table = 'applied_resumes';

    protected $fillable = [
        'transNo',
        'resume_pdf',
        'job_name',
        'role_code',
        'code',
        'fullname',
        'company',
        'fullname'
    ];

    /**
     * Relationship to AppliedJob (if needed)
     */
    public function appliedJob()
    {
        return $this->belongsTo(AppliedJobs::class, 'transNo', 'transNo');
    }
}
