<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ProjectModuleController; // Import
use App\Http\Controllers\Employee\ProfileController as EmployeeProfileController;
use App\Http\Controllers\CMS\PublicSiteController;
use App\Http\Controllers\CRM\PublicMeetingController; // Added by instruction
use App\Http\Controllers\CRM\MeetingController; // Added by instruction

// TEMPORARY TEST ROUTE - Remove after debugging
Route::get('/test-lms-access', function() {
    $user = auth()->user();
    if (!$user) return response()->json(['error' => 'Not authenticated'], 401);
    return response()->json([
        'user' => $user->name,
        'email' => $user->email,
        'roles' => $user->roles->pluck('name'),
        'has_admin' => $user->roles->contains('name', 'Admin'),
        'has_hr' => $user->roles->contains('name', 'HR'),
        'message' => 'Auth working. Visit /hr/lms/analytics next'
    ]);
})->middleware('auth');

// SYSTEM UTILITY ROUTE - Used for clearing shared hosting cache
Route::get('/system-clear-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    return "All Caches Cleared Successfully! Ziggy routes are now reset. You can close this tab.";
});

Route::get('/force-clear-all', function() {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    
    // Clear Session files manually
    $path = storage_path('framework/sessions');
    $files = glob($path . '/*');
    foreach($files as $file) {
        if(is_file($file)) @unlink($file);
    }

    // WIPE ALL BROWSER COOKIES
    if (isset($_SERVER['HTTP_COOKIE'])) {
        $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
        foreach($cookies as $cookie) {
            $parts = explode('=', $cookie);
            $name = trim($parts[0]);
            setcookie($name, '', time()-1000);
            setcookie($name, '', time()-1000, '/');
        }
    }

    return "System Wiped and Browser Cookies Cleared. Return to <a href='/'>Home</a>";
});



// --- CMS Storefront Root & Dynamic Catch-all (Priority) ---
// This ensures http://localhost:8000/ and dynamic slugs like /men work immediately.

// Storefront Auth
Route::prefix('customer')->name('psp.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [\App\Http\Controllers\CMS\CustomerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [\App\Http\Controllers\CMS\CustomerAuthController::class, 'login'])->name('login.post');
        Route::get('/register', [\App\Http\Controllers\CMS\CustomerAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [\App\Http\Controllers\CMS\CustomerAuthController::class, 'register'])->name('register.post');
    });
    
    Route::middleware('auth:customer')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\CMS\CustomerAuthController::class, 'logout'])->name('logout');
        
        // Account details
        Route::get('/dashboard', [\App\Http\Controllers\CMS\CustomerDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [\App\Http\Controllers\CMS\CustomerDashboardController::class, 'orders'])->name('orders');
        Route::get('/wishlist', [\App\Http\Controllers\CMS\CustomerDashboardController::class, 'wishlist'])->name('wishlist');
        Route::post('/wishlist/{product}', [\App\Http\Controllers\CMS\CustomerDashboardController::class, 'toggleWishlist'])->name('wishlist.toggle');
    });
});

// Seller Onboarding
Route::get('/become-seller', [\App\Http\Controllers\CMS\SellerOnboardingController::class, 'showForm'])->name('psp.become-seller');
Route::post('/become-seller', [\App\Http\Controllers\CMS\SellerOnboardingController::class, 'submit']);





Route::middleware(['auth'])->group(function () {
    Route::prefix('hr/employee-360')->name('hr.employee-360.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HR\Employee360Controller::class, 'index'])->name('index');
        Route::get('/{employee}/metrics', [\App\Http\Controllers\HR\Employee360Controller::class, 'getMetrics'])->name('metrics');
        Route::get('/{employee}/export', [\App\Http\Controllers\HR\Employee360Controller::class, 'export'])->name('export');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    // ... existing routes ...

    Route::get('/admin/theme-settings', function () {
        return Inertia::render('Admin/ThemeSettings');
    })->name('admin.theme-settings');



    // Project Modules API
    Route::prefix('projects/{project}/modules')->name('projects.modules.')->group(function () {
        Route::get('/tree', [ProjectModuleController::class, 'index'])->name('tree'); // JSON Data
        Route::post('/', [ProjectModuleController::class, 'store'])->name('store');
        Route::post('/bulk', [ProjectModuleController::class, 'bulkStore'])->name('bulk');
        Route::post('/clone', [ProjectModuleController::class, 'cloneStructure'])->name('clone');
        Route::post('/bulk-destroy', [ProjectModuleController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::put('/{module}', [ProjectModuleController::class, 'update'])->name('update');
        Route::delete('/{module}', [ProjectModuleController::class, 'destroy'])->name('destroy');
    });


    // Client Management API (Renamed to avoid conflict with UI Resource)
    Route::prefix('project-portal')->name('projects.portal.')->group(function () {
        Route::get('/', [App\Http\Controllers\ProjectManagement\ProjectPortalController::class, 'index'])->name('index');
        Route::post('/{project}/document', [App\Http\Controllers\ProjectManagement\ProjectPortalController::class, 'uploadDocument'])->name('document.upload');
        Route::post('/document/{document}/sign-off', [App\Http\Controllers\ProjectManagement\ProjectPortalController::class, 'signOffDocument'])->name('document.sign-off');
        Route::put('/{project}/governance', [App\Http\Controllers\ProjectManagement\ProjectPortalController::class, 'updateGovernance'])->name('governance.update');
        Route::get('/{project}/governance/history', [App\Http\Controllers\ProjectManagement\ProjectPortalController::class, 'getLogHistory'])->name('governance.history');
        Route::get('/{project}/summary-pdf', [App\Http\Controllers\ProjectManagement\ProjectSummaryController::class, 'exportPDF'])->name('summary-pdf');
        Route::get('/management/dealing-hub', [App\Http\Controllers\ProjectManagement\ManagementDealingController::class, 'hub'])->name('management.dealing-hub');
    });
    Route::prefix('projects/clients-api')->name('clients.')->group(function () {
        Route::get('/', [App\Http\Controllers\ProjectManagement\ClientManagementController::class, 'index'])->name('index');
        Route::post('/users', [App\Http\Controllers\ProjectManagement\ClientManagementController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [App\Http\Controllers\ProjectManagement\ClientManagementController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{user}/reset-password', [App\Http\Controllers\ProjectManagement\ClientManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('/users/{user}/kill', [App\Http\Controllers\ProjectManagement\ClientManagementController::class, 'killSwitch'])->name('users.kill');
    });


    // Workflow Architect API
    Route::prefix('projects/workflow-architect')->name('workflow-architect.')->group(function () {
        Route::get('/', [App\Http\Controllers\ProjectManagement\WorkflowArchitectController::class, 'index'])->name('index');
        Route::post('/stages', [App\Http\Controllers\ProjectManagement\WorkflowArchitectController::class, 'storeStage'])->name('stages.store');
        Route::put('/stages/{stage}', [App\Http\Controllers\ProjectManagement\WorkflowArchitectController::class, 'updateStage'])->name('stages.update');
        Route::delete('/stages/{stage}', [App\Http\Controllers\ProjectManagement\WorkflowArchitectController::class, 'deleteStage'])->name('stages.destroy');
        Route::post('/reorder', [App\Http\Controllers\ProjectManagement\WorkflowArchitectController::class, 'reorderStages'])->name('stages.reorder');
    });

    // Internal Portal API (for users with Client role)
    Route::prefix('projects/portal')->name('internal-portal.')->group(function () {
        Route::get('/context', [App\Http\Controllers\ProjectManagement\TicketPortalController::class, 'getProjectContext'])->name('context');
        Route::post('/upload', [App\Http\Controllers\ProjectManagement\TicketPortalController::class, 'uploadMedia'])->name('upload');
        Route::post('/tickets', [App\Http\Controllers\ProjectManagement\TicketPortalController::class, 'storeTicket'])->name('tickets.store');
        Route::get('/tickets/{bug}/timeline', [App\Http\Controllers\ProjectManagement\TicketPortalController::class, 'getTicketTimeline'])->name('tickets.timeline');
    });

    // Project Bug Tracker API
    Route::prefix('projects/bugs')->name('bugs.')->group(function () {
        Route::get('/analytics', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'analytics'])->name('analytics');
        Route::get('/analytics/god-mode', [App\Http\Controllers\ProjectManagement\BugAnalyticsController::class, 'godMode'])->name('analytics.god-mode');
        Route::post('/analytics/query', [App\Http\Controllers\ProjectManagement\BugAnalyticsController::class, 'query'])->name('analytics.query');
        Route::get('/analytics/heatmap', [App\Http\Controllers\ProjectManagement\BugAnalyticsController::class, 'heatmap'])->name('analytics.heatmap');
        Route::get('/export/pdf', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'exportPDF'])->name('export.pdf');
        Route::get('/export/json', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'exportJSON'])->name('export.json');
        Route::get('/', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'store'])->name('store');
        Route::post('/bulk-update', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'bulkUpdate'])->name('bulk.update');
        Route::get('/assignees', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'getAvailableAssignees'])->name('assignees');
        
        Route::get('/{bug}', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'show'])->name('show');
        Route::post('/{bug}/comments', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'storeComment'])->name('comments.store');
        Route::put('/{id}/stage', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'updateStage'])->name('stage.update');
        Route::post('/{bug}/stage-advanced', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'advancedStageUpdate'])->name('stage.update.advanced');
        
        Route::get('/custom/views', [App\Http\Controllers\ProjectManagement\BugTicketViewController::class, 'index'])->name('views.index');
        Route::post('/custom/views', [App\Http\Controllers\ProjectManagement\BugTicketViewController::class, 'store'])->name('views.store');
        Route::delete('/custom/views/{id}', [App\Http\Controllers\ProjectManagement\BugTicketViewController::class, 'destroy'])->name('views.destroy');

        Route::get('/deployments', [App\Http\Controllers\ProjectManagement\DeploymentController::class, 'index'])->name('deployments.index');
        Route::post('/deployments', [App\Http\Controllers\ProjectManagement\DeploymentController::class, 'store'])->name('deployments.store');
        Route::put('/deployments/{round}/status', [App\Http\Controllers\ProjectManagement\DeploymentController::class, 'updateStatus'])->name('deployments.update-status');

        Route::get('/reports/generate', [App\Http\Controllers\ProjectManagement\ReportController::class, 'generate'])->name('reports.generate');
        
        // Phase 11: Bulk Management
        Route::get('/import/sample', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'downloadSampleExcel'])->name('import.sample');
        Route::post('/import/bulk', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'importExcel'])->name('import.bulk');
        
        // Phase 11: Stage Configuration
        Route::post('/stages/{stage}/people', [App\Http\Controllers\ProjectManagement\BugTrackerController::class, 'updateStagePeople'])->name('stages.people.update');
    });
});

// Client Portal Routes
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/', function() {
        return redirect()->route('portal.dashboard');
    })->name('root');

    // Guest Routes
    Route::middleware('guest:client')->group(function () {
        Route::get('/login', [App\Http\Controllers\ClientPortal\ClientLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [App\Http\Controllers\ClientPortal\ClientLoginController::class, 'login'])->name('login.post');
    });

    // Protected Routes
    Route::middleware('auth:client')->group(function () {
        Route::post('/logout', [App\Http\Controllers\ClientPortal\ClientLoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/context', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'getProjectContext'])->name('context');
        Route::post('/upload', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'uploadMedia'])->name('upload');
        Route::get('/pulse', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'getLivePulse'])->name('pulse');
        Route::get('/ticket/new', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'createTicket'])->name('tickets.create');
        Route::post('/ticket', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'storeTicket'])->name('tickets.store');
        Route::post('/ticket/{bug}/timeline', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'getTicketTimeline'])->name('tickets.timeline');
        Route::post('/ticket/{bug}/comments', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'storeComment'])->name('tickets.comments.store');
        Route::post('/project/{project}/document', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'uploadDocument'])->name('projects.document.upload');
        Route::post('/document/{document}/sign-off', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'signOffDocument'])->name('projects.document.sign-off');
        Route::post('/ticket/{ticket}/verify', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'verifyTicket'])->name('tickets.verify');
        Route::post('/ticket/{ticket}/rate', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'storeRating'])->name('tickets.rate');

        // Environment Presets
        Route::get('/presets', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'getDevicePresets'])->name('presets.index');
        Route::post('/presets', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'storeDevicePreset'])->name('presets.store');
        Route::delete('/presets/{id}', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'deleteDevicePreset'])->name('presets.destroy');

        // Knowledge Vault
        Route::get('/vault', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'knowledgeVault'])->name('vault');
        Route::get('/vault/article/{slug}', [App\Http\Controllers\ClientPortal\ClientPortalController::class, 'getArticle'])->name('vault.article');
    });
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// --- Public Career Routes ---
Route::get('/careers', [App\Http\Controllers\Talent\ApplicationController::class, 'index'])->name('careers.index');
Route::get('/careers/{job}', [App\Http\Controllers\Talent\ApplicationController::class, 'show'])->name('careers.show');
Route::post('/careers/{job}/apply', [App\Http\Controllers\Talent\ApplicationController::class, 'store'])->name('careers.apply');

// --- Public Offer Portal ---
Route::prefix('portal/offer')->name('portal.offer.')->group(function () {
    Route::get('/{token}/login', [\App\Http\Controllers\Public\OfferPortalController::class, 'login'])->name('login');
    Route::post('/{token}/otp', [\App\Http\Controllers\Public\OfferPortalController::class, 'sendOtp'])->name('otp.send');
    Route::post('/{token}/verify', [\App\Http\Controllers\Public\OfferPortalController::class, 'verifyOtp'])->name('otp.verify');
    
    Route::get('/{token}', [\App\Http\Controllers\Public\OfferPortalController::class, 'show'])->name('show');
    Route::post('/{token}/action', [\App\Http\Controllers\Public\OfferPortalController::class, 'update'])->name('update');
    Route::post('/{token}/documents/{documentRequest}', [\App\Http\Controllers\Public\OfferPortalController::class, 'uploadDocument'])->name('documents.upload');
    Route::get('/{token}/download', [\App\Http\Controllers\Public\OfferPortalController::class, 'download'])->name('download');
});

// --- Public Meeting RSVP ---
Route::get('/m/{uuid}/rsvp/{status}', [\App\Http\Controllers\CRM\MeetingController::class, 'rsvp'])->name('meetings.rsvp');

// Talent/Recruitment Routes
Route::middleware(['auth'])->prefix('talent')->name('talent.')->group(function () {
    // Interviews Module
    Route::get('/interviews', [App\Http\Controllers\Talent\InterviewController::class, 'index'])->name('interviews.index');
    
    // Candidate Pipeline
    Route::get('/candidates', [App\Http\Controllers\Talent\CandidateController::class, 'index'])->name('candidates.index');
    Route::get('/candidates/{candidate}/quick-view', [App\Http\Controllers\Talent\CandidateController::class, 'quickView'])->name('candidates.quick-view');
    Route::post('/candidates/{id}/move', [App\Http\Controllers\Talent\CandidateController::class, 'updateStatus'])->name('candidates.move');
    Route::post('/candidates/{candidate}/rate-screening', [App\Http\Controllers\Talent\CandidateController::class, 'rateScreening'])->name('candidates.rate-screening');
    Route::put('/candidates/{application}/reject', [App\Http\Controllers\Talent\CandidateController::class, 'reject'])->name('candidates.reject');
    Route::put('/candidates/{application}/offer', [App\Http\Controllers\Talent\CandidateController::class, 'offer'])->name('candidates.offer');
    
    // Interview Management
    Route::post('/candidates/{applicationId}/interviews', [App\Http\Controllers\Talent\CandidateController::class, 'storeInterview'])->name('candidates.interviews.store');
    Route::put('/candidates/interviews/{interview}', [App\Http\Controllers\Talent\CandidateController::class, 'updateInterview'])->name('candidates.interviews.update');
    Route::post('/candidates/interviews/{interview}/remind', [App\Http\Controllers\Talent\CandidateController::class, 'sendReminder'])->name('candidates.interviews.remind');
    Route::post('/candidates/interviews/{interview}/cancel', [App\Http\Controllers\Talent\CandidateController::class, 'cancelInterview'])->name('candidates.interviews.cancel');
    Route::post('/candidates/interviews/{interview}/feedback', [App\Http\Controllers\Talent\CandidateController::class, 'storeFeedback'])->name('candidates.interviews.feedback.store');
    
    Route::get('/offers/{candidate}/approval', [App\Http\Controllers\Talent\OfferApprovalController::class, 'show'])->name('offers.approval');
    Route::get('/offers/{offer}/preview-pdf', [App\Http\Controllers\Talent\OfferApprovalController::class, 'previewPdf'])->name('offers.preview-pdf'); // Added
    Route::post('/offers/{offer}/approve', [App\Http\Controllers\Talent\OfferApprovalController::class, 'approve'])->name('offers.approve');
    Route::post('/offers/{offer}/reject', [App\Http\Controllers\Talent\OfferApprovalController::class, 'reject'])->name('offers.reject');
});

