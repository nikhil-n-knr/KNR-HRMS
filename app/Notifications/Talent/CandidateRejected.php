<?php

namespace App\Notifications\Talent;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\JobApplication;

class CandidateRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public $application;
    public $reason;

    public function __construct(JobApplication $application, $reason = null)
    {
        $this->application = $application;
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->application->job->title ?? 'Open Position';
        
        return (new MailMessage)
                    ->subject('Update regarding your application at ' . config('app.name'))
                    ->greeting('Hi ' . $notifiable->first_name . ',')
                    ->line("Thank you for giving us the opportunity to consider you for the {$jobTitle} position.")
                    ->line("We have reviewed your application and qualifications. While we were impressed with your background, we have decided to move forward with other candidates who better match our current requirements.")
                    ->line("We will keep your resume on file and reach out if a relevant opportunity opens up in the future.")
                    ->line('We wish you the best of luck in your job search.')
                    ->salutation('Best regards,')
                    ->with('The ' . config('app.name') . ' Hiring Team');
    }
}
