<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KeywordSetController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\StaffProfileController;   // Staff Profile module
use App\Http\Controllers\Admin\LeaveRequestController;   // Annual Leave module
use App\Http\Controllers\ApplicationSubmissionController;
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

// -----------------------------------------------------------------------
// AUTHENTICATION ROUTES
// -----------------------------------------------------------------------
require __DIR__.'/auth.php';

// -----------------------------------------------------------------------
// ADMIN ROUTES — Requires login + admin/HR role
// -----------------------------------------------------------------------
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // -------------------------------------------------------------------
    // JOB POSITIONS (Keyword Sets)
    // -------------------------------------------------------------------
    Route::resource('keyword-sets', KeywordSetController::class);
    Route::patch('/keyword-sets/{keywordSet}/toggle-status', [KeywordSetController::class, 'toggleStatus'])
        ->name('keyword-sets.toggle-status');

    // -------------------------------------------------------------------
    // STAFF PROFILES — Module 1
    // URLs: /admin/staff, /admin/staff/create, /admin/staff/{id}, etc.
    // -------------------------------------------------------------------
    Route::resource('staff', StaffProfileController::class);

    // -------------------------------------------------------------------
    // ANNUAL LEAVE TRACKING — Module 2
    // URLs: /admin/leave, /admin/leave/create, /admin/leave/{id}, etc.
    // Extra routes for approve and reject actions.
    // -------------------------------------------------------------------
    Route::resource('leave', LeaveRequestController::class);
    Route::post('/leave/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leave.approve');
    Route::post('/leave/{leave}/reject',  [LeaveRequestController::class, 'reject'])->name('leave.reject');

    // -------------------------------------------------------------------
    // CV APPLICATIONS
    // -------------------------------------------------------------------
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/reprocess', [ApplicationController::class, 'reprocess'])->name('applications.reprocess');
    Route::get('/applications/{application}/cv', [ApplicationController::class, 'downloadCV'])->name('applications.cv.download');
    Route::delete('/applications/{application}', [ApplicationController::class, 'destroy'])->name('applications.destroy');
    Route::post('/applications/bulk-action', [ApplicationController::class, 'bulkAction'])->name('applications.bulk-action');
    Route::get('/applications/export/qualified', [ApplicationController::class, 'exportQualified'])->name('applications.export-qualified');

    // -------------------------------------------------------------------
    // FUTURE MODULES — Uncomment as you build them:
    // Route::resource('compliance', ComplianceController::class);
    // Route::resource('appraisals', AppraisalController::class);
    // Route::resource('kpis', KpiController::class);
    // Route::resource('attendance', AttendanceController::class);
    // Route::resource('exit-reports', ExitReportController::class);
    // -------------------------------------------------------------------
});

// Redirect after login
Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin() || auth()->user()->isHRManager()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('application.form');
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