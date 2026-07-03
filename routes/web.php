<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| All HTTP routes for the HR Dashboard are defined here.
| Add new module imports and resource routes as modules are completed.
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KeywordSetController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\StaffProfileController;   // Module 1: Staff Profiles
use App\Http\Controllers\Admin\LeaveRequestController;   // Module 2: Annual Leave
// KpiController intentionally omitted — module files not yet on this branch
use App\Http\Controllers\Admin\ComplianceController;     // Module 4: Compliance
use App\Http\Controllers\Admin\AppraisalController;      // Module 5: Probation & Appraisals
use App\Http\Controllers\Admin\AttendanceController;     // Module 6: Absenteeism Tracking
use App\Http\Controllers\Admin\ExitReportController;     // Module 7: Exit Reports
use App\Http\Controllers\ApplicationSubmissionController;
use App\Http\Controllers\Admin\AppraisalScheduleController;
use App\Http\Controllers\StaffPortalController;        // Staff self-service portal (My 
// Leave / My Appraisals)
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// -----------------------------------------------------------------------
// PUBLIC ROUTES — No login required
// -----------------------------------------------------------------------
Route::get('/', [ApplicationSubmissionController::class, 'index'])->name('application.form');
Route::post('/application', [ApplicationSubmissionController::class, 'store'])->name('application.store');
Route::get('/application/success/{application}', [ApplicationSubmissionController::class, 'success'])->name('application.success');
Route::get('/application/status/{application}', [ApplicationSubmissionController::class, 'status'])->name('application.status');

require __DIR__.'/auth.php';

// -----------------------------------------------------------------------
// STAFF PORTAL ROUTES — Any logged in user. Non-HR/admin staff land here
// and can ONLY ever see/act on their own Leave and Appraisal records.
// (HR/admin can also open these, but they normally use /admin instead.)
// -----------------------------------------------------------------------
Route::middleware(['auth'])->prefix('my')->name('my.')->group(function () {
    Route::get('/', [StaffPortalController::class, 'dashboard'])->name('dashboard');

    Route::get('/leave', [StaffPortalController::class, 'leaveIndex'])->name('leave.index');
    Route::get('/leave/create', [StaffPortalController::class, 'leaveCreate'])->name('leave.create');
    Route::post('/leave', [StaffPortalController::class, 'leaveStore'])->name('leave.store');
    Route::get('/leave/{leave}', [StaffPortalController::class, 'leaveShow'])->name('leave.show');

    Route::get('/appraisals', [StaffPortalController::class, 'appraisalIndex'])->name('appraisals.index');
    Route::get('/appraisals/{appraisal}', [StaffPortalController::class, 'appraisalShow'])->name('appraisals.show');
    Route::get('/appraisals/{appraisal}/fill', [StaffPortalController::class, 'appraisalFill'])->name('appraisals.fill');
    Route::post('/appraisals/{appraisal}/save', [StaffPortalController::class, 'appraisalSave'])->name('appraisals.save');
});

// -----------------------------------------------------------------------
// ADMIN ROUTES — Requires login + admin/HR role
// -----------------------------------------------------------------------
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Job Positions
    Route::resource('keyword-sets', KeywordSetController::class);
    Route::patch('/keyword-sets/{keywordSet}/toggle-status', [KeywordSetController::class, 'toggleStatus'])
        ->name('keyword-sets.toggle-status');

    // Module 1: Staff Profiles
    Route::resource('staff', StaffProfileController::class);

    // Module 2: Annual Leave Tracking
    Route::resource('leave', LeaveRequestController::class);
    Route::post('/leave/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leave.approve');
    Route::post('/leave/{leave}/reject',  [LeaveRequestController::class, 'reject'])->name('leave.reject');

    // Module 3: KPI Tracking — route disabled until KpiController & Kpi model are added to branch
    // Route::resource('kpis', KpiController::class);

    // Module 4: Compliance Tracking
    Route::resource('compliance', ComplianceController::class);

    // Module 5: Probation & Appraisals
    // Route::resource('appraisals', AppraisalController::class);

    // Module 5: Probation & Appraisals
    Route::resource('appraisals', AppraisalController::class);
    Route::post('/appraisals/{appraisal}/send', [AppraisalController::class, 'sendForm'])->name('appraisals.send');

    // Appraisal Schedule (yearly timetable)
    Route::get('/appraisal-schedule', [AppraisalScheduleController::class, 'index'])->name('appraisal-schedule.index');
    Route::post('/appraisal-schedule', [AppraisalScheduleController::class, 'store'])->name('appraisal-schedule.store');
    Route::delete('/appraisal-schedule/{appraisalSchedule}', [AppraisalScheduleController::class, 'destroy'])->name('appraisal-schedule.destroy');

    // Module 6: Absenteeism Tracking
    Route::resource('attendance', AttendanceController::class);
    Route::get('/attendance/staff/{staff}', [AttendanceController::class, 'staffReport'])
        ->name('attendance.staff-report');

    // Module 7: Exit Reports
    Route::resource('exit-reports', ExitReportController::class);

    // CV Applications
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/reprocess', [ApplicationController::class, 'reprocess'])->name('applications.reprocess');
    Route::get('/applications/{application}/cv', [ApplicationController::class, 'downloadCV'])->name('applications.cv.download');
    Route::delete('/applications/{application}', [ApplicationController::class, 'destroy'])->name('applications.destroy');
    Route::post('/applications/bulk-action', [ApplicationController::class, 'bulkAction'])->name('applications.bulk-action');
    Route::get('/applications/export/qualified', [ApplicationController::class, 'exportQualified'])->name('applications.export-qualified');
});

// Redirect after login
Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin() || auth()->user()->isHRManager()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('my.dashboard');
})->middleware(['auth'])->name('dashboard');

// Health check
Route::get('/health', function () {
    return response()->json([
        'status'    => 'healthy',
        'timestamp' => now(),
        'database'  => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'queue'     => Cache::store('redis')->ping() ? 'connected' : 'disconnected',
    ]);
});