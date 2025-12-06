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
            ->view('email.applied_status_updated') // <-- must match file path
             ->with([
                    'subject' => 'Application Status Update',
                    'messageText' => "Your application for '{$this->job->job_name}' has been updated to '{$this->status}'."
                ]);

    }
}
