<?php

namespace App\Services\Assets;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetMaintenanceLog;
use App\Services\Infrastructure\NotificationService; // Assuming exists or mocked
use Illuminate\Support\Facades\DB;
use Exception;

class AssetService
{
    /**
     * Assign an asset to a user.
     */
    public function assign(int $assetId, int $userId, int $assignedBy, int $quantity = 1): AssetAssignment
    {
        return DB::transaction(function () use ($assetId, $userId, $assignedBy, $quantity) {
            $asset = Asset::lockForUpdate()->findOrFail($assetId);

            if ($asset->is_serialized) {
                if ($quantity !== 1) {
                    throw new Exception("Serialized assets can only be assigned one at a time.");
                }
                if ($asset->status !== 'Available') {
                    throw new Exception("Asset is not available for assignment. Current status: {$asset->status}");
                }
                $asset->update(['status' => 'Assigned']);
            } else {
                // Non-serialized logic
                if ($asset->quantity < $quantity) {
                    throw new Exception("Insufficient quantity. Available: {$asset->quantity}, Requested: {$quantity}");
                }
                $asset->decrement('quantity', $quantity);
                
                // If quantity hits 0, maybe mark as Assigned? 
                // Flexible choice: Let's keep it Available if we consider it a 'stockpile' record, 
                // but usually status='Available' enables visibility in lists. 
                // If qty=0, it effectively is not available.
                if ($asset->quantity === 0) {
                    $asset->update(['status' => 'Assigned']); // Or 'Out_Of_Stock'
                }
            }

            // Create Pending Assignment
            $assignment = AssetAssignment::create([
                'asset_id' => $assetId,
                'user_id' => $userId,
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
                'ack_status' => 'Pending',
                'quantity' => $quantity
            ]);

            // Trigger Notification to User (TODO: Wiring)
            // NotificationService::send($userId, 'asset_assigned', $assignment);

            return $assignment;
        });
    }

    /**
     * User accepts the asset assignment.
     */
    public function acceptAssignment(int $assignmentId, int $userId): AssetAssignment
    {
        $assignment = AssetAssignment::findOrFail($assignmentId);

        if ($assignment->user_id !== $userId) {
            throw new Exception("Unauthorized: You are not the assignee of this asset.");
        }

        if ($assignment->ack_status !== 'Pending') {
            throw new Exception("Assignment is already {$assignment->ack_status}.");
        }

        $assignment->update([
            'ack_status' => 'Accepted'
        ]);

        return $assignment;
    }

    /**
     * Log maintenance/repair for an asset.
     */
    public function logMaintenance(int $assetId, array $data): AssetMaintenanceLog
    {
        return DB::transaction(function () use ($assetId, $data) {
            $asset = Asset::findOrFail($assetId);

            // Create Log
            $log = AssetMaintenanceLog::create([
                'asset_id' => $assetId,
                'type' => $data['type'], // Repair, Upgrade
                'vendor_name' => $data['vendor_name'] ?? null,
                'cost' => $data['cost'] ?? 0,
                'parts_replaced' => $data['parts_replaced'] ?? [], // Array wrapped in JSON by cast
                'description' => $data['description'] ?? null,
                'service_date' => $data['service_date'] ?? now(),
                'next_service_date' => $data['next_service_date'] ?? null
            ]);

            // If currently in service, maybe update status? 
            if ($asset->status !== 'In_Service' && ($data['set_status_maintenance'] ?? false)) {
                $asset->update(['status' => 'In_Service']);
            }

            // Update Current Value if upgrade? (Logic can be added later)

            return $log;
        });
    }

