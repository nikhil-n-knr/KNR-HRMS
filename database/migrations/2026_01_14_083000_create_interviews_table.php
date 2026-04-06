<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('interview_feedback');
        Schema::dropIfExists('interviews');

        Schema::create('interviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('job_application_id')->constrained('job_applications')->cascadeOnDelete();
            
            // The organizer/main interviewer
            $table->foreignId('interviewer_id')->constrained('users');
            
            $table->dateTime('scheduled_at');
            $table->integer('duration')->default(60); // Minutes
            
            $table->string('type')->default('Online'); // Online, In-Person, Phone
            $table->text('meeting_link')->nullable(); // Zoom/Teams link
            $table->string('location')->nullable(); // For physical interviews
            
            $table->string('status')->default('Scheduled'); // Scheduled, Completed, Cancelled, Rescheduled
            $table->string('round')->default('Round 1'); // Technical, HR, Managerial
            $table->string('round_title')->nullable(); // e.g., "System Design", "Culture Fit"
            
            // Reminders
            $table->integer('reminder_count')->default(0);
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->json('reminder_history')->nullable();

            // Invite Config
            $table->text('message_body')->nullable();

            $table->text('calendar_event_id')->nullable(); // Google/Outlook Event ID
            
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('interview_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('interview_id')->constrained('interviews')->cascadeOnDelete();
            $table->foreignId('interviewer_id')->constrained('users'); // Independent of main organizer
            
            $table->tinyInteger('rating')->unsigned(); // 1-5
            $table->text('summary')->nullable(); // Overall feedback
            
            $table->json('pros')->nullable();
            $table->json('cons')->nullable();
            
            $table->string('recording_path')->nullable(); // Path to audio/video
            $table->string('attachment_path')->nullable(); // Test results etc
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('interview_feedback');
        Schema::dropIfExists('interviews');
    }
};
