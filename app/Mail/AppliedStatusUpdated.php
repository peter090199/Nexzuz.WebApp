<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppliedStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $job;
    public $status;

    // Accept job and status in constructor
    public function __construct($job, $status)
    {
        $this->job = $job;
        $this->status = $status;
    }

    // Use build() to define subject, view, and data
    public function build()
    {
        return $this->subject('Job Application Status Updated')
                    ->view('emails.applied_status_updated')
                    ->with([
                        'jobName' => $this->job->job_name,
                        'status' => $this->status,
                        'fullname' => $this->job->fullname,
                    ]);
    }
}
