<?php

namespace App\Services\Communication;

use Illuminate\Support\Facades\Notification;
use App\Models\NotificationInteraction;
use Illuminate\Notifications\DatabaseNotification;

class NotificationService
{
    /**
     * Send a notification to users via specified channels.
     * Centralized point to add SMS/WhatsApp logic later.
     */
    public function send($users, $notificationClass)
    {
        Notification::send($users, $notificationClass);
    }

    /**
     * Log user interaction with a notification.
     */
    public function logInteraction($user, $notificationId, $action, $request = null)
    {
        // 1. Log Interaction DB
        NotificationInteraction::create([
            'notification_id' => $notificationId,
            'user_id' => $user->id,
            'action' => $action,
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'details' => []
        ]);

        // 2. Mark as Read logic
        if (in_array($action, ['clicked', 'acknowledged', 'viewed_details', 'read'])) {
            $notification = DatabaseNotification::find($notificationId);
            if ($notification && !$notification->read_at) {
                $notification->markAsRead();
            }
        }
    }

    /**
     * Resolve the Redirect URL from a notification instance.
     */
    public function getRedirectUrl(DatabaseNotification $notification)
    {
        $data = $notification->data;
        
        // 1. Direct URL Override
        if (!empty($data['url'])) return $data['url']; 
        
        // 2. Type-based Strategy
        switch ($data['type'] ?? '') {
            case 'task_moved':
            case 'task_assigned':
            case 'sprint_status':
                 if (!empty($data['project_id'])) {
                    return route('projects.board', $data['project_id']);
                }
                break;
                
            case 'interview_cancelled':
                 // Handled via 'url' in payload, but fallback:
                 if (!empty($data['candidate_id'])) {
                     return route('talent.candidates.index', ['open_id' => $data['candidate_id']]);
                 }
                 break;

            // Finance / Expense
            case 'expense_status_update':
                if (!empty($data['expense_id'])) {
                    return route('employee.expenses.show', $data['expense_id']);
                }
                break;
            
            case 'expense_approval_request':
                return route('manager.approvals.index', ['status' => 'Pending', 'type' => 'expense']);

            // Loans
            case 'loan_status_update':
                // Employee view
                return route('employee.loans.index'); 
            
            case 'loan_approval_request':
                return route('hr.loans.index'); // Admin/HR view

            // General Approvals (Leave, Regularization)
            case 'approval_request':
                return route('manager.approvals.index');
            
            case 'leave_status_update':
                return route('attendance.leaves.index'); // Adjust if specific show route exists
        }

        return route('dashboard'); 
    }
}
