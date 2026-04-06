<?php

namespace App\Console\Commands\CRM;

use Illuminate\Console\Command;
use App\Services\CRM\MeetingDataService;

class SyncHrmsEmployees extends Command
{
    protected $signature = 'hrms:meeting-sync';
    protected $description = 'Refresh Meeting Hub Data Cache (Employees/CRM Audience)';

    public function handle(MeetingDataService $service)
    {
        $this->info('Starting Performance Sync...');
        $service->syncNow();
        $this->info('Meeting Hub Cache Warming Complete.');
    }
}