Route::get('/m/login', function() { return Inertia::render('MobileApp/Auth/Login'); })->name('mobile.login');
Route::post('/m/login', [App\Http\Controllers\Auth\AuthController::class, 'login'])->name('mobile.login.post');
Route::get('/m/forgot-password', function() { return Inertia::render('MobileApp/Auth/ForgotPassword'); })->name('mobile.password.request');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard / Home (Inertia App Entry)
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('m')->name('mobile.')->middleware(['auth'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\MobileController::class, 'dashboard'])->name('dashboard');
        Route::get('/tasks', [App\Http\Controllers\MobileController::class, 'tasks'])->name('tasks');
        Route::get('/timesheet', [App\Http\Controllers\MobileController::class, 'timesheet'])->name('timesheet');
        Route::get('/chat', [App\Http\Controllers\MobileController::class, 'chat'])->name('chat');
        Route::get('/requests', [App\Http\Controllers\MobileController::class, 'requests'])->name('requests');
        Route::get('/approvals', [App\Http\Controllers\MobileController::class, 'approvals'])->name('approvals');
        Route::get('/profile', [App\Http\Controllers\MobileController::class, 'profile'])->name('profile');
        Route::get('/notifications', [App\Http\Controllers\MobileController::class, 'notifications'])->name('notifications');
    });


    // Coming Soon Fallback
    Route::get('/coming-soon', function () {
        return Inertia::render('Generics/ComingSoon', ['module' => request('module')]);
    })->name('coming-soon');
    
    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Communication\NotificationController::class, 'index'])->name('notifications.index');
    
    // Procurement & POs (Phase 15 Hub)
    Route::post('/procurement/{pr}/convert', [App\Http\Controllers\Admin\ProcurementController::class, 'convertToAssets'])->name('procurement.convert');
    Route::resource('/procurement', App\Http\Controllers\Admin\ProcurementController::class);

    // Identity & Access (Phase 16)
    Route::get('/identity-cards', [App\Http\Controllers\Admin\IdentityCardController::class, 'index'])->name('identity.index');
    Route::post('/identity-cards', [App\Http\Controllers\Admin\IdentityCardController::class, 'store'])->name('identity.store');
    Route::put('/identity-cards/{id}/status', [App\Http\Controllers\Admin\IdentityCardController::class, 'updateStatus'])->name('identity.update-status');
    Route::post('/identity-cards/{id}/revoke', [App\Http\Controllers\Admin\IdentityCardController::class, 'revoke'])->name('identity.revoke');
    Route::post('/identity-cards/batch-initialize', [App\Http\Controllers\Admin\IdentityCardController::class, 'batchStore'])->name('identity.batch-store');
    Route::post('/identity-cards/batch-print', [App\Http\Controllers\Admin\IdentityCardController::class, 'batchPrint'])->name('identity.batch-print');
    Route::post('/identity-cards/assets/bulk-upload', [App\Http\Controllers\Admin\IdentityAssetController::class, 'bulkUpload'])->name('identity.assets.bulk-upload');
    Route::post('/identity-cards/assets/{employee}/upload', [App\Http\Controllers\Admin\IdentityAssetController::class, 'individualUpload'])->name('identity.assets.individual-upload');
    
    // ID Card Studio (Phase 17)
    Route::get('/id-card/studio/{id?}', [App\Http\Controllers\Admin\CardTemplateController::class, 'studio'])->name('id-card.studio');
    Route::post('/id-card/templates', [App\Http\Controllers\Admin\CardTemplateController::class, 'store'])->name('id-card.templates.store');
    Route::post('/id-card/templates/{id}/default', [App\Http\Controllers\Admin\CardTemplateController::class, 'toggleDefault'])->name('id-card.templates.default');
    Route::delete('/id-card/templates/{id}', [App\Http\Controllers\Admin\CardTemplateController::class, 'destroy'])->name('id-card.templates.destroy');

    // Visitor Hub (Phase 20)
    Route::get('/visitors', [App\Http\Controllers\Admin\VisitorController::class, 'index'])->name('visitors.index');
    Route::post('/visitors/check-in', [App\Http\Controllers\Admin\VisitorController::class, 'checkIn'])->name('visitors.check-in');
    Route::post('/visitors/check-out/{id}', [App\Http\Controllers\Admin\VisitorController::class, 'checkOut'])->name('visitors.check-out');
    Route::get('/visitors/print/{id}', [App\Http\Controllers\Admin\VisitorController::class, 'printBadge'])->name('visitors.print');
    Route::get('/visitors/export', [App\Http\Controllers\Admin\VisitorController::class, 'export'])->name('visitors.export');
    Route::delete('/visitors/{id}', [App\Http\Controllers\Admin\VisitorController::class, 'destroy'])->name('visitors.destroy');
    Route::delete('/events/{id}', [App\Http\Controllers\Admin\VisitorController::class, 'destroyEvent'])->name('events.destroy');
    Route::get('/events/{id}/guests', [App\Http\Controllers\Admin\VisitorController::class, 'getEventGuests'])->name('events.guests');
    Route::post('/events/{id}/import', [App\Http\Controllers\Admin\VisitorController::class, 'importGuests'])->name('events.import');
    Route::post('/visitors/bulk-action', [App\Http\Controllers\Admin\VisitorController::class, 'bulkAction'])->name('visitors.bulk-action');
    Route::get('/v/{code}', [App\Http\Controllers\Admin\VisitorController::class, 'preCheckin'])->name('visitors.guest.pre-checkin.legacy');
    Route::post('/v/confirm/{id}', [App\Http\Controllers\Admin\VisitorController::class, 'confirmCheckin'])->name('visitors.guest.confirm');
    Route::post('/events/{id}/wrap-up', [App\Http\Controllers\Admin\VisitorController::class, 'wrapUpEvent'])->name('events.wrap-up');
    Route::get('/events/{id}/performance', [App\Http\Controllers\Admin\VisitorController::class, 'getEventPerformance'])->name('events.performance');
    Route::post('/events', [App\Http\Controllers\Admin\VisitorController::class, 'storeEvent'])->name('events.store');
    
    // Kiosk - Public/Private
    Route::get('/kiosk/login', function () {
        return Inertia::render('Admin/Visitors/Kiosk');
    })->name('visitors.kiosk');



    Route::post('/notifications/read-all', [\App\Http\Controllers\Communication\NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{id}/{action}', [\App\Http\Controllers\Communication\NotificationController::class, 'handleAction'])->name('notifications.action');

    // Document Stream/Download (Global Access subject to Controller Logic)
    Route::get('/documents/stream/{id}', [App\Http\Controllers\Admin\EmployeeDocumentController::class, 'stream'])->name('documents.stream');

    // Employee Self-Service Profile (UUID-based)
    Route::get('/my-profile/{uuid}', [EmployeeProfileController::class, 'show'])->name('employee.profile');
    Route::get('/me', [EmployeeProfileController::class, 'hub'])->name('employee.hub');

    // --- Employee Modules ---
    
    // --- Employee Modules (Unified Inertia) ---
    
Route::get('/attendance', function (Illuminate\Http\Request $request) {
        $tab = $request->input('tab', 'dashboard');
        
        // If specific data controllers are needed for tabs, we can invoke them or just render the Hub.
        // For Timesheets, the dashboard method might return data props.
        if ($tab === 'timesheets') {
             return app(App\Http\Controllers\Employee\TimesheetController::class)->dashboard();
        }
        
        // Default Dashboard (AttendanceController@index usually returns todayLog, history etc)
        return app(App\Http\Controllers\Employee\AttendanceController::class)->index($request);
    })->name('employee.attendance.hub');

    // Hub Alias
    Route::get('/attendance/hub', function() { return to_route('employee.attendance.hub'); });

    // Actions
    Route::post('/attendance/clock-in', [App\Http\Controllers\Employee\AttendanceController::class, 'clockIn'])->name('employee.attendance.clock-in');
    Route::post('/attendance/clock-out', [App\Http\Controllers\Employee\AttendanceController::class, 'clockOut'])->name('employee.attendance.clock-out');
    
    Route::get('/attendance/holidays-list/export', [App\Http\Controllers\Employee\HolidayController::class, 'export'])->name('attendance.holidays.export');
    Route::get('/attendance/holidays-list', [App\Http\Controllers\Employee\HolidayController::class, 'index'])->name('attendance.holidays.list');
    
    Route::post('/attendance/regularize', [App\Http\Controllers\Employee\RegularizationController::class, 'store'])->name('employee.attendance.regularize'); 

    // Redirect Old Timesheet Route
    Route::get('/attendance/timesheets', function(Illuminate\Http\Request $request) { 
        return to_route('employee.attendance.hub', ['tab' => 'timesheets']); 
    })->name('attendance.timesheets');
    
    // Timesheet Data Actions
    Route::prefix('attendance/timesheets')->name('employee.attendance.timesheets.')->group(function() {
         Route::get('/project-tasks/{project}', [App\Http\Controllers\Employee\TimesheetController::class, 'getProjectTasks'])->name('tasks');
         Route::post('/', [App\Http\Controllers\Employee\TimesheetController::class, 'store'])->name('store');
         Route::put('/{id}', [App\Http\Controllers\Employee\TimesheetController::class, 'update'])->name('update');
         Route::delete('/{id}', [App\Http\Controllers\Employee\TimesheetController::class, 'destroy'])->name('destroy');
         Route::post('/{id}/submit', [App\Http\Controllers\Employee\TimesheetController::class, 'submit'])->name('submit');
    });

    // Floating Holidays Link for Employees
    Route::get('/attendance/floating-holidays', function () {
        return redirect()->route('employee.leave.index', ['tab' => 'restricted']);
    })->name('employee.attendance.floating-holidays');

    // My Payslips
    Route::get('/employee/payslips', [\App\Http\Controllers\Employee\EmployeePayslipController::class, 'index'])->name('employee.payslips.index');
    Route::get('/employee/payslips/{payslip}/download', [\App\Http\Controllers\Employee\EmployeePayslipController::class, 'download'])->name('employee.payslips.download');

    // My Assets
    Route::get('/employee/my-assets', [\App\Http\Controllers\Employee\AssetController::class, 'index'])->name('employee.assets.index');
    
    // My Approvals
    Route::get('/employee/my-approvals', [\App\Http\Controllers\Employee\MyApprovalsController::class, 'index'])->name('employee.my-approvals.index');

    // Shift Swaps (Redirect to Hub)
    Route::get('/attendance/swaps', function () {
        return to_route('employee.attendance.hub', ['tab' => 'swaps']);
    })->name('attendance.swaps');
    
    // Swap Data Actions
    Route::get('/api/employee/attendance/swaps', [App\Http\Controllers\Employee\ShiftSwapController::class, 'index']); // Ensure this exists for the component to fetch data
    Route::post('/api/employee/attendance/swaps', [App\Http\Controllers\Employee\ShiftSwapController::class, 'store']);
    Route::put('/api/employee/attendance/swaps/{id}', [App\Http\Controllers\Employee\ShiftSwapController::class, 'update']);

    // Unified Request Hub
    Route::get('/attendance/requests', [App\Http\Controllers\Employee\RequestController::class, 'index'])->name('attendance.requests.index');
    Route::post('/attendance/requests/cancel', [App\Http\Controllers\Employee\RequestController::class, 'cancel'])->name('attendance.requests.cancel');

    // Leave Management (Employee Hub)
    Route::get('/leave-management', [App\Http\Controllers\Admin\LeaveRequestController::class, 'myLeaves'])->name('leave.dashboard');
    
    // Legacy / Actions
    Route::get('admin/leaves/approvals', [App\Http\Controllers\Admin\LeaveRequestController::class, 'approvals'])->name('leaves.approvals');
    Route::put('leaves/bulk-action', [App\Http\Controllers\Admin\LeaveRequestController::class, 'bulkAction'])->name('leaves.bulk-action');
    Route::get('/leaves/export', [App\Http\Controllers\Admin\LeaveRequestController::class, 'export'])->name('leaves.export');
    Route::post('/leaves/apply', [App\Http\Controllers\Admin\LeaveRequestController::class, 'store'])->name('leaves.store');
    Route::put('/leaves/{leaveRequest}', [App\Http\Controllers\Admin\LeaveRequestController::class, 'update'])->name('leaves.update');
    Route::delete('/leaves/{leaveRequest}', [App\Http\Controllers\Admin\LeaveRequestController::class, 'destroy'])->name('leaves.destroy');

    // Floating Holidays (SPA)
    // Shift Swaps (SPA)
    // Timesheets (SPA)
    
    // Employee Overtime
     Route::get('/attendance/overtime/export', [App\Http\Controllers\Admin\OvertimeController::class, 'export'])->name('admin.attendance.overtime.export');
     Route::get('/attendance/overtime/list', [App\Http\Controllers\Admin\OvertimeController::class, 'index'])->name('admin.attendance.overtime');
     Route::get('/attendance/overtime/my-requests', [App\Http\Controllers\Admin\OvertimeController::class, 'myRequests'])->name('attendance.overtime.my-requests');
     Route::post('/attendance/overtime', [App\Http\Controllers\Admin\OvertimeController::class, 'store'])->name('attendance.overtime.store');
     Route::put('/attendance/overtime/{overtime}', [App\Http\Controllers\Admin\OvertimeController::class, 'update'])->name('attendance.overtime.update');

     // Employee WFH
     Route::get('/attendance/wfh/export', [App\Http\Controllers\Admin\WfhController::class, 'export'])->name('admin.attendance.wfh.export');
     Route::get('/attendance/wfh/list', [App\Http\Controllers\Admin\WfhController::class, 'index'])->name('admin.attendance.wfh');
     Route::get('/attendance/wfh/my-requests', [App\Http\Controllers\Admin\WfhController::class, 'myRequests'])->name('attendance.wfh.my-requests');
     Route::post('/attendance/wfh', [App\Http\Controllers\Admin\WfhController::class, 'store'])->name('attendance.wfh.store');
     Route::put('/attendance/wfh/{wfh}', [App\Http\Controllers\Admin\WfhController::class, 'update'])->name('attendance.wfh.update');

     // --- Operations & Workload ---
     Route::get('/operations/calendar', [App\Http\Controllers\OperationsController::class, 'index'])->name('operations.calendar');
     Route::get('/operations/calendar/events', [App\Http\Controllers\OperationsController::class, 'events'])->name('operations.calendar.events');

    // Documents (Employee View)
    // Often handled within Profile, but if standalone:
    // Route::get('/my-documents', ...);

    
    // --- HR & Payroll ---
    Route::middleware(['role:Admin|HR'])->prefix('hr')->name('hr.')->group(function () {
        // Payroll
        Route::get('payroll', [App\Http\Controllers\HR\PayrollController::class, 'index'])->name('payroll.index');
        Route::get('payroll/run', [App\Http\Controllers\HR\PayrollController::class, 'create'])->name('payroll.create');
        Route::get('payroll/sync-stats', [App\Http\Controllers\HR\PayrollController::class, 'syncStats'])->name('payroll.sync-stats');
        Route::post('payroll', [App\Http\Controllers\HR\PayrollController::class, 'store'])->name('payroll.store');
        
        // Bulk Salary Management (Must be before wildcard {payroll})
        Route::get('payroll/bulk-update', [\App\Http\Controllers\HR\BulkSalaryController::class, 'index'])->name('payroll.bulk');
        Route::post('payroll/bulk-update', [\App\Http\Controllers\HR\BulkSalaryController::class, 'store'])->name('payroll.bulk.store');
        Route::get('payroll/bulk-update/sample', [\App\Http\Controllers\HR\BulkSalaryController::class, 'sampleSheet'])->name('payroll.bulk.sample');
        
        // Bulk Bank Update
        Route::get('payroll/bulk-bank', [\App\Http\Controllers\HR\BulkBankController::class, 'index'])->name('payroll.bulk-bank');
        Route::post('payroll/bulk-bank', [\App\Http\Controllers\HR\BulkBankController::class, 'store'])->name('payroll.bulk-bank.store');
        Route::get('payroll/bulk-bank/sample', [\App\Http\Controllers\HR\BulkBankController::class, 'downloadSample'])->name('payroll.bulk-bank.sample');
        Route::post('payroll/bulk-bank/import', [\App\Http\Controllers\HR\BulkBankController::class, 'import'])->name('payroll.bulk-bank.import');

        Route::get('payroll/disbursement', function() {
            return Inertia::render('HR/Payroll/Disbursement', [
                'payrolls' => \App\Models\Payroll::latest()->select('id', 'month', 'year', 'status')->get()
                    ->map(function($p) {
                         $p->month_name = date("F", mktime(0, 0, 0, $p->month, 10));
                         return $p;
                    })
            ]);
        })->name('payroll.disbursement');
        // Tax Settings (New Phase 10)
        Route::get('payroll/tax-settings', [\App\Http\Controllers\HR\TaxSettingController::class, 'index'])->name('payroll.tax-settings');
        Route::post('payroll/tax-regimes', [\App\Http\Controllers\HR\TaxSettingController::class, 'storeRegime'])->name('payroll.tax-regimes.store');
        Route::post('payroll/tax-slabs', [\App\Http\Controllers\HR\TaxSettingController::class, 'storeSlab'])->name('payroll.tax-slabs.store');
        Route::put('payroll/tax-slabs/{slab}', [\App\Http\Controllers\HR\TaxSettingController::class, 'updateSlab'])->name('payroll.tax-slabs.update');
        Route::delete('payroll/tax-slabs/{slab}', [\App\Http\Controllers\HR\TaxSettingController::class, 'destroySlab'])->name('payroll.tax-slabs.destroy');

        Route::get('payroll/{payroll}', [App\Http\Controllers\HR\PayrollController::class, 'show'])->name('payroll.show');
        Route::post('payroll/{payroll}/submit', [App\Http\Controllers\HR\PayrollController::class, 'submit'])->name('payroll.submit');
        Route::post('payroll/{payroll}/approve', [App\Http\Controllers\HR\PayrollController::class, 'approve'])->name('payroll.approve');
        Route::post('payroll/{payroll}/reject', [App\Http\Controllers\HR\PayrollController::class, 'reject'])->name('payroll.reject');
        Route::post('payroll/{payroll}/publish', [App\Http\Controllers\HR\PayrollController::class, 'publish'])->name('payroll.publish');
        Route::post('payroll/{payroll}/unpublish', [App\Http\Controllers\HR\PayrollController::class, 'unpublish'])->name('payroll.unpublish');
        Route::post('payroll/{payroll}/regenerate', [App\Http\Controllers\HR\PayrollController::class, 'regenerate'])->name('payroll.regenerate');
        Route::delete('payroll/{payroll}', [App\Http\Controllers\HR\PayrollController::class, 'destroy'])->name('payroll.destroy');
        Route::post('payroll/{payroll}/restore', [App\Http\Controllers\HR\PayrollController::class, 'restore'])->name('payroll.restore');
        
        Route::get('payroll/{payroll}/bank-transfer', [App\Http\Controllers\HR\PayrollController::class, 'exportBankFile'])->name('payroll.export-bank');
        Route::get('payroll/{payroll}/export-breakdown', [App\Http\Controllers\HR\PayrollController::class, 'exportBreakdown'])->name('payroll.export-breakdown');
        Route::get('payroll/{payroll}/export-pre-approval', [App\Http\Controllers\HR\PayrollController::class, 'exportPreApproval'])->name('payroll.export-pre-approval');
        Route::post('payroll/{payroll}/confirm', [App\Http\Controllers\HR\PayrollController::class, 'confirm'])->name('payroll.confirm');
        Route::get('payroll/payslip/{payslip}/download', [App\Http\Controllers\HR\PayrollController::class, 'downloadPdf'])->name('payslip.download');
        Route::post('payroll/payslip/{payslip}/email', [App\Http\Controllers\HR\PayrollController::class, 'emailPayslip'])->name('payslip.email');
        Route::post('payroll/payslip/{payslip}/update', [App\Http\Controllers\HR\PayrollController::class, 'updatePayslip'])->name('payslip.update');
        Route::post('payroll/payslip/{payslip}/toggle-hold', [App\Http\Controllers\HR\PayrollController::class, 'toggleHoldPayslip'])->name('payslip.toggle-hold');
        Route::delete('payroll/payslip/{payslip}', [App\Http\Controllers\HR\PayrollController::class, 'removePayslip'])->name('payslip.destroy');
        Route::get('payroll/all-payslips', [App\Http\Controllers\HR\PayrollController::class, 'allPayslips'])->name('payroll.all-payslips');

        // Compliance Reports
        Route::get('compliance', [App\Http\Controllers\HR\ComplianceController::class, 'index'])->name('compliance.index');
        Route::post('compliance/record-payment', [App\Http\Controllers\HR\ComplianceController::class, 'recordPayment'])->name('compliance.record-payment'); // New
        Route::get('compliance/missing-uan', [App\Http\Controllers\HR\ComplianceController::class, 'getMissingUan'])->name('compliance.missing-uan'); // New
        Route::put('compliance/employees/{employee}/mapping', [App\Http\Controllers\HR\ComplianceController::class, 'updateMapping'])->name('compliance.mapping.update'); // New
        Route::post('compliance/ecr/generate', [App\Http\Controllers\HR\ComplianceController::class, 'generateEcr'])->name('compliance.ecr.generate');
        Route::post('compliance/ecr/validate', [\App\Http\Controllers\HR\ComplianceController::class, 'validateBatch'])->name('compliance.ecr.validate'); // New
        Route::get('compliance/payrolls', [\App\Http\Controllers\HR\ComplianceController::class, 'getPayrolls'])->name('compliance.payrolls'); // New
        Route::post('compliance/ecr/preview', [App\Http\Controllers\HR\ComplianceController::class, 'previewBatch'])->name('compliance.ecr.preview'); // New
        
        Route::post('compliance/registers/download', [App\Http\Controllers\HR\ComplianceController::class, 'downloadRegister'])->name('compliance.registers.download'); // New
        Route::post('compliance/config', [App\Http\Controllers\HR\ComplianceController::class, 'saveConfig'])->name('compliance.config.save'); // New

        Route::get('compliance/{payroll}/pf', [App\Http\Controllers\HR\ComplianceController::class, 'downloadPf'])->name('compliance.pf');
        Route::get('compliance/{payroll}/esi', [App\Http\Controllers\HR\ComplianceController::class, 'downloadEsi'])->name('compliance.esi');
        Route::get('compliance/{payroll}/pt', [App\Http\Controllers\HR\ComplianceController::class, 'downloadPt'])->name('compliance.pt');

        // Compliance Modules (Branches, Rules, Licences, Sandbox)
        Route::get('compliance/modules/branches', [\App\Http\Controllers\HR\ComplianceModuleController::class, 'getBranches'])->name('compliance.modules.branches');
        Route::post('compliance/modules/branches', [\App\Http\Controllers\HR\ComplianceModuleController::class, 'storeBranch'])->name('compliance.modules.branches.store');
        Route::put('compliance/modules/branches/{location}', [\App\Http\Controllers\HR\ComplianceModuleController::class, 'updateBranch'])->name('compliance.modules.branches.update');
        Route::delete('compliance/modules/branches/{location}', [\App\Http\Controllers\HR\ComplianceModuleController::class, 'destroyBranch'])->name('compliance.modules.branches.destroy');
        
        Route::get('compliance/modules/rules/{state_code}', [\App\Http\Controllers\HR\ComplianceModuleController::class, 'getRules'])->name('compliance.modules.rules');
        Route::post('compliance/modules/rules', [\App\Http\Controllers\HR\ComplianceModuleController::class, 'saveRule'])->name('compliance.modules.rules.save');
        
        Route::get('compliance/modules/licences', [\App\Http\Controllers\HR\ComplianceModuleController::class, 'getLicences'])->name('compliance.modules.licences');
        Route::post('compliance/modules/licences', [\App\Http\Controllers\HR\ComplianceModuleController::class, 'uploadLicence'])->name('compliance.modules.licences.upload');
        Route::delete('compliance/modules/licences/{licence}', [\App\Http\Controllers\HR\ComplianceModuleController::class, 'destroyLicence'])->name('compliance.modules.licences.destroy');
        
        Route::get('compliance/modules/salary/minimum-wages', [\App\Http\Controllers\HR\ComplianceModuleController::class, 'getMinimumWages'])->name('compliance.modules.salary.minimum-wages');
        Route::post('compliance/modules/salary/minimum-wages', [\App\Http\Controllers\HR\ComplianceModuleController::class, 'saveMinimumWage'])->name('compliance.modules.salary.minimum-wages.save');
        Route::delete('compliance/modules/salary/minimum-wages/{minimumWage}', [\App\Http\Controllers\HR\ComplianceModuleController::class, 'destroyMinimumWage'])->name('compliance.modules.salary.minimum-wages.destroy');
        Route::post('compliance/modules/salary/validate', [\App\Http\Controllers\HR\ComplianceModuleController::class, 'validateSalary'])->name('compliance.modules.salary.validate');

        // Loans & Advances
        Route::get('loans/stats', [App\Http\Controllers\HR\LoanController::class, 'stats'])->name('loans.api.stats');
        Route::get('loans', [App\Http\Controllers\HR\LoanController::class, 'index'])->name('loans.index');
        Route::post('loans', [App\Http\Controllers\HR\LoanController::class, 'store'])->name('loans.store');
        Route::post('loans/{loan}/approve', [App\Http\Controllers\HR\LoanController::class, 'approve'])->name('loans.approve');
        Route::post('loans/{loan}/reject', [App\Http\Controllers\HR\LoanController::class, 'reject'])->name('loans.reject');
        
        // Loan Configuration API (Tab E)
        Route::get('loans/api/products', [App\Http\Controllers\HR\LoanController::class, 'products'])->name('loans.api.products');
        
        // Loan APIs (Tab B, C, D)
        Route::get('loans/api/queue', [App\Http\Controllers\HR\LoanController::class, 'queue'])->name('loans.api.queue');
        Route::get('loans/api/portfolio', [App\Http\Controllers\HR\LoanController::class, 'portfolio'])->name('loans.api.portfolio');
        Route::get('loans/api/disbursement', [App\Http\Controllers\HR\LoanController::class, 'disbursement'])->name('loans.api.disbursement');
        
        // Disbursement Actions
        Route::post('loans/sync-payroll', [App\Http\Controllers\HR\LoanController::class, 'pushToPayroll'])->name('loans.sync');
        Route::post('loans/{loan}/disburse', [App\Http\Controllers\HR\LoanController::class, 'markDisbursed'])->name('loans.disburse');
        
        // Active Actions
        Route::post('loans/{loan}/foreclose', [App\Http\Controllers\HR\LoanController::class, 'foreclose'])->name('loans.foreclose');
        Route::post('loans/{loan}/pause', [App\Http\Controllers\HR\LoanController::class, 'pause'])->name('loans.pause');

        // Analytics (JSON)
        Route::get('analytics/payroll', [App\Http\Controllers\Api\AnalyticsController::class, 'getPayrollStats'])->name('analytics.payroll');
        Route::get('analytics/loans', [App\Http\Controllers\Api\AnalyticsController::class, 'getLoanStats'])->name('analytics.loans');

        // FnF Settlement
        Route::get('employees/{employee}/settlement', [\App\Http\Controllers\HR\SettlementController::class, 'create'])->name('settlement.create');
        Route::post('employees/{employee}/settlement', [\App\Http\Controllers\HR\SettlementController::class, 'store'])->name('settlement.store');
        Route::get('employees/{employee}/orchestration', [\App\Http\Controllers\HR\ExitOrchestrationController::class, 'show'])->name('exits.orchestration');
        Route::post('exits/orchestration/update/{id}', [\App\Http\Controllers\HR\ExitOrchestrationController::class, 'updateStatus'])->name('exits.orchestration.update');

        // Expenses - now handled by Comprehensive Dashboard at /hr/expenses prefix
        // Expenses - Comprehensive Dashboard
        Route::prefix('expenses')->name('expenses.')->group(function() {
            Route::get('/', [\App\Http\Controllers\HR\ExpenseDashboardController::class, 'index'])->name('dashboard');
            Route::get('/api/stats', [\App\Http\Controllers\HR\ExpenseDashboardController::class, 'stats'])->name('api.stats');
            Route::get('/api/approvals', [\App\Http\Controllers\HR\ExpenseDashboardController::class, 'approvals'])->name('api.approvals');
            Route::get('/api/disbursement', [\App\Http\Controllers\HR\ExpenseDashboardController::class, 'disbursement'])->name('api.disbursement');
            Route::get('/api/history', [\App\Http\Controllers\HR\ExpenseDashboardController::class, 'history'])->name('api.history');
        });
        
        // Employee Salary (Appraisals)
        Route::post('employees/{employee}/salary', [App\Http\Controllers\HR\EmployeeSalaryController::class, 'store'])->name('employees.salary.store');

        // Salary Holds (Must be before wildcard {payroll})
        Route::resource('hr/payroll/holds', \App\Http\Controllers\HR\PayrollHoldController::class)
             ->names('payroll.holds')
             ->only(['index', 'store', 'update', 'destroy']);

        // Variable Pay (Must be before wildcard {payroll})
        Route::post('payroll/variable/bulk', [\App\Http\Controllers\HR\VariablePayoutController::class, 'bulkStore'])->name('payroll.variable.bulk');
        Route::post('payroll/variable/import', [\App\Http\Controllers\HR\VariablePayoutController::class, 'importCsv'])->name('payroll.variable.import');
        Route::resource('payroll/variable', \App\Http\Controllers\HR\VariablePayoutController::class)
             ->names('payroll.variable')
             ->only(['index', 'store', 'update', 'destroy']);


        // Tax Engine
        Route::prefix('tax')->name('tax.')->group(function() {
             Route::get('declarations', [\App\Http\Controllers\HR\TaxDeclarationController::class, 'index'])->name('declarations.index');
             
             // Settings (Regimes & Slabs) 
             Route::get('configuration', [\App\Http\Controllers\HR\TaxConfigurationController::class, 'index'])->name('configuration.index');
             Route::post('configuration/settings', [\App\Http\Controllers\HR\TaxConfigurationController::class, 'storeSettings'])->name('configuration.settings');
             Route::post('configuration/slabs', [\App\Http\Controllers\HR\TaxConfigurationController::class, 'storeSlabs'])->name('configuration.slabs');
             Route::post('configuration/limits', [\App\Http\Controllers\HR\TaxConfigurationController::class, 'storeLimits'])->name('configuration.limits');
             // User requested: GET /hr/payroll/tax-settings.
             // So it should be in the 'hr.' prefix group, likely under 'payroll' prefix or just 'hr'.
             // Let's add it to the Payroll Group (lines 160+) to match request.

             Route::post('declarations', [\App\Http\Controllers\HR\TaxDeclarationController::class, 'store'])->name('declarations.store');
             Route::post('regime', [\App\Http\Controllers\HR\TaxDeclarationController::class, 'updateRegime'])->name('regime.update');
             Route::get('computation', [\App\Http\Controllers\HR\TaxDeclarationController::class, 'downloadComputation'])->name('computation.download');
             
             // Proofs
             Route::get('verification-queue', [\App\Http\Controllers\HR\VerificationController::class, 'index'])->name('proofs.index');
             Route::post('proofs/verify', [\App\Http\Controllers\HR\VerificationController::class, 'verify'])->name('proofs.verify');
             Route::post('proofs/reject', [\App\Http\Controllers\HR\VerificationController::class, 'reject'])->name('proofs.reject');
             
             // Reports & Analytics
             Route::get('reports/dashboard', [\App\Http\Controllers\HR\TaxReportController::class, 'dashboard'])->name('reports.dashboard');
             Route::get('reports', [\App\Http\Controllers\HR\TaxReportController::class, 'index'])->name('reports.index'); // Export Hub
             Route::get('reports/download', [\App\Http\Controllers\HR\TaxReportController::class, 'export'])->name('reports.download');

             // Old Proofs (if needed for employee deletion)
             Route::delete('proofs/{proof}', [\App\Http\Controllers\HR\InvestmentProofController::class, 'destroy'])->name('proofs.destroy');
        });

        // Deep Analytics (Strategic Decision Engine)
        Route::prefix('analytics')->name('analytics.')->group(function() {
            Route::get('command-center', [\App\Http\Controllers\HR\AnalyticsController::class, 'commandCenter'])->name('command-center');
            Route::get('strategic', [\App\Http\Controllers\HR\AnalyticsController::class, 'strategicIndex'])->name('strategic');
            Route::get('strategic', [\App\Http\Controllers\HR\AnalyticsController::class, 'strategicIndex'])->name('strategic');
            Route::get('player-search', [\App\Http\Controllers\HR\AnalyticsController::class, 'playerSearch'])->name('player-search');
            Route::get('career-dna/{employee}', [\App\Http\Controllers\HR\AnalyticsController::class, 'careerDna'])->name('career-dna');
        });

        // Finance Hub (Payroll, Compensation, Exit)
        Route::prefix('finance')->name('finance.')->group(function() {
             Route::get('/hub', [\App\Http\Controllers\HR\HRFinanceHubController::class, 'index'])->name('hub');
             Route::post('/deferred', [\App\Http\Controllers\HR\HRFinanceHubController::class, 'storeDeferred'])->name('deferred.store');
             Route::post('/variable/adhoc', [\App\Http\Controllers\HR\HRFinanceHubController::class, 'storeAdhoc'])->name('variable.store-adhoc');
             Route::post('/payslip/{payslip}/update', [\App\Http\Controllers\HR\HRFinanceHubController::class, 'updatePayslip'])->name('payslip.update');
             Route::post('/appraisals', [\App\Http\Controllers\HR\HRFinanceHubController::class, 'storeAppraisals'])->name('appraisals.store');
             
             // Exit & FNF
             Route::post('/exits', [\App\Http\Controllers\HR\HRFinanceHubController::class, 'storeExit'])->name('exit.store');
             Route::post('/exits/{exit}/stage', [\App\Http\Controllers\HR\HRFinanceHubController::class, 'updateExitStage'])->name('exit.update-stage');
        });
    });

    // ============================================================================
    // LMS ROUTES (Legacy Basic LMS)
    // ============================================================================
    
    Route::prefix('hr/lms')->middleware(['auth'])->name('hr.lms.')->group(function() {
        Route::get('/', [\App\Http\Controllers\HR\LMS\CourseController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\HR\LMS\CourseController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\HR\LMS\CourseController::class, 'store'])->name('store');
        Route::get('/questions', [\App\Http\Controllers\HR\LMS\QuestionController::class, 'index'])->name('questions.index');
        Route::post('/questions', [\App\Http\Controllers\HR\LMS\QuestionController::class, 'store'])->name('questions.store');
        Route::put('/questions/{question}', [\App\Http\Controllers\HR\LMS\QuestionController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [\App\Http\Controllers\HR\LMS\QuestionController::class, 'destroy'])->name('questions.destroy');
        Route::get('/questions/{question}/analytics', [\App\Http\Controllers\HR\LMS\QuestionController::class, 'analytics'])->name('questions.analytics');
        Route::post('/questions/bulk-import', [\App\Http\Controllers\HR\LMS\QuestionController::class, 'bulkImport'])->name('questions.bulk-import');
        Route::get('/questions/template', [\App\Http\Controllers\HR\LMS\QuestionController::class, 'downloadTemplate'])->name('questions.template');
        Route::get('/analytics', [\App\Http\Controllers\HR\LMS\AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/course/{course}', [\App\Http\Controllers\HR\LMS\AnalyticsController::class, 'course'])->name('analytics.course');
        Route::get('/analytics/export', [\App\Http\Controllers\HR\LMS\AnalyticsController::class, 'export'])->name('analytics.export');
        Route::get('/{course}', [\App\Http\Controllers\HR\LMS\CourseController::class, 'show'])->name('show');
        Route::get('/{course}/edit', [\App\Http\Controllers\HR\LMS\CourseController::class, 'edit'])->name('edit');
        Route::put('/{course}', [\App\Http\Controllers\HR\LMS\CourseController::class, 'update'])->name('update');
        Route::delete('/{course}', [\App\Http\Controllers\HR\LMS\CourseController::class, 'destroy'])->name('destroy');
        Route::post('/{course}/assign', [\App\Http\Controllers\HR\LMS\CourseController::class, 'assign'])->name('assign');
    });
    
    Route::prefix('lms')->middleware(['auth'])->name('lms.')->group(function() {
        Route::get('/my-courses', [\App\Http\Controllers\HR\LMS\PlayerController::class, 'myCourses'])->name('my-courses');
        Route::get('/play/{course}', [\App\Http\Controllers\HR\LMS\PlayerController::class, 'play'])->name('play');
        Route::post('/start/{course}', [\App\Http\Controllers\HR\LMS\PlayerController::class, 'start'])->name('start');
        Route::post('/submit/{attempt}', [\App\Http\Controllers\HR\LMS\PlayerController::class, 'submit'])->name('submit');
        Route::post('/track-violation/{attempt}', [\App\Http\Controllers\HR\LMS\PlayerController::class, 'trackViolation'])->name('track-violation');
        Route::get('/certificate/{certificate}/download', [\App\Http\Controllers\HR\LMS\CertificateController::class, 'download'])->name('certificate.download');
    });
    
    Route::get('/lms/verify/{code}', [\App\Http\Controllers\HR\LMS\CertificateController::class, 'verify'])->name('lms.verify');

    // ============================================================================
    // ADVANCED DEEP LMS - MANAGEMENT HUB (NEW)
    // ============================================================================
    Route::middleware(['auth', 'role:Admin|Manager'])->prefix('admin/lms-hub')->name('lms.hub.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LMS\LmsHubController::class, 'index'])->name('index');
        
        // Explicit routes mapped seamlessly for HRMS Sidebar
        Route::get('/dashboard', function(\Illuminate\Http\Request $request) {
            $request->merge(['section' => 'dashboard']);
            return app(\App\Http\Controllers\LMS\LmsHubController::class)->index($request);
        })->name('dashboard');
        
        Route::get('/certificates', function(\Illuminate\Http\Request $request) {
            $request->merge(['section' => 'certificates']);
            return app(\App\Http\Controllers\LMS\LmsHubController::class)->index($request);
        })->name('certificates');
        
        Route::get('/courses', function(\Illuminate\Http\Request $request) {
            $request->merge(['section' => 'courses']);
            return app(\App\Http\Controllers\LMS\LmsHubController::class)->index($request);
        })->name('courses');
        
        Route::get('/analytics', function(\Illuminate\Http\Request $request) {
            $request->merge(['section' => 'analytics']);
            return app(\App\Http\Controllers\LMS\LmsHubController::class)->index($request);
        })->name('analytics');
        
        Route::get('/erp', function(\Illuminate\Http\Request $request) {
            $request->merge(['section' => 'erp']);
            return app(\App\Http\Controllers\LMS\LmsHubController::class)->index($request);
        })->name('erp');
    });

    // Student Dashboard (Requires Auth)
    Route::middleware(['auth'])->prefix('lms')->name('lms.store.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\LMS\LearnerController::class, 'hub'])->name('dashboard');
        Route::post('/checkout/{course}', [\App\Http\Controllers\LMS\LmsStorefrontController::class, 'checkout'])->name('checkout');
    });

    // ============================================================================
    // ADVANCED DEEP LMS ROUTES
    // ============================================================================

    // Admin / Course Builder
    Route::prefix('lms/admin')->middleware(['auth'])->name('lms.admin.')->group(function () {
        Route::get('/courses', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'index'])->name('builder.index');
        Route::get('/courses/create', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'create'])->name('courses.create');
        Route::post('/courses', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'show'])->name('courses.show');
        Route::get('/courses/{course}/builder', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'builder'])->name('courses.builder');
        Route::put('/courses/{course}', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'destroy'])->name('courses.destroy');
        Route::post('/courses/{course}/publish', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'publish'])->name('courses.publish');
        Route::post('/courses/{course}/enroll-bulk', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'bulkEnroll'])->name('courses.enroll-bulk');

        // Structure builder routes
        Route::post('/courses/{course}/modules', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'storeModule'])->name('courses.modules.store');
        Route::put('/courses/{course}/modules/{module}', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'updateModule'])->name('courses.modules.update');
        Route::delete('/courses/{course}/modules/{module}', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'destroyModule'])->name('courses.modules.destroy');
        Route::post('/courses/{course}/modules/{module}/chapters', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'storeChapter'])->name('courses.chapters.store');
        Route::put('/courses/{course}/modules/{module}/chapters/{chapter}', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'updateChapter'])->name('courses.chapters.update');
        Route::post('/courses/{course}/modules/{module}/chapters/{chapter}/concepts', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'storeConcept'])->name('courses.concepts.store');
        Route::put('/courses/{course}/modules/{module}/chapters/{chapter}/concepts/{concept}', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'updateConcept'])->name('courses.concepts.update');
        Route::post('/courses/{course}/concepts/{concept}/activities', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'storeActivity'])->name('courses.activities.store');
        Route::post('/courses/{course}/reorder', [\App\Http\Controllers\LMS\CourseBuilderController::class, 'reorderStructure'])->name('courses.reorder');

        // Analytics
        Route::get('/analytics', [\App\Http\Controllers\LMS\LmsAnalyticsController::class, 'commandCenter'])->name('analytics.command-center');
        Route::get('/analytics/courses/{course}', [\App\Http\Controllers\LMS\LmsAnalyticsController::class, 'courseAnalytics'])->name('analytics.course');
        Route::get('/analytics/institutions/{institution}', [\App\Http\Controllers\LMS\LmsAnalyticsController::class, 'institutionReport'])->name('analytics.institution');

        // ERP Integration
        Route::get('/erp', [\App\Http\Controllers\LMS\ErpIntegrationController::class, 'index'])->name('erp.index');
        Route::post('/erp/sync', [\App\Http\Controllers\LMS\ErpIntegrationController::class, 'sync'])->name('erp.sync');
    });

    // Learner routes
    Route::prefix('lms/learn')->middleware(['auth'])->name('lms.learn.')->group(function () {
        Route::get('/hub', [\App\Http\Controllers\LMS\LearnerController::class, 'hub'])->name('hub');
        Route::get('/courses/{course}', [\App\Http\Controllers\LMS\LearnerController::class, 'courseDetail'])->name('course.detail');
        Route::get('/courses/{course}/play/{concept}', [\App\Http\Controllers\LMS\LearnerController::class, 'playConcept'])->name('course.play-concept');
        Route::post('/concept/{concept}/complete', [\App\Http\Controllers\LMS\LearnerController::class, 'markConceptComplete'])->name('concept.complete');
        Route::get('/certificates', [\App\Http\Controllers\LMS\LearnerController::class, 'certificates'])->name('certificates');
        Route::post('/video/{videoLesson}/heartbeat', [\App\Http\Controllers\LMS\LearnerController::class, 'videoHeartbeat'])->name('video.heartbeat');
        Route::post('/video/{videoLesson}/checkpoint', [\App\Http\Controllers\LMS\LearnerController::class, 'videoCheckpointAnswer'])->name('video.checkpoint');
        Route::post('/activity/{activity}/quiz/start', [\App\Http\Controllers\LMS\LearnerController::class, 'startQuiz'])->name('quiz.start');
        Route::post('/quiz/{attempt}/submit', [\App\Http\Controllers\LMS\LearnerController::class, 'submitQuiz'])->name('quiz.submit');
        Route::post('/quiz/{attempt}/violation', [\App\Http\Controllers\LMS\LearnerController::class, 'trackViolation'])->name('quiz.violation');
        Route::post('/activity/{activity}/assignment/submit', [\App\Http\Controllers\LMS\LearnerController::class, 'submitAssignment'])->name('assignment.submit');
    });

    // Faculty / Instructor Journey
    Route::prefix('lms/faculty')->middleware(['auth'])->name('lms.faculty.')->group(function () {
        Route::get('/hub', [\App\Http\Controllers\LMS\FacultyHubController::class, 'index'])->name('hub');
        Route::get('/courses/{course}', [\App\Http\Controllers\LMS\FacultyHubController::class, 'courseDetail'])->name('course.detail');
        Route::post('/message', [\App\Http\Controllers\LMS\FacultyHubController::class, 'sendMessage'])->name('message.send');
    });

    // Stakeholder Views
    Route::prefix('lms/stakeholder')->name('lms.stakeholder.')->group(function () {
        Route::get('/parent', [\App\Http\Controllers\LMS\StakeholderController::class, 'parentHub'])->name('parent');
        Route::get('/employer/{employee}', [\App\Http\Controllers\LMS\StakeholderController::class, 'employerTranscript'])->name('employer');
    });


    // --- Manager Modules ---
    Route::middleware(['role:Manager|Admin'])->prefix('manager')->name('manager.')->group(function () {
        Route::get('/approvals', [App\Http\Controllers\Manager\ApprovalController::class, 'index'])->name('approvals.index');
        Route::post('/approvals/action', [App\Http\Controllers\Manager\ApprovalController::class, 'action'])->name('approvals.action');
        Route::post('/approvals/bulk-action', [App\Http\Controllers\Manager\ApprovalController::class, 'bulkAction'])->name('approvals.bulk_action');
 
    });

    // --- Talent Acquisition (Recruitment) ---
    // Moved here to be top-level (talent.hub) instead of admin.talent.hub
    Route::middleware(['role:Admin|Manager|HR'])->prefix('talent')->name('talent.')->group(function () {
             Route::get('/', [App\Http\Controllers\Talent\ReportingController::class, 'index'])->name('hub');
             Route::get('/analytics', [App\Http\Controllers\Talent\ReportingController::class, 'index'])->name('reports'); // Renamed internal alias
             Route::get('/jobs/create', [App\Http\Controllers\Talent\RecruitmentController::class, 'create'])->name('jobs.create');
             Route::post('/jobs', [App\Http\Controllers\Talent\RecruitmentController::class, 'store'])->name('jobs.store');
             Route::get('/jobs/{job}/edit', [App\Http\Controllers\Talent\RecruitmentController::class, 'edit'])->name('jobs.edit');
             Route::put('/jobs/{job}', [App\Http\Controllers\Talent\RecruitmentController::class, 'update'])->name('jobs.update');
             Route::get('/jobs', [App\Http\Controllers\Talent\RecruitmentController::class, 'index'])->name('jobs.index'); 
             Route::post('/jobs/bulk', [App\Http\Controllers\Talent\RecruitmentController::class, 'bulkAction'])->name('jobs.bulk');
             Route::put('/jobs/stage-config/bulk', [App\Http\Controllers\Talent\RecruitmentController::class, 'bulkUpdateStageConfig'])->name('jobs.stage_config.bulk'); // Bulk Config
             Route::put('/jobs/{job}/stage-config', [App\Http\Controllers\Talent\RecruitmentController::class, 'updateStageConfig'])->name('jobs.stage_config'); // Config Route
             Route::post('/job-categories', [App\Http\Controllers\Talent\RecruitmentController::class, 'storeCategory'])->name('categories.store'); 
             
             // Candidates (ATS)
             Route::get('/candidates', [App\Http\Controllers\Talent\CandidateController::class, 'index'])->name('candidates.index');
             Route::get('/candidates/{candidate}/quick-view', [App\Http\Controllers\Talent\CandidateController::class, 'quickView'])->name('candidates.quick-view');
             Route::post('/candidates/{id}/move', [App\Http\Controllers\Talent\CandidateController::class, 'updateStatus'])->name('candidates.move');
             Route::post('/candidates/{candidate}/rate-screening', [App\Http\Controllers\Talent\CandidateController::class, 'rateScreening'])->name('candidates.rate-screening');
             Route::put('/candidates/{application}/reject', [App\Http\Controllers\Talent\CandidateController::class, 'reject'])->name('candidates.reject');
             Route::put('/candidates/{application}/offer', [App\Http\Controllers\Talent\CandidateController::class, 'offer'])->name('candidates.offer');
             Route::post('/interviews/{interview}/feedback', [App\Http\Controllers\Talent\CandidateController::class, 'storeFeedback'])->name('interviews.feedback.store');
             
             Route::post('/candidates/{applicationId}/interviews', [App\Http\Controllers\Talent\CandidateController::class, 'storeInterview'])->name('candidates.interviews.store');
             Route::put('/candidates/interviews/{interview}', [App\Http\Controllers\Talent\CandidateController::class, 'updateInterview'])->name('candidates.interviews.update');
             Route::post('/candidates/interviews/{interview}/remind', [App\Http\Controllers\Talent\CandidateController::class, 'sendReminder'])->name('candidates.interviews.remind');
             Route::post('/candidates/interviews/{interview}/cancel', [App\Http\Controllers\Talent\CandidateController::class, 'cancelInterview'])->name('candidates.interviews.cancel');
             // Onboarding
             Route::get('/onboard', [App\Http\Controllers\Talent\OnboardingController::class, 'index'])->name('onboard.index');
             Route::get('/candidates/{candidate}/onboard', [App\Http\Controllers\Talent\OnboardingController::class, 'create'])->name('candidates.onboard.create');
             Route::post('/candidates/{candidate}/onboard', [App\Http\Controllers\Talent\OnboardingController::class, 'store'])->name('candidates.onboard.store');

             Route::post('/candidates/bulk', [App\Http\Controllers\Talent\CandidateController::class, 'bulkAction'])->name('candidates.bulk');
             Route::resource('candidates', App\Http\Controllers\Talent\CandidateController::class)->except(['index', 'updateStatus']);

             // Offers (Corporate)
             Route::get('/offers', [App\Http\Controllers\Talent\OfferLetterController::class, 'index'])->name('offers.index');
             Route::get('/offers/create', [App\Http\Controllers\Talent\OfferLetterController::class, 'create'])->name('offers.create');
             Route::post('/offers', [App\Http\Controllers\Talent\OfferLetterController::class, 'store'])->name('offers.store');
             // Specific routes BEFORE wildcards
             Route::post('/offers/preview', [App\Http\Controllers\Talent\OfferLetterController::class, 'preview'])->name('offers.preview'); 
             Route::get('/offers/{offer}/approval', [App\Http\Controllers\Talent\OfferLetterController::class, 'approvalView'])->name('offers.approval-internal'); 
             Route::post('/offers/{offer}/approve-action', [App\Http\Controllers\Talent\OfferLetterController::class, 'approveAction'])->name('offers.approve-action'); 
             Route::get('/offers/{offer}', [App\Http\Controllers\Talent\OfferLetterController::class, 'show'])->name('offers.show');
             Route::post('/offers/{offer}/send', [App\Http\Controllers\Talent\OfferLetterController::class, 'send'])->name('offers.send');
             Route::post('/offers/{offer}/withdraw', [App\Http\Controllers\Talent\OfferLetterController::class, 'withdraw'])->name('offers.withdraw');
             Route::post('/offers/{offer}/extend', [App\Http\Controllers\Talent\OfferLetterController::class, 'extend'])->name('offers.extend');
             Route::post('/offers/{offer}/release', [App\Http\Controllers\Talent\OfferLetterController::class, 'release'])->name('offers.release');
             Route::post('/offers/{offer}/resend', [App\Http\Controllers\Talent\OfferLetterController::class, 'resend'])->name('offers.resend');
             // Route::post('/offers/preview', ...); // Duplicate line removed if present, but keeping context safe.
             
             Route::get('/offers/documents/{documentRequest}/download', [App\Http\Controllers\Talent\OfferLetterController::class, 'downloadDocument'])->name('offers.documents.download');
             Route::get('/offers/{offer}/download-pdf', [App\Http\Controllers\Talent\OfferLetterController::class, 'downloadOffer'])->name('offers.download'); // PDF Download
             Route::post('/offers/documents/{documentRequest}/verify', [App\Http\Controllers\Talent\OfferLetterController::class, 'verifyDocument'])->name('offers.documents.verify');

             
             // Screening
             Route::resource('screening-templates', App\Http\Controllers\Talent\ScreeningTemplateController::class);
    });

    // --- Performance Management (Phase 4) ---
    Route::middleware(['role:Admin|Manager|Employee'])->prefix('performance')->name('performance.')->group(function () {
        // Goals
        Route::resource('goals', \App\Http\Controllers\Performance\GoalController::class);
        Route::patch('goals/{goal}/approve', [\App\Http\Controllers\Performance\GoalController::class, 'approve'])->name('goals.approve');
        
        // Appraisals
        Route::get('team', [\App\Http\Controllers\Performance\AppraisalController::class, 'team'])->name('team'); // Manager View
        Route::resource('appraisals', \App\Http\Controllers\Performance\AppraisalController::class)->only(['show', 'update']);
        Route::post('appraisals/{appraisal}/submit', [\App\Http\Controllers\Performance\AppraisalController::class, 'submit'])->name('appraisals.submit');

        // Admin Cycles (Admin/HR Only)
        Route::resource('cycles', \App\Http\Controllers\Performance\AppraisalCycleController::class)->middleware('role:Admin|HR');
    });

    // --- Project Management (Employee/Universal Access) ---
    // Clients (CRUD)
    Route::resource('projects/clients', App\Http\Controllers\ProjectManagement\ClientController::class)
         ->names('projects.clients') // This prefixes names with 'projects.clients.' -> projects.clients.index, projects.clients.store
         ->parameter('clients', 'client'); // Parameter name {client}

    Route::post('projects/clients/{client}/invite', [App\Http\Controllers\ProjectManagement\ClientController::class, 'inviteUser'])->name('projects.clients.invite');
         
    // Planner UI & API (Universal)
    Route::get('/projects/planner', function (\Illuminate\Http\Request $request) { 
        return Inertia::render('Project/Planner/Index', [
            'initialProjectId' => $request->query('project')
        ]); 
    })->name('planner.index');
    Route::get('/planner/data', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'loadData'])->name('planner.data');
    Route::post('/planner/tasks', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'store'])->name('planner.store');
    Route::put('/planner/tasks/{id}', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'update'])->name('planner.update');
    Route::delete('/planner/tasks/{id}', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'destroy'])->name('planner.destroy');
    Route::post('/planner/move-task/{id}', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'moveTask'])->name('planner.move');
    Route::post('/planner/assign', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'assign'])->name('planner.assign');
    Route::post('/planner/extend-task/{id}', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'extendTask'])->name('planner.extend-task');
    
    // Reports
    Route::get('/planner/reports', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'getReports'])->name('planner.reports');
    Route::get('/planner/reports/export', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'exportReports'])->name('planner.reports.export');

    // Documents
    Route::get('/planner/shareables', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'getShareables'])->name('planner.shareables');
    Route::get('/planner/documents', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'getDocuments'])->name('planner.documents.index');
    Route::post('/planner/documents', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'uploadDocument'])->name('planner.documents.store');
    Route::delete('/planner/documents/{id}', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'deleteDocument'])->name('planner.documents.delete');
    Route::get('/planner/documents/{id}/download', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'downloadDocument'])->name('planner.documents.download');

    // Projects & Tasks (Universal Access - Protected by Policies)
    // Specific routes MUST generally come before Resources to avoid wildcard shadowing
    Route::get('/projects/dashboard', [App\Http\Controllers\ProjectManagement\ProjectController::class, 'index'])->name('projects.dashboard'); // Alias
    Route::get('/projects/my-tasks', [App\Http\Controllers\ProjectManagement\TaskController::class, 'myTasks'])->name('projects.my-tasks');
    
    Route::post('projects/{project}/archive', [App\Http\Controllers\ProjectManagement\ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('projects/{project}/unarchive', [App\Http\Controllers\ProjectManagement\ProjectController::class, 'unarchive'])->name('projects.unarchive');
    
    Route::resource('projects', App\Http\Controllers\ProjectManagement\ProjectController::class);
    Route::post('projects/{project}/planner/bulk-assign', [App\Http\Controllers\ProjectManagement\ProjectController::class, 'bulkAssign'])->name('projects.planner.bulk-assign');
    
    // Sprints & Stages (Dynamic Board)
    Route::apiResource('projects.stages', App\Http\Controllers\ProjectManagement\ProjectStageController::class);
    Route::post('projects/{project}/stages/reorder', [App\Http\Controllers\ProjectManagement\ProjectStageController::class, 'reorder'])->name('projects.stages.reorder');
    
    Route::apiResource('projects.sprints', App\Http\Controllers\ProjectManagement\SprintController::class);
    Route::post('projects/{project}/sprints/{sprint}/start', [App\Http\Controllers\ProjectManagement\SprintController::class, 'start'])->name('projects.sprints.start');
    Route::post('projects/{project}/sprints/{sprint}/complete', [App\Http\Controllers\ProjectManagement\SprintController::class, 'complete'])->name('projects.sprints.complete');
    
    Route::apiResource('projects.priorities', App\Http\Controllers\ProjectManagement\ProjectPriorityController::class);
    
    // Kanban & Tasks
    Route::get('projects/{project}/board', [App\Http\Controllers\Admin\KanbanController::class, 'index'])->name('projects.board');
    Route::get('projects/{project}/board/activities', [App\Http\Controllers\Admin\KanbanController::class, 'activityLog'])->name('projects.board.activities');
    Route::get('projects/{project}/board/activities/export', [App\Http\Controllers\Admin\KanbanController::class, 'exportActivityLog'])->name('projects.board.activities.export');

    // Task CRUD (used by Board modal + Task List page)
    Route::post('projects/{project}/tasks', [App\Http\Controllers\Admin\KanbanController::class, 'store'])->name('projects.tasks.store');
    Route::put('projects/{project}/tasks/{task}', [App\Http\Controllers\Admin\KanbanController::class, 'update'])->name('projects.tasks.update');
    Route::delete('projects/{project}/tasks/{task}', [App\Http\Controllers\Admin\KanbanController::class, 'destroy'])->name('projects.tasks.destroy');
    Route::get('projects/{project}/tasks/{task}', [App\Http\Controllers\Admin\KanbanController::class, 'show'])->name('projects.tasks.show');
    Route::post('projects/{project}/tasks/{task}/move', [App\Http\Controllers\Admin\KanbanController::class, 'move'])->name('projects.tasks.move');
    Route::post('projects/{project}/tasks/bulk-update', [App\Http\Controllers\Admin\KanbanController::class, 'bulkUpdate'])->name('projects.tasks.bulk_update');
    Route::post('projects/{project}/tasks/reorder', [App\Http\Controllers\Admin\KanbanController::class, 'reorder'])->name('projects.tasks.reorder');
    
    // Task Hub API (Comments, Checklists)
    Route::controller(App\Http\Controllers\ProjectManagement\TaskCommentController::class)->group(function () {
        Route::post('tasks/{task}/comments', 'store')->name('tasks.comments.store');
        Route::delete('comments/{comment}', 'destroy')->name('tasks.comments.destroy');
    });

    Route::controller(App\Http\Controllers\ProjectManagement\TaskChecklistController::class)->group(function () {
        Route::post('tasks/{task}/checklists', 'store')->name('tasks.checklists.store');
        Route::put('checklists/{checklist}', 'update')->name('tasks.checklists.update');
        Route::delete('checklists/{checklist}', 'destroy')->name('tasks.checklists.destroy');
        Route::post('checklists/{checklist}/toggle', 'toggle')->name('tasks.checklists.toggle');
        Route::post('tasks/{task}/checklists/clone', 'cloneFromTask')->name('tasks.checklists.clone');
        Route::post('tasks/{task}/checklists/import', 'importFile')->name('tasks.checklists.import');
    });

    Route::controller(App\Http\Controllers\ProjectManagement\TaskPullRequestController::class)->group(function () {
        Route::post('tasks/{task}/pull-requests', 'store')->name('tasks.pull-requests.store');
        Route::patch('pull-requests/{pr}', 'update')->name('tasks.pull-requests.update');
        Route::delete('pull-requests/{pr}', 'destroy')->name('tasks.pull-requests.destroy');
    });

    // Project Tabs (New)
    Route::get('projects/{project}/list', [App\Http\Controllers\ProjectManagement\ProjectController::class, 'taskList'])->name('projects.tasks.index');
    Route::post('projects/{project}/tasks/{task}/backlog', [App\Http\Controllers\Admin\KanbanController::class, 'moveToBacklog'])->name('projects.tasks.backlog');
    Route::post('projects/{project}/tasks/{task}/restore', [App\Http\Controllers\Admin\KanbanController::class, 'restoreFromBacklog'])->name('projects.tasks.restore');
    Route::get('projects/{project}/modules', [App\Http\Controllers\ProjectManagement\ProjectController::class, 'modules'])->name('projects.modules.index');
    Route::get('projects/{project}/files', [App\Http\Controllers\ProjectManagement\ProjectController::class, 'files'])->name('projects.files');
    Route::get('projects/{project}/reports', [App\Http\Controllers\ProjectManagement\ProjectController::class, 'reports'])->name('projects.reports');
    Route::get('projects/{project}/war-room', [App\Http\Controllers\ProjectManagement\ProjectController::class, 'warRoom'])->name('projects.warroom');
    Route::post('projects/{project}/incidents', [App\Http\Controllers\ProjectManagement\ProjectController::class, 'storeIncident'])->name('projects.incidents.store');
    Route::put('projects/{project}/incidents/{incident}', [App\Http\Controllers\ProjectManagement\ProjectController::class, 'updateIncident'])->name('projects.incidents.update');
    Route::post('projects/{project}/incidents/{incident}/execute', [App\Http\Controllers\ProjectManagement\ProjectController::class, 'executeAction'])->name('projects.incidents.execute'); 


    // DevOps Dashboard
    Route::get('projects/{project}/devops', [App\Http\Controllers\ProjectManagement\DevOpsDashboardController::class, 'index'])->name('projects.devops.index');
    Route::get('projects/{project}/devops/export', [App\Http\Controllers\ProjectManagement\DevOpsDashboardController::class, 'export'])->name('projects.devops.export');
    Route::get('projects/{project}/devops/pulse', [App\Http\Controllers\ProjectManagement\DevOpsDashboardController::class, 'getPulseStats'])->name('projects.devops.pulse');
    Route::get('projects/{project}/devops/stats/prs', [App\Http\Controllers\ProjectManagement\DevOpsDashboardController::class, 'getPrAnalytics'])->name('projects.devops.stats.prs');
    Route::get('projects/{project}/devops/stats/reviews', [App\Http\Controllers\ProjectManagement\DevOpsDashboardController::class, 'getReviewIntelligence'])->name('projects.devops.stats.reviews');
    Route::get('projects/{project}/devops/commits', [App\Http\Controllers\ProjectManagement\DevOpsDashboardController::class, 'getCommits'])->name('projects.devops.commits');

    // Planner API
    Route::get('/planner/data', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'loadData'])->name('planner.data');
    Route::get('/planner/reports', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'reports'])->name('planner.reports');
    Route::post('/planner/tasks', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'store'])->name('planner.tasks.store');
    Route::put('/planner/tasks/{id}', [App\Http\Controllers\ProjectManagement\PlannerApiController::class, 'update'])->name('planner.tasks.update');

    Route::post('projects/{project}/tasks', [App\Http\Controllers\Admin\KanbanController::class, 'store'])->name('projects.tasks.store');
    Route::put('projects/{project}/tasks/{task}', [App\Http\Controllers\Admin\KanbanController::class, 'update'])->name('projects.tasks.update');
    Route::delete('projects/{project}/tasks/{task}', [App\Http\Controllers\Admin\KanbanController::class, 'destroy'])->name('projects.tasks.destroy');
    Route::post('projects/{project}/tasks/reorder', [App\Http\Controllers\Admin\KanbanController::class, 'reorder'])->name('projects.tasks.reorder');
    Route::post('projects/{project}/tasks/{task}/move', [App\Http\Controllers\Admin\KanbanController::class, 'move'])->name('projects.tasks.move'); // Drag & Drop
    Route::get('projects/{project}/tasks/{task}', [App\Http\Controllers\Admin\KanbanController::class, 'show'])->name('projects.tasks.show'); // Task Detail
    Route::post('projects/{project}/tasks/bulk-update', [App\Http\Controllers\ProjectManagement\TaskController::class, 'bulkUpdate'])->name('projects.tasks.bulk_update'); // New Row

    // Task Templates
    Route::get('projects/{project}/templates', [App\Http\Controllers\ProjectManagement\TaskTemplateController::class, 'index'])->name('projects.templates.index');
    Route::post('projects/{project}/templates', [App\Http\Controllers\ProjectManagement\TaskTemplateController::class, 'store'])->name('projects.templates.store');

    // Kits (Bundles) - Top Level for URL /kits
    Route::post('kits/{kit}/assign', [App\Http\Controllers\Admin\KitController::class, 'assign'])->name('kits.assign');
    Route::resource('kits', App\Http\Controllers\Admin\KitController::class);

    // --- Teams Management (New) ---
    Route::prefix('admin/teams')->name('admin.teams.')->group(function() {
        Route::get('/', [App\Http\Controllers\Admin\TeamController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\TeamController::class, 'store'])->name('store');
        Route::put('/{team}', [App\Http\Controllers\Admin\TeamController::class, 'update'])->name('update');
        Route::delete('/{team}', [App\Http\Controllers\Admin\TeamController::class, 'destroy'])->name('destroy');
        
        // Members
        Route::put('/{team}/members/bulk', [App\Http\Controllers\Admin\TeamController::class, 'bulkAddMembers'])->name('members.bulk');
        Route::delete('/{team}/members/{user}', [App\Http\Controllers\Admin\TeamController::class, 'removeMember'])->name('members.remove');
    });

    // --- HR / Admin Modules ---
    Route::middleware(['role:Admin|Manager'])->prefix('admin')->name('admin.')->group(function () {
        // Core
        Route::get('/ai-logs', [App\Http\Controllers\Admin\AiLogController::class, 'index'])->name('ai-logs.index');
        
        // DevOps Configuration
        Route::get('/devops/overview', [App\Http\Controllers\Admin\DevOpsController::class, 'globalDashboard'])->name('devops.dashboard'); // Global
        Route::get('/devops/export', [App\Http\Controllers\Admin\DevOpsController::class, 'export'])->name('devops.export');
        Route::get('/devops/providers', [App\Http\Controllers\Admin\DevOpsController::class, 'listProviders'])->name('devops.providers.index');
        Route::post('/devops/providers', [App\Http\Controllers\Admin\DevOpsController::class, 'storeProvider'])->name('devops.providers.store');
        Route::delete('/devops/providers/{id}', [App\Http\Controllers\Admin\DevOpsController::class, 'deleteProvider'])->name('devops.providers.destroy');
        Route::get('/devops/providers/{id}/repos', [App\Http\Controllers\Admin\DevOpsController::class, 'listRemoteRepositories'])->name('devops.providers.repos');
        Route::post('/devops/verify-token', [App\Http\Controllers\Admin\DevOpsController::class, 'verifyToken'])->name('devops.verify-token');
        Route::post('/devops/repos', [App\Http\Controllers\Admin\DevOpsController::class, 'mapRepository'])->name('devops.repos.map');
        Route::post('/devops/repos/{id}/webhooks', [App\Http\Controllers\Admin\DevOpsController::class, 'configureWebhooks'])->name('devops.repos.webhooks');
        Route::post('/devops/repos/unmap/{id}', [App\Http\Controllers\Admin\DevOpsController::class, 'unmapRepository'])->name('devops.repos.unmap');
        
        // Helpers
        Route::get('/devops/list/projects', [App\Http\Controllers\Admin\DevOpsController::class, 'listProjects'])->name('devops.list.projects');
        Route::get('/devops/list/modules', [App\Http\Controllers\Admin\DevOpsController::class, 'listSystemModules'])->name('devops.list.modules');

        // System Settings
        
        // System Settings
        Route::get('/settings', [App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [App\Http\Controllers\Admin\SystemSettingController::class, 'store'])->name('settings.store');

        // Compliance Rules (Phase 1)
        Route::get('/compliance/settings', [App\Http\Controllers\Admin\ComplianceSettingsController::class, 'index'])->name('compliance.settings.index');
        Route::post('/compliance/settings', [App\Http\Controllers\Admin\ComplianceSettingsController::class, 'update'])->name('compliance.settings.update');
        
        // Payroll / Salary
        Route::resource('salary-structures', App\Http\Controllers\Admin\SalaryStructureController::class);

        // Document Templates
        Route::get('document-templates/{document_template}/preview', [App\Http\Controllers\Admin\DocumentTemplateController::class, 'previewPdf'])->name('document-templates.preview-pdf');
        
        // Components Library
        Route::get('document-templates/components', [App\Http\Controllers\Admin\DocumentTemplateController::class, 'fetchComponents'])->name('document-templates.components.fetch'); // Fetch
        Route::post('document-templates/components', [App\Http\Controllers\Admin\DocumentTemplateController::class, 'saveComponent'])->name('document-templates.components.save'); // Save
        
        Route::resource('document-templates', App\Http\Controllers\Admin\DocumentTemplateController::class);

        // ... Talent Routes moved out ...
        
        // Attendance Policies (Resource & Logic moved to CRM block)
        Route::put('attendance/policies/update-fallback', [App\Http\Controllers\Admin\AttendancePolicyController::class, 'updateFallback'])->name('attendance.policies.update.fallback');
        // Route::get('attendance/policies', ...) REMOVED - duplicates line 1059
        Route::post('attendance/policies', [App\Http\Controllers\Admin\AttendancePolicyController::class, 'store'])->name('attendance.policies.store');
        Route::match(['put', 'patch'], 'attendance/policies/{policy}', [App\Http\Controllers\Admin\AttendancePolicyController::class, 'update'])->name('attendance.policies.update');
        Route::delete('attendance/policies/{policy}', [App\Http\Controllers\Admin\AttendancePolicyController::class, 'destroy'])->name('attendance.policies.destroy');

        
        // Specific Routes MUST come before Resources
        Route::get('/users/access-review', [App\Http\Controllers\Admin\UserController::class, 'accessReview'])->name('users.access-review');
        Route::get('/users/search', [App\Http\Controllers\Admin\UserController::class, 'search'])->name('users.search');
        Route::resource('users', App\Http\Controllers\Admin\UserController::class);
        
        Route::get('roles/matrix', [App\Http\Controllers\Admin\RoleController::class, 'matrix'])->name('roles.matrix');
        Route::post('/roles/{role}/toggle-permission', [App\Http\Controllers\Admin\RoleController::class, 'togglePermission'])->name('roles.toggle-permission');
        Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
        
        // Employees
        Route::get('/employees/export', [App\Http\Controllers\Admin\EmployeeController::class, 'export'])->name('employees.export');
        
        // Employee Reports
        Route::prefix('employees/reports')->name('employees.reports.')->group(function () {
             Route::get('/documents', [App\Http\Controllers\Admin\EmployeeReportController::class, 'documents'])->name('documents');
             Route::get('/family', [App\Http\Controllers\Admin\EmployeeReportController::class, 'family'])->name('family');
             Route::get('/history', [App\Http\Controllers\Admin\EmployeeReportController::class, 'history'])->name('history');
        });
        
        Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class);
        
        // Org
        Route::post('/employees/{employee}/personal', [App\Http\Controllers\Admin\EmployeeController::class, 'updatePersonal'])->name('employees.update-personal');
        Route::post('/employees/{employee}/health', [App\Http\Controllers\Admin\EmployeeController::class, 'updateHealth'])->name('employees.update-health');
        Route::post('/employees/{employee}/bank', [App\Http\Controllers\Admin\EmployeeController::class, 'updateBank'])->name('employees.update-bank');
        
        Route::post('/employees/{employee}/families', [App\Http\Controllers\Admin\EmployeeController::class, 'storeFamily'])->name('employees.families.store');
        Route::put('/employees/{employee}/families/{family}', [App\Http\Controllers\Admin\EmployeeController::class, 'updateFamily'])->name('employees.families.update');
        Route::delete('/employees/{employee}/families/{family}', [App\Http\Controllers\Admin\EmployeeController::class, 'destroyFamily'])->name('employees.families.destroy');

        Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class);
        Route::resource('locations', App\Http\Controllers\Admin\LocationController::class);

        // User Management
        // (Roles & Users defined above under 'Specific Routes MUST come before Resources')
        Route::get('access-review', [App\Http\Controllers\Admin\UserController::class, 'accessReview'])->name('access-review.index');
        
        // Settings & Configs
        Route::get('settings', [App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('settings.index');
        Route::get('modules', [App\Http\Controllers\Admin\ModuleManagerController::class, 'index'])->name('modules.index');
        Route::resource('tenants', App\Http\Controllers\Admin\TenantController::class);
        Route::resource('salary-structures', App\Http\Controllers\Admin\SalaryStructureController::class);
        Route::resource('vendors', App\Http\Controllers\Admin\VendorController::class);
        

        // Expense Settlement (Finance)
        Route::get('/expenses/settlement', [\App\Http\Controllers\Finance\ExpenseSettlementController::class, 'index'])->name('expenses.settlement');
        Route::post('/expenses/settlement/payroll', [\App\Http\Controllers\Finance\ExpenseSettlementController::class, 'markForPayroll'])->name('expenses.settlement.payroll');
        Route::post('/expenses/settlement/pay', [\App\Http\Controllers\Finance\ExpenseSettlementController::class, 'settleManually'])->name('expenses.settlement.pay');
        
        // Comprehensive Expense Dashboard (HR)
        // Moved to HR Group (Line 194)
        
        // Loan Configuration
        Route::get('loan-products/settings', [App\Http\Controllers\Admin\LoanProductController::class, 'getSettings'])->name('loan-products.settings');
        Route::post('loan-products/settings', [App\Http\Controllers\Admin\LoanProductController::class, 'updateSettings'])->name('loan-products.settings.update');
        Route::resource('loan-products', App\Http\Controllers\Admin\LoanProductController::class);


        
        // Workflow Analytics
        Route::get('workflows/analytics/dashboard', [App\Http\Controllers\Admin\WorkflowAnalyticsController::class, 'index'])->name('workflows.analytics.index');
        Route::get('workflows/analytics/data', [App\Http\Controllers\Admin\WorkflowAnalyticsController::class, 'stats'])->name('workflows.analytics.data');
        
        // Module Access Control & Licensing
        Route::prefix('modules')->name('modules.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ModuleManagerController::class, 'index'])->name('index');
            Route::post('/{module}/request-activation', [App\Http\Controllers\Admin\ModuleManagerController::class, 'requestActivation'])->name('request-activation');
            Route::post('/verify', [App\Http\Controllers\Admin\ModuleManagerController::class, 'verify'])->name('verify');
            Route::post('/{module}/dev-activate', [App\Http\Controllers\Admin\ModuleManagerController::class, 'devActivate'])->name('dev-activate');
            
            // User Access Management
            Route::get('/{module}/users', [App\Http\Controllers\Admin\ModuleManagerController::class, 'users'])->name('users');
            Route::post('/{module}/users/{user}/grant', [App\Http\Controllers\Admin\ModuleManagerController::class, 'grantAccess'])->name('grant-access');
            Route::delete('/{module}/users/{user}', [App\Http\Controllers\Admin\ModuleManagerController::class, 'revokeAccess'])->name('revoke-access');
            Route::put('/{module}/users/{user}/role', [App\Http\Controllers\Admin\ModuleManagerController::class, 'updateRole'])->name('update-role');
        });
        
        // CRM Module (Protected by Module Access Control)
        Route::middleware(['module:CRM'])->prefix('crm')->name('crm.')->group(function () {
            Route::get('/hub', [App\Http\Controllers\CRM\HubController::class, 'index'])->name('hub');
            
            // Leads
            Route::resource('leads', App\Http\Controllers\CRM\LeadController::class);
            Route::post('leads/{lead}/convert', [App\Http\Controllers\CRM\LeadController::class, 'convert'])->name('leads.convert');
            
            // Contacts
            Route::resource('contacts', App\Http\Controllers\CRM\ContactController::class);
            
            // Accounts
            Route::resource('accounts', App\Http\Controllers\CRM\AccountController::class);
            
            // Deals
            Route::resource('deals', App\Http\Controllers\CRM\DealController::class);
            
            // Activities
            Route::resource('activities', App\Http\Controllers\CRM\ActivityController::class);
            Route::post('activities/{activity}/complete', [App\Http\Controllers\CRM\ActivityController::class, 'complete'])->name('activities.complete');
        });
        
        // HR Intelligence (Phase 9)
        Route::get('/hr/pulse', [\App\Http\Controllers\HR\PulseController::class, 'index'])->name('hr.pulse.dashboard');

        // Finance / Ledger (Gap E)
        Route::get('/finance/ledger', [App\Http\Controllers\Admin\LedgerController::class, 'index'])->name('finance.ledger');

        // Attendance Admin (Data & Export)
        Route::get('/requests', [App\Http\Controllers\Admin\RequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/export', [App\Http\Controllers\Admin\RequestController::class, 'export'])->name('requests.export');
        
        // Physical Documents
        Route::get('/physical-documents/config', [App\Http\Controllers\Admin\PhysicalDocumentController::class, 'config'])->name('physical-documents.config');
        Route::post('/physical-documents/locations', [App\Http\Controllers\Admin\PhysicalDocumentController::class, 'storeLocation'])->name('physical-documents.locations.store');
        Route::delete('/physical-documents/locations/{location}', [App\Http\Controllers\Admin\PhysicalDocumentController::class, 'destroyLocation'])->name('physical-documents.locations.destroy');

        // Attendance Hub (Centralized)
        Route::prefix('attendance')->name('attendance.')->group(function() {
            // Main Hub Entry Points (Render the same Hub.vue with different 'activeTab' prop)
            Route::get('/monitoring', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'monitor_view')->name('monitoring');
            Route::get('/roster', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'roster')->name('roster');
            Route::get('/timesheets', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'timesheets')->name('timesheets');
            Route::get('/regularization', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'approvals')->name('regularization');
            Route::get('/holidays', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'holidays')->name('holidays');
            Route::get('/leave-types', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'leave_types')->name('leave-types');
            Route::get('/devices', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'biometric')->name('devices');
            Route::get('/manual', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'manual')->name('manual');
            
            Route::post('/manual', [\App\Http\Controllers\Admin\AttendanceController::class, 'storeManual'])->name('manual.store');
            Route::post('/manual/bulk', [\App\Http\Controllers\Admin\AttendanceController::class, 'bulkImport'])->name('manual.bulk');
            Route::post('/manual/bulk-mark', [\App\Http\Controllers\Admin\AttendanceController::class, 'storeBulkMark'])->name('manual.bulk-mark.store');

            // Intelligence Tabs
            Route::get('/policies', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'policies')->name('policies');
            Route::get('/attendance-policies', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'attendance_policies')->name('policies.index');
            Route::get('/workflows', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'workflows')->name('workflows');
            Route::get('/gamification', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'gamification')->name('gamification');
            Route::get('/teams', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'teams')->name('teams.index');
            Route::resource('teams', \App\Http\Controllers\Admin\TeamController::class)->except(['index'])->names('teams');
            
            // AI Logs & Analytics
            Route::get('/analytics', [App\Http\Controllers\Admin\AttendanceAnalyticsController::class, 'index'])->name('analytics');
            Route::get('/ai-logs', [App\Http\Controllers\Admin\AiLogController::class, 'index'])->name('ai-logs.index');

            // Shifts & Calendar
            Route::get('/shifts-list', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'index'])->defaults('tab', 'shift_config')->name('shifts.list');
            Route::get('/shifts/calendar', [App\Http\Controllers\Admin\ShiftController::class, 'calendar'])->name('shifts.calendar');

            // Legacy / Helper Route for dynamic switching
            Route::get('/hub', function (Illuminate\Http\Request $request) {
                return Inertia::render('Admin/Attendance/Hub', ['tab' => $request->tab ?? 'monitor_view']);
            })->name('hub');

            // --- DATA & ACTION ENDPOINTS ---
            Route::get('/monitoring/data', [App\Http\Controllers\Admin\MonitoringController::class, 'getData'])->name('monitoring.data');
            Route::get('/monitoring/stats', [App\Http\Controllers\Admin\MonitoringController::class, 'stats'])->name('monitoring.stats');
            Route::get('/roster/data', [\App\Http\Controllers\Admin\ShiftRosterController::class, 'index'])->name('roster.data');
            Route::post('/roster/assign', [\App\Http\Controllers\Admin\ShiftRosterController::class, 'assign'])->name('roster.assign');

            Route::post('/regularization/store', [App\Http\Controllers\Admin\RegularizationController::class, 'store'])->name('regularization.store');
            Route::put('/regularization/{id}', [App\Http\Controllers\Admin\RegularizationController::class, 'update'])->name('regularization.update');
            
            Route::resource('shifts', \App\Http\Controllers\Admin\ShiftController::class)->only(['index', 'store', 'update', 'destroy'])->names('shifts');
            Route::get('/shifts/export', [\App\Http\Controllers\Admin\ShiftController::class, 'export'])->name('shifts.export');
            
            Route::post('/policies/update', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'updateFallback']);
            Route::resource('policies', \App\Http\Controllers\Admin\AttendancePolicyController::class)->only(['store', 'update', 'destroy'])->names('policies_config');
            
            Route::get('/data', [App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('data');
            Route::get('/export', [App\Http\Controllers\Admin\AttendanceController::class, 'export'])->name('export');
            Route::get('/swaps', [App\Http\Controllers\Admin\ShiftSwapController::class, 'index'])->name('swaps');
             
            Route::resource('wfh', \App\Http\Controllers\Admin\WfhController::class)->only(['index', 'update', 'store'])->names('wfh');
            Route::resource('overtime', \App\Http\Controllers\Admin\OvertimeController::class)->only(['index', 'update', 'store'])->names('overtime');
            Route::get('/comp-offs', [App\Http\Controllers\Admin\CompOffController::class, 'index'])->name('compoffs.index');

            // Timesheets
            Route::get('/timesheets/data', [\App\Http\Controllers\Admin\TimesheetController::class, 'index'])->name('timesheets_data');
            Route::post('/timesheets', [\App\Http\Controllers\Admin\TimesheetController::class, 'store'])->name('timesheets.store');
            Route::put('/timesheets/{id}', [\App\Http\Controllers\Admin\TimesheetController::class, 'update'])->name('timesheets.update');
            Route::post('/timesheets/{timesheet}/approve', [\App\Http\Controllers\Admin\TimesheetController::class, 'approve'])->name('timesheets.approve');
            Route::get('/timesheets/export', [\App\Http\Controllers\Admin\TimesheetController::class, 'export'])->name('timesheets.export');
            Route::get('/timesheets/audit-logs', [App\Http\Controllers\Admin\AttendanceController::class, 'auditLogs'])->name('timesheets.audit');

            // Devices
            Route::get('/devices/data', [\App\Http\Controllers\Admin\DeviceController::class, 'index'])->name('devices.data');
            Route::post('/devices/{device}/ping', [\App\Http\Controllers\Admin\DeviceController::class, 'ping'])->name('devices.ping');
            Route::resource('devices', \App\Http\Controllers\Admin\DeviceController::class)
                 ->except(['index', 'create', 'edit', 'show'])
                 ->names('devices_resource');

            // Sub-module group definitions (Actions/Rules)
            Route::prefix('gamification')->name('gamification.')->group(function() {
                Route::put('/rules/{pointRule}', [App\Http\Controllers\Admin\GamificationController::class, 'updateRule'])->name('rules.update');
                Route::post('/badges', [App\Http\Controllers\Admin\GamificationController::class, 'storeBadge'])->name('badges.store');
                Route::delete('/badges/{badge}', [App\Http\Controllers\Admin\GamificationController::class, 'destroyBadge'])->name('badges.destroy');
                Route::put('/update-fallback', [App\Http\Controllers\Admin\GamificationController::class, 'updateFallback']);
            });

            Route::prefix('workflows')->name('workflows.')->group(function() {
                Route::post('/init', [App\Http\Controllers\Admin\WorkflowController::class, 'initDefaults'])->name('init');
                Route::post('/', [App\Http\Controllers\Admin\WorkflowController::class, 'store'])->name('store');
                Route::put('/{workflow}', [App\Http\Controllers\Admin\WorkflowController::class, 'update'])->name('update');
                Route::delete('/{workflow}', [App\Http\Controllers\Admin\WorkflowController::class, 'destroy'])->name('destroy');
                Route::post('/{workflow}/stages', [App\Http\Controllers\Admin\WorkflowController::class, 'addStage'])->name('stages.store');
                Route::put('/stages/{stage}', [App\Http\Controllers\Admin\WorkflowController::class, 'updateStage'])->name('stages.update');
                Route::delete('/stages/{stage}', [App\Http\Controllers\Admin\WorkflowController::class, 'removeStage'])->name('stages.destroy');
                Route::put('/{workflow}/reorder', [App\Http\Controllers\Admin\WorkflowController::class, 'reorderStages'])->name('reorder');
                Route::post('/{workflow}/clone', [App\Http\Controllers\Admin\WorkflowController::class, 'clone'])->name('clone');
            });
        });

        // Analytics
        Route::get('/analytics/visual', [\App\Http\Controllers\Admin\VisualAnalyticsController::class, 'index'])->name('analytics.visual');
        // Attendance Hub (Moved to top)
        


        // --- Assets Management (Smart Group) ---
        Route::prefix('assets')->name('assets.')->group(function () {
             // 1. Command Center (New Hub)
             Route::get('/hub', [App\Http\Controllers\Admin\AssetHubController::class, 'index'])->name('hub'); 
             Route::post('/hub/import', [App\Http\Controllers\Admin\AssetHubController::class, 'import'])->name('import'); 

             // 1. Dashboard & Stats (Static routes first)
             Route::get('/dashboard', [App\Http\Controllers\Admin\AssetController::class, 'dashboard'])->name('dashboard'); // Legacy
             
              // Configuration Hub (Meta-Brain)
              Route::get('/configurations', [App\Http\Controllers\Admin\ConfigHubController::class, 'index'])->name('configurations');
              Route::post('/configurations/attributes', [App\Http\Controllers\Admin\ConfigHubController::class, 'storeAttribute'])->name('configs.attributes.store');
              Route::put('/configurations/attributes/{attribute}', [App\Http\Controllers\Admin\ConfigHubController::class, 'updateAttribute'])->name('configs.attributes.update');
              Route::delete('/configurations/attributes/{attribute}', [App\Http\Controllers\Admin\ConfigHubController::class, 'destroyAttribute'])->name('configs.attributes.destroy');
              Route::post('/configurations/rules', [App\Http\Controllers\Admin\ConfigHubController::class, 'storeRule'])->name('configs.rules.store');
              Route::put('/configurations/rules/{rule}', [App\Http\Controllers\Admin\ConfigHubController::class, 'updateRule'])->name('configs.rules.update');
              Route::delete('/configurations/rules/{rule}', [App\Http\Controllers\Admin\ConfigHubController::class, 'destroyRule'])->name('configs.rules.destroy');
              Route::post('/configurations/settings', [App\Http\Controllers\Admin\ConfigHubController::class, 'saveSettings'])->name('configs.settings.save');

             // 2. Import Center
             Route::prefix('import')->name('import.')->group(function () {
                 Route::get('/smart', [App\Http\Controllers\Admin\SmartImportController::class, 'index'])->name('smart');
                 Route::post('/inspect', [App\Http\Controllers\Admin\SmartImportController::class, 'inspect'])->name('inspect');
                 Route::post('/process', [App\Http\Controllers\Admin\SmartImportController::class, 'process'])->name('process');
                 Route::get('/', [App\Http\Controllers\Admin\AssetImportController::class, 'create'])->name('legacy'); // Was assets.import
                 Route::post('/', [App\Http\Controllers\Admin\AssetImportController::class, 'store'])->name('store');
             });

             // 3. Configurations API
             Route::put('/categories/{category}/config', [App\Http\Controllers\Admin\AssetConfigurationController::class, 'updateCategory'])->name('categories.config.update');
             
             // 4. Asset Requests (Approvals)
             Route::post('/requests/{request}/approve', [App\Http\Controllers\Admin\AssetRequestController::class, 'approve'])->name('asset-requests.approve');
             Route::post('/requests/{request}/reject', [App\Http\Controllers\Admin\AssetRequestController::class, 'reject'])->name('asset-requests.reject');

             // 5. Actions on Assets (Static verbs on {asset})
             Route::get('/{asset}/label', [App\Http\Controllers\Admin\AssetController::class, 'printLabel'])->name('label');
             Route::post('/{asset}/return', [App\Http\Controllers\Admin\AssetController::class, 'return'])->name('return');
             Route::post('/{asset}/maintenance', [App\Http\Controllers\Admin\AssetController::class, 'storeMaintenance'])->name('maintenance.store');
             Route::post('/{asset}/assign', [App\Http\Controllers\Admin\AssetController::class, 'assign'])->name('assign');
             
             // 5. Actions on Assets (Static verbs on {asset})
             Route::get('/', [App\Http\Controllers\Admin\AssetController::class, 'index'])->name('index');
             Route::post('/', [App\Http\Controllers\Admin\AssetController::class, 'store'])->name('store');
             Route::get('/create', [App\Http\Controllers\Admin\AssetController::class, 'create'])->name('create');
             Route::get('/{asset}', [App\Http\Controllers\Admin\AssetController::class, 'show'])->name('show');
             Route::put('/{asset}', [App\Http\Controllers\Admin\AssetController::class, 'update'])->name('update');
             Route::put('/{asset}', [App\Http\Controllers\Admin\AssetController::class, 'update'])->name('update');
             Route::put('/{asset}', [App\Http\Controllers\Admin\AssetController::class, 'update'])->name('update');
             Route::delete('/{asset}', [App\Http\Controllers\Admin\AssetController::class, 'destroy'])->name('destroy');
             
             // Bulk Actions
             Route::get('/actions/bulk-assign', [App\Http\Controllers\Admin\AssetController::class, 'bulkAssign'])->name('bulk-assign');
             Route::post('/actions/bulk-assign', [App\Http\Controllers\Admin\AssetController::class, 'processBulkAssign'])->name('bulk-assign.process');
        });

        // Redirects/Aliases for Sidebar compatibility
        Route::get('/asset-requests', function() {
            return redirect()->route('admin.assets.dashboard', ['view' => 'requests']);
        });

        // Maintenance Hub (Gap A)
        Route::prefix('assets/maintenance')->name('assets.maintenance.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\MaintenanceBoardController::class, 'index'])->name('index');
            Route::post('/{id}/status', [App\Http\Controllers\Admin\MaintenanceBoardController::class, 'updateStatus'])->name('update-status');
        });

        // Audit Mode (Gap B + Phase 4 PDF)
        Route::prefix('assets/audit')->name('assets.audit.')->group(function () {
            Route::get('/run', [App\Http\Controllers\Admin\AuditController::class, 'index'])->name('run');
            Route::post('/fetch', [App\Http\Controllers\Admin\AuditController::class, 'fetchExpected'])->name('fetch');
            Route::post('/submit', [App\Http\Controllers\Admin\AuditController::class, 'submit'])->name('submit');
            Route::get('/history', [App\Http\Controllers\Admin\AuditController::class, 'history'])->name('history');
            Route::get('/report/{id}', [App\Http\Controllers\Admin\AuditController::class, 'downloadReport'])->name('report');
        });

        // --- Inventory / Store Management (Smart Group) ---
        Route::prefix('inventory')->name('inventory.')->group(function () {
             // 1. Dashboard & Tools
             Route::get('/dashboard', [App\Http\Controllers\Admin\InventoryController::class, 'dashboard'])->name('dashboard'); // Need to create Logic
             Route::get('/scanner', [App\Http\Controllers\Admin\InventoryController::class, 'scanner'])->name('scanner');
             Route::post('/scan', [App\Http\Controllers\Admin\InventoryController::class, 'processScan'])->name('scan');

             // 2. Actions
             Route::post('/{item}/add-stock', [App\Http\Controllers\Admin\InventoryController::class, 'addStock'])->name('add-stock');
             Route::post('/{item}/consume', [App\Http\Controllers\Admin\InventoryController::class, 'consume'])->name('consume');
             
             // 3. Procurement (Gap D)
             Route::get('/procurement/restock', [App\Http\Controllers\Admin\RestockController::class, 'index'])->name('procurement.restock');
             Route::post('/procurement/generate', [App\Http\Controllers\Admin\RestockController::class, 'createPO'])->name('procurement.generate');

             
             // 4. Resource
             Route::get('/', [App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('index');
             Route::post('/', [App\Http\Controllers\Admin\InventoryController::class, 'store'])->name('store');
             Route::get('/create', [App\Http\Controllers\Admin\InventoryController::class, 'create'])->name('create');
             Route::get('/{item}', [App\Http\Controllers\Admin\InventoryController::class, 'show'])->name('show');
             Route::put('/{item}', [App\Http\Controllers\Admin\InventoryController::class, 'update'])->name('update');
             Route::delete('/{item}', [App\Http\Controllers\Admin\InventoryController::class, 'destroy'])->name('destroy');
        });

        Route::get('physical-documents', [App\Http\Controllers\Admin\PhysicalDocumentController::class, 'index'])->name('physical-documents.index');
        Route::post('physical-documents/check-in', [App\Http\Controllers\Admin\PhysicalDocumentController::class, 'checkIn'])->name('physical-documents.check-in');

        // --- Visitor Management (Smart Hub) ---
        Route::prefix('visitors')->name('visitors.')->group(function () {
             Route::get('/', [\App\Http\Controllers\Admin\VisitorController::class, 'index'])->name('index');
             Route::post('/check-in', [\App\Http\Controllers\Admin\VisitorController::class, 'checkIn'])->name('check-in');
             Route::post('/invite', [\App\Http\Controllers\Admin\VisitorController::class, 'invite'])->name('invite');
             Route::get('/print-pass/{id}', [\App\Http\Controllers\Admin\VisitorController::class, 'printBadge'])->name('print');
             Route::get('/export', [\App\Http\Controllers\Admin\VisitorController::class, 'export'])->name('export');
             Route::post('/check-out/{id}', [\App\Http\Controllers\Admin\VisitorController::class, 'checkOut'])->name('check-out');
             Route::post('/events', [\App\Http\Controllers\Admin\VisitorController::class, 'storeEvent'])->name('events.store');
             
             // Settings / Config
             Route::get('/settings/form-builder', [\App\Http\Controllers\Admin\VisitorPurposeController::class, 'index'])->name('settings.form-builder');
             Route::put('/settings/purposes/{id}', [\App\Http\Controllers\Admin\VisitorPurposeController::class, 'update'])->name('settings.purposes.update');

             // Kits (Bundles)
             Route::post('kits/{kit}/assign', [App\Http\Controllers\Admin\KitController::class, 'assign'])->name('kits.assign');
             Route::resource('kits', App\Http\Controllers\Admin\KitController::class);
        });

        // Phase 3: Bulk Audits
        // Duplicate Timesheet routes removed. See below.
        

        // Leave Management Hub
        Route::get('/leave-management', function () {
            return Inertia::render('Admin/LeaveManagement/Index');
        })->name('leave.index');
        
        // Leave Administration (Data & Processing)
        Route::get('/leaves/approvals', [App\Http\Controllers\Admin\LeaveRequestController::class, 'approvals'])->name('leaves.approvals');
        Route::put('/leaves/{leaveRequest}/action', [App\Http\Controllers\Admin\LeaveRequestController::class, 'action'])->name('leaves.action');

        // Leave Types & Holidays Data API
        Route::resource('leave-types', App\Http\Controllers\Admin\LeaveTypeController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::prefix('holidays')->name('holidays.')->group(function() {
            Route::post('/{holiday}/toggle-visibility', [App\Http\Controllers\Admin\HolidayController::class, 'toggleVisibility'])->name('toggle-visibility');
            Route::post('/publish', [App\Http\Controllers\Admin\HolidayController::class, 'publish'])->name('publish');
            Route::post('/unpublish', [App\Http\Controllers\Admin\HolidayController::class, 'unpublish'])->name('unpublish');
            Route::get('/export', [App\Http\Controllers\Admin\HolidayController::class, 'export'])->name('export');
        });
        Route::resource('holidays', App\Http\Controllers\Admin\HolidayController::class);

        // Intelligence Action Endpoints (Moved from attendance group for clarity if needed, or kept here)
        // Note: Main hub entries for these are in the 'attendance' group above.
        
        Route::prefix('gamification')->name('gamification.')->group(function() {
            Route::put('/update-fallback', [App\Http\Controllers\Admin\GamificationController::class, 'updateFallback']);
        });

        Route::prefix('teams')->name('teams.')->group(function() {
            Route::put('/update-fallback', [App\Http\Controllers\Admin\TeamController::class, 'updateFallback']);
            Route::put('/{team}/members/bulk', [App\Http\Controllers\Admin\TeamController::class, 'bulkAddMembers'])->name('members.bulk');
            Route::delete('/{team}/members/{user}', [App\Http\Controllers\Admin\TeamController::class, 'removeMember'])->name('members.remove');
        });





        // Exit Management
        Route::get('/clearances', [\App\Http\Controllers\Exit\ExitClearanceController::class, 'index'])->name('clearances.index');
        Route::put('/clearances/{clearance}', [\App\Http\Controllers\Exit\ExitClearanceController::class, 'update'])->name('clearances.update');
        Route::post('/clearances/initiate', [\App\Http\Controllers\Exit\ExitClearanceController::class, 'store'])->name('clearances.store');

        // Global Documents
        Route::prefix('documents')->name('documents.')->group(function () {
             Route::get('/', [App\Http\Controllers\Admin\AdminDocumentController::class, 'index'])->name('index');
             Route::get('/custody', [App\Http\Controllers\Admin\CustodyController::class, 'index'])->name('custody');
        });

        // -----------------------------------------------------------------------------
        // Project Management (Scrum) Routes REMOVED
        // These are now handled by the Universal Project Routes at logic root.
        // -----------------------------------------------------------------------------
    });

    // Employee Routes
    Route::middleware(['auth'])->prefix('employee')->name('employee.')->group(function () {
        // Assets (ESS)
        Route::get('/my-assets', [\App\Http\Controllers\Admin\AssetController::class, 'myAssets'])->name('assets.index');
        Route::post('/my-assets/{asset}/accept', [\App\Http\Controllers\Admin\AssetController::class, 'acceptAsset'])->name('assets.accept');
        Route::post('/my-assets/{asset}/return', [\App\Http\Controllers\Admin\AssetController::class, 'requestReturn'])->name('assets.return');

        Route::get('/referrals', [\App\Http\Controllers\Employee\ReferralController::class, 'index'])->name('referrals.index');
        Route::get('/referrals/search', [\App\Http\Controllers\Employee\ReferralController::class, 'search'])->name('referrals.search');
        Route::post('/referrals', [\App\Http\Controllers\Employee\ReferralController::class, 'store'])->name('referrals.store');

        Route::get('/my-approvals', [\App\Http\Controllers\Employee\MyApprovalsController::class, 'index'])->name('my-approvals.index');
        Route::post('/my-approvals/bulk', [\App\Http\Controllers\Employee\MyApprovalsController::class, 'bulkAction'])->name('my-approvals.bulk');

        // Leave Requests (Employee)
        Route::post('/leaves', [\App\Http\Controllers\LeaveRequestController::class, 'store'])->name('leaves.store');
        Route::delete('/leaves/{leaveRequest}', [\App\Http\Controllers\LeaveRequestController::class, 'destroy'])->name('leaves.destroy');

        Route::get('/clearances', [\App\Http\Controllers\Exit\ExitClearanceController::class, 'myClearance'])->name('clearances.index');
        Route::get('/leaves', [App\Http\Controllers\Admin\LeaveRequestController::class, 'myLeaves'])->name('leave.index'); // used in MyLeaveDashboard
        Route::post('/leaves', [App\Http\Controllers\Admin\LeaveRequestController::class, 'store'])->name('leaves.store');
        // Note: The frontend uses 'leaves.update' and 'leaves.destroy' which are likely resource routes or shared.
        // However, standard resource is under 'admin'. We should alias or expose them for employee?
        // Actually, the LeaveRequestController::update/destroy check for ownership, so we can expose them generally or specifically.
        // Let's rely on the existing global/resource routes IF they are accessible to simple auth users, 
        // BUT they are inside the 'admin' prefix group in web.php (Line 379).
        // So Employees cannot access 'admin.leaves.*' routes if they don't have Admin/Manager role?
        // Line 379: Route::middleware(['role:Admin|Manager'])...
        // YES, BLOCKED. We must define Employee-accessible routes here.
        Route::put('/leaves/{leaveRequest}', [App\Http\Controllers\Admin\LeaveRequestController::class, 'update'])->name('leaves.update');
        Route::delete('/leaves/{leaveRequest}', [App\Http\Controllers\Admin\LeaveRequestController::class, 'destroy'])->name('leaves.destroy');
        
        // Regularization & Request Hub
        Route::get('/requests', [App\Http\Controllers\Employee\RequestController::class, 'index'])->name('requests.index');
        
        // Individual Store Routes (accessed by components)
        Route::post('/attendance/regularization', [App\Http\Controllers\Admin\RegularizationController::class, 'store'])->name('attendance.regularization.store');
        Route::post('/attendance/overtime', [App\Http\Controllers\Admin\OvertimeController::class, 'store'])->name('attendance.overtime.store');
        
        // We might need store routes for others too if they use Admin controllers or need new ones.
        // Assuming Admin controllers handle basic store logic with permission checks or we use separate ones.
        // For now Regularization is fixed.

        // Employee Expenses
        Route::resource('expenses', \App\Http\Controllers\Employee\ExpenseController::class)
            ->only(['index', 'store', 'show']);

        // Employee Loans
        Route::get('loans/status', [\App\Http\Controllers\Employee\LoanController::class, 'status'])->name('loans.status');
        Route::get('loans/products', [\App\Http\Controllers\Employee\LoanController::class, 'products'])->name('loans.products');
        Route::resource('loans', \App\Http\Controllers\Employee\LoanController::class)
            ->only(['index', 'create', 'store', 'show']);
        Route::post('loans/simulate', [\App\Http\Controllers\Employee\LoanController::class, 'simulate'])->name('loans.simulate');

        // Tax & Declarations
        Route::prefix('tax')->name('tax.')->group(function() {
            Route::get('/', [\App\Http\Controllers\Employee\TaxController::class, 'index'])->name('index');
            Route::post('regime', [\App\Http\Controllers\Employee\TaxController::class, 'updateRegime'])->name('regime');
            Route::post('hra', [\App\Http\Controllers\Employee\TaxController::class, 'storeHra'])->name('hra.store');
            Route::post('declarations', [\App\Http\Controllers\Employee\TaxController::class, 'storeDeclarations'])->name('declarations.store');
            Route::post('proofs', [\App\Http\Controllers\Employee\TaxController::class, 'uploadProof'])->name('proofs.store');
            Route::post('disputes', [\App\Http\Controllers\Employee\TaxController::class, 'raiseDispute'])->name('disputes.store');
            Route::delete('proofs/{proof}', [\App\Http\Controllers\Employee\TaxController::class, 'deleteProof'])->name('proofs.destroy');
            Route::get('documents/form12bb', [\App\Http\Controllers\HR\TaxDocumentController::class, 'downloadForm12BB'])->name('documents.form12bb');
            Route::get('documents/form16', [\App\Http\Controllers\HR\TaxDocumentController::class, 'downloadForm16'])->name('documents.form16');
        });
    });

    // Profile Routes
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->middleware('nocache');
Route::post('/impersonate', [App\Http\Controllers\Auth\AuthController::class, 'impersonate']);
Route::post('/forgot-password', [App\Http\Controllers\Auth\AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
Route::post('/verify-otp', [App\Http\Controllers\Auth\AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');
Route::post('/reset-password', [App\Http\Controllers\Auth\AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

// Public Storefront (Default)
// Public Storefront (Default) - This is now handled by the catch-all dynamic route below.

// Client Portal Routes (Dedicated Flow)
Route::prefix('client-portal')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\ClientAuthController::class, 'showLogin'])->name('client.login');
    Route::post('/login', [App\Http\Controllers\Auth\ClientAuthController::class, 'login'])->name('client.login.post');
    Route::post('/logout', [App\Http\Controllers\Auth\ClientAuthController::class, 'logout'])->name('client.logout');

    Route::middleware(['auth:client'])->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\ProjectManagement\ClientDashboardController::class, 'index'])->name('client.dashboard');
        Route::post('/bugs', [App\Http\Controllers\ProjectManagement\ClientDashboardController::class, 'store'])->name('client.bugs.store');
        Route::get('/bugs/{bug}', [App\Http\Controllers\ProjectManagement\ClientDashboardController::class, 'show'])->name('client.bugs.show');
        Route::post('/bugs/{bug}/verify', [App\Http\Controllers\ProjectManagement\ClientDashboardController::class, 'verifyTicket'])->name('client.bugs.verify');
        Route::post('/bugs/{bug}/comment', [App\Http\Controllers\ProjectManagement\ClientDashboardController::class, 'addComment'])->name('client.bugs.comment');
    });
});

Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login')->middleware('nocache');
    // Public Verification (No Auth)
    Route::get('/verify-card/{uuid}', [App\Http\Controllers\Admin\IdentityCardController::class, 'verify'])->name('identity.verify');

// Vendor Portal (Public with Token)
Route::get('/vendor/upload', [App\Http\Controllers\VendorPortalController::class, 'showUpload'])->name('vendor.upload');
Route::post('/vendor/upload', [App\Http\Controllers\VendorPortalController::class, 'processUpload'])->name('vendor.process');

// --- Host Portal (Approvals) ---
Route::middleware('auth')->prefix('host')->name('host.')->group(function () {
    Route::get('/approvals', [\App\Http\Controllers\Host\HostApprovalController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/{pass}/action', [\App\Http\Controllers\Host\HostApprovalController::class, 'action'])->name('approvals.action');
});

// --- Kiosk Mode (Tablet) ---
Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Kiosk\KioskController::class, 'standby'])->name('standby');
    Route::get('/check-in', [\App\Http\Controllers\Kiosk\KioskController::class, 'checkIn'])->name('check-in');
    Route::get('/walk-in', [\App\Http\Controllers\Kiosk\KioskController::class, 'walkIn'])->name('walk-in');
    Route::post('/walk-in', [\App\Http\Controllers\Kiosk\KioskController::class, 'storeWalkIn'])->name('store-walk-in');
    Route::get('/scan', [\App\Http\Controllers\Kiosk\KioskController::class, 'scan'])->name('scan');
    Route::post('/scan', [\App\Http\Controllers\Kiosk\KioskController::class, 'processScan'])->name('process-scan');
    Route::get('/success/{pass}', [\App\Http\Controllers\Kiosk\KioskController::class, 'success'])->name('success');
});

