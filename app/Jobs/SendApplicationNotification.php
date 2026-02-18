<?php

namespace App\Jobs;

use App\Mail\CourseApplicationNotificationMail;
use App\Models\CourseApplication;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendApplicationNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(public CourseApplication $courseApplication) {}

    public function handle(): void
    {
        $adminEmail = (string) config('classmemoir.admin_email');

        if ($adminEmail === '') {
            return;
        }

        Mail::to($adminEmail)->send(new CourseApplicationNotificationMail($this->courseApplication));
    }
}
