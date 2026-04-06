<?php

namespace App\Services\Communication;

class NotificationTemplates
{
    public static function get(string $key, array $data = []): array
    {
        $templates = [
            'forgot_password' => [
                'subject' => 'Password Reset OTP',
                'content' => "Your OTP is: {$data['otp']}. It expires in 10 minutes. If you did not request this, please ignore this email.",
            ],
            // Add more templates here
        ];

        return $templates[$key] ?? ['subject' => 'Notification', 'content' => ''];
    }
}