// --- Guest Portal (Public) ---
Route::get('/visitors/portal/{pass}', [\App\Http\Controllers\Guests\GuestPortalController::class, 'preCheckIn'])->name('visitors.guest.pre-checkin');
Route::post('/visitors/portal/{pass}', [\App\Http\Controllers\Guests\GuestPortalController::class, 'update'])->name('visitors.guest.update');

// ============================================
// CRM Routes
// ============================================
Route::middleware(['auth'])->prefix('admin/crm')->name('crm.')->group(function () {
    // Hub
    Route::get('/hub', [App\Http\Controllers\CRM\HubController::class, 'index'])->name('hub');

    // Leads
    Route::resource('leads', App\Http\Controllers\CRM\LeadController::class);
    Route::resource('lead-score-rules', App\Http\Controllers\CRM\LeadScoreRuleController::class)->only(['index', 'store', 'destroy']);
    Route::post('leads/bulk', [App\Http\Controllers\CRM\LeadController::class, 'bulkAction'])->name('leads.bulk');
    Route::post('leads/{lead}/convert', [App\Http\Controllers\CRM\LeadController::class, 'convert'])->name('leads.convert');
    
    // CRM Actions (Macros)
    Route::post('leads/{lead}/voice-call', [App\Http\Controllers\CRM\ActionController::class, 'voiceCall'])->name('leads.voice-call');
    Route::post('leads/{lead}/whatsapp', [App\Http\Controllers\CRM\ActionController::class, 'whatsappMessage'])->name('leads.whatsapp');
    Route::post('leads/{lead}/score-adjust', [App\Http\Controllers\CRM\ActionController::class, 'adjustScore'])->name('leads.score-adjust');
    Route::post('leads/{lead}/nurture', [App\Http\Controllers\CRM\ActionController::class, 'enrollNurture'])->name('leads.nurture');

    // Contacts
    Route::resource('contacts', App\Http\Controllers\CRM\ContactController::class);

    // Accounts
    Route::resource('accounts', App\Http\Controllers\CRM\AccountController::class);

    // Contact Imports
    Route::get('contacts/imports', [App\Http\Controllers\CRM\ContactImportController::class, 'index'])->name('contacts.imports.index');
    Route::get('contacts/imports/sample', [App\Http\Controllers\CRM\ContactImportController::class, 'downloadSample'])->name('contacts.imports.sample');
    Route::post('contacts/imports', [App\Http\Controllers\CRM\ContactImportController::class, 'store'])->name('contacts.imports.store');

    // Segments
    Route::resource('segments', App\Http\Controllers\CRM\ContactSegmentController::class);

    // Deals
    Route::resource('deals', App\Http\Controllers\CRM\DealController::class);
    Route::patch('deals/{deal}/stage', [App\Http\Controllers\CRM\DealController::class, 'updateStage'])->name('deals.stage');
    Route::post('deals/{deal}/win', [App\Http\Controllers\CRM\DealController::class, 'markAsWon'])->name('deals.win');
    Route::post('deals/{deal}/lose', [App\Http\Controllers\CRM\DealController::class, 'markAsLost'])->name('deals.lose');

    // Quotes
    Route::resource('quotes', App\Http\Controllers\CRM\QuoteController::class);

    // Forecasting
    Route::get('sales/forecast', [App\Http\Controllers\CRM\SalesForecastController::class, 'index'])->name('sales.forecast');

    // Activities
    Route::resource('activities', App\Http\Controllers\CRM\ActivityController::class)->only(['store', 'destroy']);
    Route::post('activities/{activity}/complete', [App\Http\Controllers\CRM\ActivityController::class, 'complete'])->name('activities.complete');

    // Dedicated CRM Workflows
    Route::prefix('workflows')->name('workflows.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CRM\CRMWorkflowController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\CRM\CRMWorkflowController::class, 'store'])->name('store');
        Route::put('/{workflow}', [App\Http\Controllers\Admin\CRM\CRMWorkflowController::class, 'update'])->name('update');
        Route::delete('/{workflow}', [App\Http\Controllers\Admin\CRM\CRMWorkflowController::class, 'destroy'])->name('destroy');
        Route::post('/{workflow}/stages', [App\Http\Controllers\Admin\CRM\CRMWorkflowController::class, 'addStage'])->name('stages.store');
        Route::put('/stages/{stage}', [App\Http\Controllers\Admin\CRM\CRMWorkflowController::class, 'updateStage'])->name('stages.update');
        Route::delete('/stages/{stage}', [App\Http\Controllers\Admin\CRM\CRMWorkflowController::class, 'removeStage'])->name('stages.destroy');
        Route::put('/{workflow}/reorder', [App\Http\Controllers\Admin\CRM\CRMWorkflowController::class, 'reorderStages'])->name('reorder');
    });

    // Marketing Automation (Legacy/Marketing specific)
    Route::get('marketing/automations', [App\Http\Controllers\CRM\MarketingAutomationController::class, 'index'])->name('marketing.automations.index');
    Route::post('marketing/automations', [App\Http\Controllers\CRM\MarketingAutomationController::class, 'store'])->name('marketing.automations.store');
    Route::post('marketing/automations/{automation}/steps', [App\Http\Controllers\CRM\MarketingAutomationController::class, 'addStep'])->name('marketing.automations.steps.store');
    Route::post('marketing/automations/{automation}/toggle', [App\Http\Controllers\CRM\MarketingAutomationController::class, 'toggle'])->name('marketing.automations.toggle');
    Route::delete('marketing/automations/{automation}', [App\Http\Controllers\CRM\MarketingAutomationController::class, 'destroy'])->name('marketing.automations.destroy');

    // Marketing
    Route::resource('marketing/templates', App\Http\Controllers\CRM\MarketingTemplateController::class)
        ->names('marketing.templates')
        ->parameters(['templates' => 'marketingTemplate']);

    Route::resource('marketing/campaigns', App\Http\Controllers\CRM\MarketingCampaignController::class)
        ->names('marketing.campaigns')
        ->parameters(['campaigns' => 'marketingCampaign']);

    Route::post('marketing/campaigns/{marketingCampaign}/send', [App\Http\Controllers\CRM\MarketingCampaignController::class, 'send'])->name('marketing.campaigns.send');

    // Customer Support (Tickets)
    Route::get('tickets/sla-stats', [App\Http\Controllers\CRM\TicketController::class, 'slaStats'])->name('tickets.sla-stats');
    Route::resource('tickets', App\Http\Controllers\CRM\TicketController::class);
    Route::post('tickets/{ticket}/messages', [App\Http\Controllers\CRM\TicketController::class, 'addMessage'])->name('tickets.messages.store');

    // Knowledge Base
    Route::get('kb/categories', [App\Http\Controllers\CRM\KnowledgeBaseController::class, 'categories'])->name('kb.categories');
    Route::post('kb/categories', [App\Http\Controllers\CRM\KnowledgeBaseController::class, 'storeCategory'])->name('kb.categories.store');
    Route::get('kb/articles', [App\Http\Controllers\CRM\KnowledgeBaseController::class, 'articles'])->name('kb.articles');
    Route::post('kb/articles', [App\Http\Controllers\CRM\KnowledgeBaseController::class, 'storeArticle'])->name('kb.articles.store');
    Route::get('kb/articles/{article}', [App\Http\Controllers\CRM\KnowledgeBaseController::class, 'showArticle'])->name('kb.articles.show');
    Route::get('kb/categories', [App\Http\Controllers\CRM\KnowledgeBaseController::class, 'categories'])->name('kb.categories');
    Route::post('kb/categories', [App\Http\Controllers\CRM\KnowledgeBaseController::class, 'storeCategory'])->name('kb.categories.store');
    Route::get('kb/articles', [App\Http\Controllers\CRM\KnowledgeBaseController::class, 'articles'])->name('kb.articles');
    Route::post('kb/articles', [App\Http\Controllers\CRM\KnowledgeBaseController::class, 'storeArticle'])->name('kb.articles.store');
    Route::get('kb/articles/{article}', [App\Http\Controllers\CRM\KnowledgeBaseController::class, 'showArticle'])->name('kb.articles.show');

    // Configuration Routes
    Route::get('products/tree', [App\Http\Controllers\CRM\ProductController::class, 'tree'])->name('products.tree');
    Route::post('products/import', [App\Http\Controllers\CRM\ProductController::class, 'import'])->name('products.import');
    Route::resource('products', App\Http\Controllers\CRM\ProductController::class);
    // Communication & Meetings Hub
    Route::get('/communication-hub', [\App\Http\Controllers\CRM\CommunicationHubController::class, 'index'])->name('comms.hub');
    Route::post('/communication-hub/transfer', [\App\Http\Controllers\CRM\CommunicationHubController::class, 'transfer'])->name('comms.hub.transfer');
    Route::post('/communication-hub/sync', [\App\Http\Controllers\CRM\CommunicationHubController::class, 'sync'])->name('comms.hub.sync');
    Route::post('/communication-hub/reply', [\App\Http\Controllers\CRM\CommunicationHubController::class, 'reply'])->name('comms.hub.reply');
    Route::get('/communication-hub/threads/{thread}', [\App\Http\Controllers\CRM\CommunicationHubController::class, 'showThread'])->name('comms.hub.thread');
    Route::post('/webhooks/resend', [\App\Http\Controllers\CRM\EmailTrackingController::class, 'handleWebhook'])->name('webhooks.resend');

    
    // Email Auth & Sync
    Route::get('/comms/auth/{provider}', [\App\Http\Controllers\CRM\EmailAuthController::class, 'redirect'])->name('comms.auth.redirect');
    Route::get('/comms/auth/{provider}/callback', [\App\Http\Controllers\CRM\EmailAuthController::class, 'callback'])->name('comms.auth.callback');
    Route::post('/comms/settings/imap', [\App\Http\Controllers\CRM\EmailAuthController::class, 'storeImap'])->name('comms.settings.imap');
    Route::post('/comms/settings/accounts/{account}/toggle', [\App\Http\Controllers\CRM\EmailAuthController::class, 'toggleActive'])->name('comms.settings.accounts.toggle');
    Route::post('/comms/settings/accounts/{account}/assign', [\App\Http\Controllers\CRM\EmailAuthController::class, 'assignUsers'])->name('comms.settings.accounts.assign');
    Route::delete('/comms/settings/accounts/{account}', [\App\Http\Controllers\CRM\EmailAuthController::class, 'unlink'])->name('comms.settings.accounts.unlink');

    // Automation & Journey Management
    Route::apiResource('campaign-journeys', App\Http\Controllers\CRM\CampaignJourneyController::class);
    Route::apiResource('campaign-steps', App\Http\Controllers\CRM\CampaignStepController::class);
    Route::apiResource('automation-rules', App\Http\Controllers\CRM\AutomationRuleController::class);

    // Communication & Timeline Hub (Primary CRM)
    Route::get('emails', [App\Http\Controllers\CRM\EmailController::class, 'index'])->name('emails.index');
    Route::get('emails/sync', [App\Http\Controllers\CRM\EmailController::class, 'sync'])->name('emails.sync');
    Route::post('emails/manual', [App\Http\Controllers\CRM\EmailController::class, 'storeManual'])->name('emails.store-manual');
    Route::get('emails/{thread}', [App\Http\Controllers\CRM\EmailController::class, 'show'])->name('emails.show');
    Route::post('emails', [App\Http\Controllers\CRM\EmailController::class, 'store'])->name('emails.store');
    
    Route::get('timeline', [App\Http\Controllers\CRM\TimelineController::class, 'show'])->name('timeline.show');
    Route::post('leads/{lead}/transfer', [App\Http\Controllers\CRM\LeadController::class, 'transfer'])->name('leads.transfer');
    Route::post('contacts/{contact}/transfer', [App\Http\Controllers\CRM\ContactController::class, 'transfer'])->name('contacts.transfer');

    Route::resource('meetings', App\Http\Controllers\CRM\MeetingController::class);
    Route::get('/meetings-hub', [App\Http\Controllers\CRM\MeetingController::class, 'hub'])->name('meetings.hub');
    Route::post('meetings/{meeting}/no-show', [App\Http\Controllers\CRM\MeetingController::class, 'noShow'])->name('meetings.no-show');
    Route::post('meetings/{meeting}/reschedule', [App\Http\Controllers\CRM\MeetingController::class, 'reschedule'])->name('meetings.reschedule');

    Route::resource('pipeline-stages', App\Http\Controllers\CRM\PipelineStageController::class);
    Route::post('pipeline-stages/reorder', [App\Http\Controllers\CRM\PipelineStageController::class, 'reorder'])->name('pipeline-stages.reorder');
    Route::resource('custom-fields', App\Http\Controllers\CRM\CustomFieldController::class);
    
    Route::get('settings', [App\Http\Controllers\CRM\HubController::class, 'settings'])->name('settings.index');
    Route::post('settings', [App\Http\Controllers\CRM\HubController::class, 'updateSettings'])->name('settings.update');
    Route::post('roles/{role}/toggle-permission', [App\Http\Controllers\CRM\HubController::class, 'togglePermission'])->name('roles.toggle-permission');
    Route::post('roles/{role}/master-access', [App\Http\Controllers\CRM\HubController::class, 'grantMasterAccess'])->name('roles.master-access');
    Route::post('users/{user}/assign-role', [App\Http\Controllers\CRM\HubController::class, 'assignRole'])->name('users.assign-role');
    Route::get('reports/export', [App\Http\Controllers\CRM\HubController::class, 'exportCsv'])->name('reports.export');
});

