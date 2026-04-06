<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create Visitor Purposes Table (Form Builder Config)
        if (!Schema::hasTable('visitor_purposes')) {
            Schema::create('visitor_purposes', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Interview, Client Meeting, Vendor, Personal
                $table->string('key')->unique(); // interview, client, vendor
                $table->json('form_config')->nullable(); 
                // Example: { "phone": "required", "photo": "optional", "materials": "hidden" }
                $table->string('workflow_type')->default('standard'); // standard, recruitment, crm
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Add CRM integration column to Visitors
        Schema::table('visitors', function (Blueprint $table) {
            if (!Schema::hasColumn('visitors', 'linked_candidate_id')) {
                // Check if candidates table exists (it should based on previous migrations)
                // Fix: Candidates uses UUID
                $table->foreignUuid('linked_candidate_id')->nullable()->constrained('candidates')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('visitors', 'linked_lead_id')) {
                // CRM Lead Link (assuming leads table might not exist yet, using simplified approach or check)
                // For now, we'll store specific CRM ID, nullable constraint if table exists, else just indexed column
                if (Schema::hasTable('leads')) {
                    $table->foreignId('linked_lead_id')->nullable()->constrained('leads')->nullOnDelete();
                } else {
                    $table->unsignedBigInteger('linked_lead_id')->nullable();
                }
            }
        });

        // 3. Add Advanced Access columns to Visitor Passes
        Schema::table('visitor_passes', function (Blueprint $table) {
            if (!Schema::hasColumn('visitor_passes', 'is_vip')) {
                $table->boolean('is_vip')->default(false); // VIP Mode
            }
            if (!Schema::hasColumn('visitor_passes', 'host_vouched')) {
                $table->boolean('host_vouched')->default(false); // Forgot ID Override
            }
            if (!Schema::hasColumn('visitor_passes', 'group_size')) {
                $table->integer('group_size')->default(1); // Entourage count
            }
            if (!Schema::hasColumn('visitor_passes', 'material_details')) {
                $table->json('material_details')->nullable(); // [{"item": "Laptop", "serial": "123"}, {"item": "Tools"}]
            }
            if (!Schema::hasColumn('visitor_passes', 'visitor_feedback')) {
                $table->string('visitor_feedback')->nullable(); // Post-visit feedback from Host
            }
        });

        // --- SEED DEFAULT PURPOSES ---
        // We do this here to ensure immediate availability
        $purposes = [
            [
                'name' => 'Interview / Candidate',
                'key' => 'interview',
                'workflow_type' => 'recruitment',
                'form_config' => json_encode([
                    'phone' => 'required',
                    'email' => 'required',
                    'photo' => 'required',
                    'company' => 'hidden', // Candidates don't usually represent a company
                    'resume' => 'optional' // Upload
                ])
            ],
            [
                'name' => 'Client / Business Meeting',
                'key' => 'client',
                'workflow_type' => 'crm',
                'form_config' => json_encode([
                    'phone' => 'required',
                    'email' => 'required',
                    'company' => 'required', // Mandatory for B2B
                    'business_card' => 'optional' 
                ])
            ],
            [
                'name' => 'Vendor / Delivery',
                'key' => 'vendor',
                'workflow_type' => 'standard',
                'form_config' => json_encode([
                    'phone' => 'required',
                    'company' => 'required',
                    'vehicle_no' => 'optional',
                    'material_entry' => 'required'
                ])
            ],
            [
                'name' => 'Personal / Family',
                'key' => 'personal',
                'workflow_type' => 'standard',
                'form_config' => json_encode([
                    'phone' => 'required',
                    'email' => 'hidden',
                    'company' => 'hidden',
                    'photo' => 'required'
                ])
            ]
        ];

        foreach ($purposes as $p) {
            DB::table('visitor_purposes')->updateOrInsert(
                ['key' => $p['key']],
                array_merge($p, [
                    'created_at' => now(), 
                    'updated_at' => now()
                ])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitor_passes', function (Blueprint $table) {
            $table->dropColumn(['is_vip', 'host_vouched', 'group_size', 'material_details', 'visitor_feedback']);
        });

        Schema::table('visitors', function (Blueprint $table) {
            $table->dropColumn(['linked_candidate_id', 'linked_lead_id']);
        });

        Schema::dropIfExists('visitor_purposes');
    }
};
