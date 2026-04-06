<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            // Institutional Context
            $table->foreignId('institution_id')->nullable()->after('created_by')
                ->constrained('lms_institutions')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->after('institution_id')
                ->constrained('lms_programs')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('program_id')
                ->constrained('lms_categories')->nullOnDelete();
            
            // Course Identity
            $table->integer('semester')->nullable()->after('category_id');
            $table->string('level', 50)->nullable()->after('semester'); // beginner, intermediate, advanced
            $table->string('language', 10)->default('en')->after('level');
            $table->string('promo_video_url')->nullable()->after('language');
            $table->string('thumbnail_url')->nullable()->after('promo_video_url');
            
            // Mode & Access
            $table->enum('mode', ['standalone','institutional','both'])->default('institutional')->after('thumbnail_url');
            $table->boolean('is_public')->default(false)->after('mode'); // for standalone enrollment
            $table->boolean('is_published')->default(false)->after('is_public');
            $table->boolean('allow_self_enrollment')->default(false)->after('is_published');
            $table->decimal('price', 10, 2)->nullable()->after('allow_self_enrollment');
            
            // Metadata
            $table->json('tags')->nullable()->after('price'); // cross-listing tags
            $table->string('cover_image')->nullable()->after('tags');
            $table->string('banner_image')->nullable()->after('cover_image');
            $table->json('prerequisites')->nullable()->after('banner_image'); // [{type:'course', id:5}, {type:'text','desc':'...'}]
            $table->text('what_youll_learn')->nullable()->after('prerequisites'); // rich text
            $table->text('course_requirements')->nullable()->after('what_youll_learn');
            $table->text('target_audience')->nullable()->after('course_requirements');
            
            // Stats (denormalized for performance)
            $table->integer('total_concepts')->default(0)->after('target_audience');
            $table->integer('total_modules')->default(0)->after('total_concepts');
            $table->integer('total_duration_minutes')->default(0)->after('total_modules');
            $table->integer('enrolled_count')->default(0)->after('total_duration_minutes');
            $table->decimal('avg_rating', 3, 2)->default(0)->after('enrolled_count');
            $table->integer('total_ratings')->default(0)->after('avg_rating');
            $table->integer('completion_count')->default(0)->after('total_ratings');
            
            // Certificate linkage
            $table->foreignId('certificate_rule_id')->nullable()->after('completion_count')
                ->constrained('lms_certificate_rules')->nullOnDelete();
            $table->foreignId('certificate_template_id')->nullable()->after('certificate_rule_id')
                ->constrained('lms_certificate_templates')->nullOnDelete();
            
            // ERP sync fields
            $table->string('erp_course_code')->nullable()->after('certificate_template_id');
            $table->json('erp_metadata')->nullable()->after('erp_course_code');
            
            $table->index(['institution_id', 'is_published', 'is_active']);
            $table->index(['category_id']);
            $table->index(['program_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::table('lms_courses', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropForeign(['program_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['certificate_rule_id']);
            $table->dropForeign(['certificate_template_id']);
            $table->dropColumns([
                'institution_id', 'program_id', 'category_id', 'semester',
                'level', 'language', 'promo_video_url', 'thumbnail_url',
                'mode', 'is_public', 'is_published', 'allow_self_enrollment', 'price',
                'tags', 'cover_image', 'banner_image', 'prerequisites',
                'what_youll_learn', 'course_requirements', 'target_audience',
                'total_concepts', 'total_modules', 'total_duration_minutes',
                'enrolled_count', 'avg_rating', 'total_ratings', 'completion_count',
                'certificate_rule_id', 'certificate_template_id',
                'erp_course_code', 'erp_metadata',
            ]);
        });
    }
};