// CMS (KNR Office Engine) Routes
Route::middleware(['auth', 'verified'])->prefix('cms')->name('cms.')->group(function () {
    Route::get('/hub', [\App\Http\Controllers\CMS\HubController::class, 'index'])->name('hub');

    // ── Sites ──
    Route::apiResource('sites', \App\Http\Controllers\CMS\SiteController::class);
    Route::post('sites/{site}/toggle-mode',    [\App\Http\Controllers\CMS\SiteController::class, 'toggleMode'])->name('sites.toggle-mode');
    Route::post('sites/{site}/publish',        [\App\Http\Controllers\CMS\SiteController::class, 'publish'])->name('sites.publish');
    Route::post('sites/{site}/settings',       [\App\Http\Controllers\CMS\SiteController::class, 'saveSettings'])->name('sites.settings');

    // ── Pages ──
    Route::apiResource('pages', \App\Http\Controllers\CMS\PageController::class);
    Route::post('pages/{page}/clone',          [\App\Http\Controllers\CMS\PageController::class, 'clone'])->name('pages.clone');
    Route::post('pages/{page}/save-blocks',    [\App\Http\Controllers\CMS\PageController::class, 'saveBlocks'])->name('pages.save-blocks');
    Route::post('pages/{page}/publish',        [\App\Http\Controllers\CMS\PageController::class, 'publish'])->name('pages.publish');
    Route::post('pages/{page}/seo',            [\App\Http\Controllers\CMS\PageController::class, 'saveSeo'])->name('pages.seo');

    // ── Page Version Restore ──
    Route::get('pages/{page}/versions',            [\App\Http\Controllers\CMS\PageController::class, 'versions'])->name('pages.versions');
    Route::post('page-versions/{version}/restore', [\App\Http\Controllers\CMS\PageController::class, 'restoreVersion'])->name('pages.versions.restore');

    // ── Themes ──
    Route::apiResource('themes', \App\Http\Controllers\CMS\ThemeController::class);
    Route::post('themes/{theme}/activate',     [\App\Http\Controllers\CMS\ThemeController::class, 'activate'])->name('themes.activate');
    Route::post('themes/{theme}/compile',      [\App\Http\Controllers\CMS\ThemeController::class, 'compile'])->name('themes.compile');

    // ── Media ──
    Route::apiResource('media', \App\Http\Controllers\CMS\MediaController::class);
    Route::post('media/bulk-delete',           [\App\Http\Controllers\CMS\MediaController::class, 'bulkDelete'])->name('media.bulk-delete');

    // ── Products (Ecommerce) ──
    Route::apiResource('products',             \App\Http\Controllers\CMS\ProductController::class);
    Route::post('products/{product}/toggle',   [\App\Http\Controllers\CMS\ProductController::class, 'toggle'])->name('products.toggle');
    Route::post('products/{product}/sync-crm', [\App\Http\Controllers\CMS\ProductController::class, 'syncCrm'])->name('products.sync-crm');
    Route::post('products/bulk-action',        [\App\Http\Controllers\CMS\ProductController::class, 'bulkAction'])->name('products.bulk-action');
    Route::post('products/import',             [\App\Http\Controllers\CMS\ProductController::class, 'import'])->name('products.import');

    Route::apiResource('product-categories',   \App\Http\Controllers\CMS\ProductCategoryController::class);

    // ── Orders ──
    Route::apiResource('orders', \App\Http\Controllers\CMS\OrderController::class)->only(['index','show','update','destroy']);
    Route::post('orders/{order}/status',       [\App\Http\Controllers\CMS\OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('orders/{order}/refund',        [\App\Http\Controllers\CMS\OrderController::class, 'refund'])->name('orders.refund');
    Route::get('orders/{order}/invoice',        [\App\Http\Controllers\CMS\OrderController::class, 'invoice'])->name('orders.invoice');

    // ── Razorpay (checkout flow) ──
    Route::post('checkout/create-order',        [\App\Http\Controllers\CMS\CheckoutController::class, 'createOrder'])->name('checkout.create-order');
    Route::post('checkout/verify-payment',      [\App\Http\Controllers\CMS\CheckoutController::class, 'verifyPayment'])->name('checkout.verify-payment');
    Route::post('checkout/webhook',             [\App\Http\Controllers\CMS\CheckoutController::class, 'webhook'])->name('checkout.webhook')->withoutMiddleware(['auth', 'verified']);

    // ── Coupons ──
    Route::apiResource('coupons', \App\Http\Controllers\CMS\CouponController::class);
    Route::post('coupons/validate',             [\App\Http\Controllers\CMS\CouponController::class, 'validateCode'])->name('coupons.validate');

    // ── Forms ──
    Route::apiResource('forms', \App\Http\Controllers\CMS\FormController::class);
    Route::get('forms/{form}/submissions',      [\App\Http\Controllers\CMS\FormController::class, 'submissions'])->name('forms.submissions');
    Route::delete('forms/submissions/{id}',     [\App\Http\Controllers\CMS\FormController::class, 'destroySubmission'])->name('forms.submissions.destroy');
    Route::post('forms/{form}/submit',          [\App\Http\Controllers\CMS\FormController::class, 'submit'])->name('forms.submit')->withoutMiddleware(['auth','verified']);

    // ── SEO ──
    Route::post('seo/audit',                    [\App\Http\Controllers\CMS\SeoController::class, 'audit'])->name('seo.audit');
    Route::post('seo/generate-meta',            [\App\Http\Controllers\CMS\SeoController::class, 'generateMeta'])->name('seo.generate-meta');
    Route::post('seo/sitemap',                  [\App\Http\Controllers\CMS\SeoController::class, 'generateSitemap'])->name('seo.sitemap');

    // ── Analytics ──
    Route::get('analytics/overview',            [\App\Http\Controllers\CMS\AnalyticsController::class, 'overview'])->name('analytics.overview');
    Route::post('analytics/track',              [\App\Http\Controllers\CMS\AnalyticsController::class, 'track'])->name('analytics.track')->withoutMiddleware(['auth','verified']);

    // ── Version Pruning ──
    Route::post('versions/prune',               [\App\Http\Controllers\CMS\PageController::class, 'pruneVersions'])->name('pages.versions.prune');

    // ── AB Testing ──
    Route::get('ab-tests',                      [\App\Http\Controllers\CMS\AbTestController::class, 'index'])->name('ab-tests.index');
    Route::post('ab-tests',                     [\App\Http\Controllers\CMS\AbTestController::class, 'store'])->name('ab-tests.store');
    Route::post('ab-tests/{abTest}/toggle',     [\App\Http\Controllers\CMS\AbTestController::class, 'toggle'])->name('ab-tests.toggle');
    Route::post('ab-tests/{abTest}/winner',     [\App\Http\Controllers\CMS\AbTestController::class, 'declareWinner'])->name('ab-tests.winner');
 
    // ── Segments ──
    Route::apiResource('segments', \App\Http\Controllers\CMS\SegmentController::class);

    // ── Settings ──
    Route::post('settings',                     [\App\Http\Controllers\CMS\SettingsController::class, 'save'])->name('settings.save');
    Route::post('settings/payment',             [\App\Http\Controllers\CMS\SettingsController::class, 'savePayment'])->name('settings.payment');
    Route::post('settings/pwa',                 [\App\Http\Controllers\CMS\SettingsController::class, 'savePwa'])->name('settings.pwa');
    Route::post('settings/cart',                [\App\Http\Controllers\CMS\SettingsController::class, 'saveCart'])->name('settings.cart');
});

// ── Public Storefront (no auth) ──
Route::middleware(['web'])->prefix('store/{siteId}')->name('store.')->group(function () {
    Route::get  ('cart',             [\App\Http\Controllers\CMS\CartController::class, 'index'])->name('cart.index');
    Route::post ('cart/add',         [\App\Http\Controllers\CMS\CartController::class, 'add'])->name('cart.add');
    Route::put  ('cart/{key}',       [\App\Http\Controllers\CMS\CartController::class, 'update'])->name('cart.update');
    Route::delete('cart/{key}',      [\App\Http\Controllers\CMS\CartController::class, 'remove'])->name('cart.remove');
    Route::post ('cart/coupon',      [\App\Http\Controllers\CMS\CartController::class, 'applyCoupon'])->name('cart.coupon');
});

// Newsletter subscribe (public, no auth)
Route::post('cms/newsletter/subscribe', function (\Illuminate\Http\Request $request) {
    $email  = $request->validate(['email' => 'required|email'])['email'];
    $siteId = $request->input('site_id');
    // Store in cms_cart_sessions notes or a simple table — for now just log
    \Log::info("Newsletter subscribe: {$email} for site {$siteId}");
    return response()->json(['subscribed' => true]);
})->middleware('web');

// Coupon validate (public)
Route::post('cms/coupons/validate', [\App\Http\Controllers\CMS\CouponController::class, 'validateCode'])->middleware('web');

// Admin Vault Management
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/vault', [App\Http\Controllers\Admin\VaultController::class, 'index'])->name('vault.index');
    Route::post('/vault/categories', [App\Http\Controllers\Admin\VaultController::class, 'storeCategory'])->name('vault.categories.store');
    Route::put('/vault/categories/{category}', [App\Http\Controllers\Admin\VaultController::class, 'updateCategory'])->name('vault.categories.update');
    Route::delete('/vault/categories/{category}', [App\Http\Controllers\Admin\VaultController::class, 'destroyCategory'])->name('vault.categories.destroy');
    Route::post('/vault/articles', [App\Http\Controllers\Admin\VaultController::class, 'storeArticle'])->name('vault.articles.store');
    Route::put('/vault/articles/{article}', [App\Http\Controllers\Admin\VaultController::class, 'updateArticle'])->name('vault.articles.update');
    Route::delete('/vault/articles/{article}', [App\Http\Controllers\Admin\VaultController::class, 'destroyArticle'])->name('vault.articles.destroy');
});

