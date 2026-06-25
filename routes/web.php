<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestA4Controller;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\TeamAdminController;
use App\Http\Controllers\Admin\StatusAdminController;
use App\Http\Controllers\Admin\StatusActionAdminController;
use App\Http\Controllers\Admin\RequestTypeAdminController;
use App\Http\Controllers\Admin\PriorityAdminController;
use App\Http\Controllers\Admin\CompanyAdminController;
use App\Http\Controllers\Admin\SoftwareAdminController;
use App\Http\Controllers\Admin\LogAdminController;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TinyMceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// Main authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Fiches A4 (Requests)
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/',              [RequestA4Controller::class, 'index'])->name('index');
        Route::get('/create',        [RequestA4Controller::class, 'create'])->name('create');
        Route::post('/',             [RequestA4Controller::class, 'store'])->name('store');
        Route::get('/{id}',          [RequestA4Controller::class, 'show'])->name('show');
        Route::get('/{id}/edit',     [RequestA4Controller::class, 'edit'])->name('edit');
        Route::put('/{id}',          [RequestA4Controller::class, 'update'])->name('update');
        Route::delete('/{id}',       [RequestA4Controller::class, 'destroy'])->name('destroy');
        // Custom routes
        Route::get('/{id}/pdf',                           [RequestA4Controller::class, 'pdf'])->name('pdf');
        Route::post('/{id}/pdf/generate',                 [RequestA4Controller::class, 'pdf'])->name('pdf.generate');
        Route::get('/{id}/pdf/{attachmentId}/download',   [AttachmentController::class, 'download'])->name('pdf.download');
        Route::post('/{id}/actions/{actionId}',           [RequestA4Controller::class, 'executeAction'])->name('actions.execute');
        // Attachments
        Route::post('/{id}/attachments',                  [AttachmentController::class, 'storeForRequest'])->name('attachments.store');
    });

    // Attachments (global — download & delete)
    Route::prefix('attachments')->name('attachments.')->group(function () {
        Route::get('/{id}/download',                      [AttachmentController::class, 'download'])->name('download');
        Route::delete('/{id}',                            [AttachmentController::class, 'destroy'])->name('destroy');
    });

    // Tasks
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/gantt',         [TaskController::class, 'gantt'])->name('gantt');
        Route::get('/',              [TaskController::class, 'index'])->name('index');
        Route::get('/create',        [TaskController::class, 'create'])->name('create');
        Route::post('/',             [TaskController::class, 'store'])->name('store');
        Route::get('/{id}',          [TaskController::class, 'show'])->name('show');
        Route::get('/{id}/edit',     [TaskController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [TaskController::class, 'update'])->name('update');
        Route::delete('/{id}',       [TaskController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/gantt',    [TaskController::class, 'ganttByRequest'])->name('gantt.request');
    });

    // Time Entries
    Route::prefix('time-entries')->name('time-entries.')->group(function () {
        Route::get('/',              [TimeEntryController::class, 'index'])->name('index');
        Route::get('/create',        [TimeEntryController::class, 'create'])->name('create');
        Route::post('/',             [TimeEntryController::class, 'store'])->name('store');
        Route::get('/{id}',          [TimeEntryController::class, 'show'])->name('show');
        Route::get('/{id}/edit',     [TimeEntryController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [TimeEntryController::class, 'update'])->name('update');
        Route::delete('/{id}',       [TimeEntryController::class, 'destroy'])->name('destroy');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',              [ReportController::class, 'index'])->name('index');
        Route::get('/time',          [ReportController::class, 'time'])->name('time');
        Route::get('/load',          [ReportController::class, 'load'])->name('load');
        Route::get('/export',        [ReportController::class, 'export'])->name('export');
    });

    // TinyMCE image upload
    Route::post('/tinymce/upload', [TinyMceController::class, 'upload'])->name('tinymce.upload');

    // JSON/Ajax endpoints (Gantt, etc.) — session auth, no Sanctum token needed
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/tasks/gantt-data',          [TaskApiController::class, 'ganttData'])->name('tasks.gantt-data');
        Route::get('/tasks/by-user/{userId}',    [TaskApiController::class, 'byUser'])->name('tasks.by-user');
        Route::patch('/tasks/{id}/dates',        [TaskApiController::class, 'updateDates'])->name('tasks.update-dates');
        Route::patch('/tasks/{id}/progress',     [TaskApiController::class, 'updateProgress'])->name('tasks.update-progress');
    });

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',     [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/',   [ProfileController::class, 'update'])->name('update');
        Route::delete('/',  [ProfileController::class, 'destroy'])->name('destroy');
    });
});

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {

    // Users admin
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',              [UserAdminController::class, 'index'])->name('index');
        Route::get('/create',        [UserAdminController::class, 'create'])->name('create');
        Route::post('/',             [UserAdminController::class, 'store'])->name('store');
        Route::get('/{id}',          [UserAdminController::class, 'show'])->name('show');
        Route::get('/{id}/edit',     [UserAdminController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [UserAdminController::class, 'update'])->name('update');
        Route::delete('/{id}',       [UserAdminController::class, 'destroy'])->name('destroy');
    });

    // Teams admin
    Route::prefix('teams')->name('teams.')->group(function () {
        Route::get('/',              [TeamAdminController::class, 'index'])->name('index');
        Route::get('/create',        [TeamAdminController::class, 'create'])->name('create');
        Route::post('/',             [TeamAdminController::class, 'store'])->name('store');
        Route::get('/{id}',          [TeamAdminController::class, 'show'])->name('show');
        Route::get('/{id}/edit',     [TeamAdminController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [TeamAdminController::class, 'update'])->name('update');
        Route::delete('/{id}',       [TeamAdminController::class, 'destroy'])->name('destroy');
        // Team member management
        Route::prefix('{teamId}/members')->name('members.')->group(function () {
            Route::post('/',          [TeamAdminController::class, 'memberStore'])->name('store');
            Route::patch('/{turId}',  [TeamAdminController::class, 'memberUpdate'])->name('update');
            Route::delete('/{turId}', [TeamAdminController::class, 'memberDestroy'])->name('destroy');
        });
    });

    // Statuses admin
    Route::prefix('statuses')->name('statuses.')->group(function () {
        Route::get('/',              [StatusAdminController::class, 'index'])->name('index');
        Route::get('/create',        [StatusAdminController::class, 'create'])->name('create');
        Route::post('/',             [StatusAdminController::class, 'store'])->name('store');
        Route::get('/{id}',          [StatusAdminController::class, 'show'])->name('show');
        Route::get('/{id}/edit',     [StatusAdminController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [StatusAdminController::class, 'update'])->name('update');
        Route::delete('/{id}',       [StatusAdminController::class, 'destroy'])->name('destroy');
    });

    // Status Actions (nested under a status)
    Route::prefix('statuses/{statusId}/actions')->name('status-actions.')->group(function () {
        Route::get('/',              [StatusActionAdminController::class, 'index'])->name('index');
        Route::get('/create',        [StatusActionAdminController::class, 'create'])->name('create');
        Route::post('/',             [StatusActionAdminController::class, 'store'])->name('store');
        Route::get('/{id}/edit',     [StatusActionAdminController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [StatusActionAdminController::class, 'update'])->name('update');
        Route::delete('/{id}',       [StatusActionAdminController::class, 'destroy'])->name('destroy');
    });

    // Request Types admin
    Route::prefix('request-types')->name('request-types.')->group(function () {
        Route::get('/',              [RequestTypeAdminController::class, 'index'])->name('index');
        Route::get('/create',        [RequestTypeAdminController::class, 'create'])->name('create');
        Route::post('/',             [RequestTypeAdminController::class, 'store'])->name('store');
        Route::get('/{id}/edit',     [RequestTypeAdminController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [RequestTypeAdminController::class, 'update'])->name('update');
        Route::delete('/{id}',       [RequestTypeAdminController::class, 'destroy'])->name('destroy');
    });

    // Priorities admin
    Route::prefix('priorities')->name('priorities.')->group(function () {
        Route::get('/',              [PriorityAdminController::class, 'index'])->name('index');
        Route::get('/create',        [PriorityAdminController::class, 'create'])->name('create');
        Route::post('/',             [PriorityAdminController::class, 'store'])->name('store');
        Route::get('/{id}/edit',     [PriorityAdminController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [PriorityAdminController::class, 'update'])->name('update');
        Route::delete('/{id}',       [PriorityAdminController::class, 'destroy'])->name('destroy');
    });

    // Companies admin
    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/',              [CompanyAdminController::class, 'index'])->name('index');
        Route::get('/create',        [CompanyAdminController::class, 'create'])->name('create');
        Route::post('/',             [CompanyAdminController::class, 'store'])->name('store');
        Route::get('/{id}/edit',     [CompanyAdminController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [CompanyAdminController::class, 'update'])->name('update');
        Route::delete('/{id}',       [CompanyAdminController::class, 'destroy'])->name('destroy');
    });

    // Softwares admin
    Route::prefix('softwares')->name('softwares.')->group(function () {
        Route::get('/',              [SoftwareAdminController::class, 'index'])->name('index');
        Route::get('/create',        [SoftwareAdminController::class, 'create'])->name('create');
        Route::post('/',             [SoftwareAdminController::class, 'store'])->name('store');
        Route::get('/{id}/edit',     [SoftwareAdminController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [SoftwareAdminController::class, 'update'])->name('update');
        Route::delete('/{id}',       [SoftwareAdminController::class, 'destroy'])->name('destroy');
    });

    // Activity logs
    Route::get('/logs', [LogAdminController::class, 'index'])->name('logs.index');
});

require __DIR__ . '/auth.php';
