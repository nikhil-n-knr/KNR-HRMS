<?php

namespace App\Notifications\Talent;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OnboardingWelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $password;
    protected $name;

    /**
     * Create a new notification instance.
     */
    public function __construct($name, $password)
    {
        $this->password = $password;
        $this->name = $name;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function medicalMailMessage(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Welcome to Talent Hub - Your Access Credentials')
                    ->greeting('Hello, ' . $this->name . '!')
                    ->line('Congratulations on joining our team. Your account has been successfully created.')
                    ->line('You can now log in to the portal using the following credentials:')
                    ->line('Email: ' . $notifiable->email)
                    ->line('Password: ' . $this->password)
                    ->action('Go to Dashboard', url('/login'))
                    ->line('Please make sure to change your password after your first login.')
                    ->line('Thank you for being part of our team!');
    }
    
    // Default to mail if medicalMailMessage was a typo in my thought but I'll use toMail
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Welcome to Talent Hub - Your Access Credentials')
                    ->greeting('Hello, ' . $this->name . '!')
                    ->line('Congratulations on joining our team. Your account has been successfully created.')
                    ->line('You can now log in to the portal using the following credentials:')
                    ->line('Email: ' . $notifiable->email)
                    ->line('Password: ' . $this->password)
                    ->action('Go to Dashboard', url('/login'))
                    ->line('Please make sure to change your password after your first login.')
                    ->line('Thank you for being part of our team!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