// Tracking Routes (Publicly accessible)
Route::get('marketing/open/{id}', [App\Http\Controllers\CRM\TrackingController::class, 'trackOpen'])->name('marketing.track.open');
Route::get('marketing/click/{id}', [App\Http\Controllers\CRM\TrackingController::class, 'trackClick'])->name('marketing.track.click');



// ============================================================================
// ADVANCED DEEP LMS - PUBLIC ROUTES (No Auth)
// ============================================================================
Route::prefix('lms')->name('lms.store.')->group(function () {
    Route::get('/catalog', [\App\Http\Controllers\LMS\LmsStorefrontController::class, 'catalog'])->name('catalog');
    Route::get('/course/{course:slug}', [\App\Http\Controllers\LMS\LmsStorefrontController::class, 'courseShow'])->name('course.show');
    Route::get('/pricing', [\App\Http\Controllers\LMS\LmsStorefrontController::class, 'pricing'])->name('pricing');
    
    // Student Authentication
    Route::get('/login', [\App\Http\Controllers\LMS\LmsAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\LMS\LmsAuthController::class, 'login'])->name('login.post');
    Route::get('/register', [\App\Http\Controllers\LMS\LmsAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [\App\Http\Controllers\LMS\LmsAuthController::class, 'register'])->name('register.post');
    Route::get('/forgot-password', [\App\Http\Controllers\LMS\LmsAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\LMS\LmsAuthController::class, 'sendResetLink'])->name('password.email');
    Route::post('/logout', [\App\Http\Controllers\LMS\LmsAuthController::class, 'logout'])->name('logout');
    Route::get('/profile/{user}', [\App\Http\Controllers\LMS\LearnerController::class, 'publicProfile'])->name('profile');
});

Route::get('/lms/certificate/verify/{code}', [\App\Http\Controllers\LMS\LearnerController::class, 'verifyCertificate'])->name('lms.verify.certificate');

// ═══════════════════════════════════════════════════════
// PUBLIC CMS SITE ROUTES  (no auth — served to visitors)
// Must come AFTER all authenticated /cms/* routes.
// ═══════════════════════════════════════════════════════

// System / SEO assets (no rate limit needed on these few routes)
Route::get('/manifest.json',   [\App\Http\Controllers\CMS\PublicSiteController::class, 'manifest'])->name('psp.manifest');
Route::get('/sitemap.xml',     [\App\Http\Controllers\CMS\PublicSiteController::class, 'sitemap'])->name('psp.sitemap');
Route::get('/robots.txt',      [\App\Http\Controllers\CMS\PublicSiteController::class, 'robots'])->name('psp.robots');

// MEETING HUB PUBLIC ROUTES
Route::get('/m/{uuid}', [App\Http\Controllers\CRM\PublicMeetingController::class, 'show'])->name('crm.meetings.public.show');
Route::post('/m/{uuid}/rsvp', [App\Http\Controllers\CRM\PublicMeetingController::class, 'rsvp'])->name('crm.meetings.public.rsvp');

// --- Routes handled by Catch-all at bottom ---
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('psp.home');
Route::get('/{slug}', [\App\Http\Controllers\CMS\PublicSiteController::class, 'serve'])
    ->where('slug', '.*')
    ->name('psp.dynamic');
