<?php

namespace App\Mail;

use App\Models\CourseApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseApplicationNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CourseApplication $courseApplication) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Course/University Application — '.$this->courseApplication->university_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.course-application-notification',
            with: [
                'application' => $this->courseApplication,
            ],
        );
    }
}