    /**
     * Return asset (End assignment).
     */
    public function returnAsset(int $assignmentId, string $condition): AssetAssignment
    {
        $assignment = AssetAssignment::findOrFail($assignmentId);
        
        $assignment->update([
            'returned_at' => now(),
            'condition_on_return' => $condition
        ]);

        $assignment->asset->update([
            'status' => 'Available',
             // 'available_at' => now() 
        ]);
        
        if (!$assignment->asset->is_serialized) {
            $assignment->asset->increment('quantity', $assignment->quantity);
        }

        return $assignment;
    }
    /**
     * War Room Intelligence: Detect Impact Graph from Incident Text.
     */
    public function detectImpact(string $title, ?string $description, string $type): array
    {
        $text = strtolower($title . ' ' . $description);
        $nodes = [];
        $links = [];
        
        // 1. Root Node: The Incident itself (Threat/Source)
        $nodes[] = [
            'id' => 'incident_source',
            'label' => $type,
            'type' => 'threat',
            'layer' => 0,
            'status' => 'active'
        ];

        // 2. Keyword Heuristics
        $keywords = [
            'database' => ['id' => 'db_primary', 'label' => 'Primary DB', 'type' => 'asset', 'layer' => 1],
            'sql' => ['id' => 'db_primary', 'label' => 'Primary DB', 'type' => 'asset', 'layer' => 1],
            'redis' => ['id' => 'cache_redis', 'label' => 'Redis Cache', 'type' => 'asset', 'layer' => 1],
            'cache' => ['id' => 'cache_redis', 'label' => 'Redis Cache', 'type' => 'asset', 'layer' => 1],
            'api' => ['id' => 'svc_api', 'label' => 'API Gateway', 'type' => 'service', 'layer' => 2],
            'timeout' => ['id' => 'svc_api', 'label' => 'API Gateway', 'type' => 'service', 'layer' => 2],
            'auth' => ['id' => 'svc_auth', 'label' => 'Auth Service', 'type' => 'service', 'layer' => 2],
            'login' => ['id' => 'svc_auth', 'label' => 'Auth Service', 'type' => 'service', 'layer' => 2],
            'user' => ['id' => 'data_users', 'label' => 'User Records', 'type' => 'data', 'layer' => 1],
            'payment' => ['id' => 'svc_payment', 'label' => 'Payment Gateway', 'type' => 'service', 'layer' => 2],
            's3' => ['id' => 'storage_s3', 'label' => 'S3 Bucket', 'type' => 'asset', 'layer' => 1],
            'file' => ['id' => 'storage_s3', 'label' => 'S3 Bucket', 'type' => 'asset', 'layer' => 1],
        ];

        $detectedIds = [];

        foreach ($keywords as $key => $nodeDef) {
            if (str_contains($text, $key)) {
                if (!in_array($nodeDef['id'], $detectedIds)) {
                    $nodes[] = [
                        'id' => $nodeDef['id'],
                        'label' => $nodeDef['label'],
                        'type' => $nodeDef['type'],
                        'layer' => $nodeDef['layer'],
                        'status' => 'warning' // Default detected status
                    ];
                    $detectedIds[] = $nodeDef['id'];
                    
                    // Link to Source
                    $links[] = ['source' => 'incident_source', 'target' => $nodeDef['id']];
                }
            }
        }
        
        // 3. Inference / Second-order Links (Mock Dependency Map)
        // If DB is hit, Data is often hit.
        if (in_array('db_primary', $detectedIds) && in_array('data_users', $detectedIds)) {
             $links[] = ['source' => 'db_primary', 'target' => 'data_users'];
        }
        // If Auth is hit, API is often hit.
        if (in_array('svc_auth', $detectedIds) && in_array('svc_api', $detectedIds)) {
             $links[] = ['source' => 'svc_api', 'target' => 'svc_auth']; // API calls Auth
        }
        
        // Fallback if nothing detected
        if (count($nodes) === 1) {
            $nodes[] = ['id' => 'unknown_asset', 'label' => 'Unknown System', 'type' => 'asset', 'layer' => 1, 'status' => 'warning'];
            $links[] = ['source' => 'incident_source', 'target' => 'unknown_asset'];
        }

        return ['nodes' => $nodes, 'links' => $links];
    }
}
