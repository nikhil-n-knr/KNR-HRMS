<?php

namespace App\Services\CRM;

use Exception;
use Illuminate\Support\Facades\Http;
use App\Models\Employee;

class MeetingProviderService
{
    /**
     * Create a meeting on the specified provider.
     */
    public function createMeeting(Employee $employee, array $data): string
    {
        $provider = $data['provider'] ?? 'meet';

        return match($provider) {
            'zoom' => $this->createZoom($employee, $data),
            'meet' => $this->createGoogleMeet($employee, $data),
            'teams' => $this->createMsTeams($employee, $data),
            default => throw new Exception("Provider {$provider} not supported.")
        };
    }

    /**
     * Zoom API Integration.
     */
    private function createZoom(Employee $employee, array $data): string
    {
        if (!$employee->zoom_token) {
            throw new Exception("Zoom account not connected for employee.");
        }

        $baseUrl = config('services.zoom.base_url', 'https://api.zoom.us/v2');
        $response = Http::withToken($employee->zoom_token)
            ->post("{$baseUrl}/users/me/meetings", [
                'topic' => $data['title'],
                'type' => 2, // Scheduled meeting
                'start_time' => $data['start_time'],
                'duration' => $data['duration'] ?? 60,
                'timezone' => $data['timezone'] ?? 'UTC',
                'settings' => [
                    'host_video' => true,
                    'participant_video' => true,
                    'join_before_host' => false,
                    'mute_upon_entry' => true,
                    'waiting_room' => true,
                ],
            ]);

        if ($response->failed()) {
            throw new Exception("Zoom API Error: " . ($response->json()['message'] ?? 'Unknown Error'));
        }

        return $response->json()['join_url'];
    }

    /**
     * Google Meet API Integration.
     */
    private function createGoogleMeet(Employee $employee, array $data): string
    {
        if (!$employee->google_token) {
            throw new Exception("Google account not connected for employee.");
        }

        $response = Http::withToken($employee->google_token)
            ->post("https://meet.googleapis.com/v1/spaces");

        if ($response->failed()) {
            throw new Exception("Google Meet API Error: " . ($response->json()['error']['message'] ?? 'Unknown Error'));
        }

        return $response->json()['config']['accessUrl'] ?? $response->json()['meetingUri'];
    }

    /**
     * Microsoft Teams API Integration (MS Graph).
     */
    private function createMsTeams(Employee $employee, array $data): string
    {
        if (!$employee->ms_token) {
            throw new Exception("Microsoft account not connected for employee.");
        }

        $baseUrl = config('services.ms_graph.base_url', 'https://graph.microsoft.com/v1.0');
        $response = Http::withToken($employee->ms_token)
            ->post("{$baseUrl}/me/onlineMeetings", [
                'startDateTime' => $data['start_time'],
                'endDateTime' => $data['end_time'],
                'subject' => $data['title'],
                'isBroadcast' => false,
                'accessLevel' => 'everyone',
            ]);

        if ($response->failed()) {
            throw new Exception("MS Teams API Error: " . ($response->json()['error']['message'] ?? 'Unknown Error'));
        }

        return $response->json()['joinWebUrl'];
    }
}
