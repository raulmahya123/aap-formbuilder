<?php

use Illuminate\Support\Facades\Route;

// ADMIN Controllers
use App\Http\Controllers\Admin\DepartmentController;               // pastikan namespace & huruf besar benar
use App\Http\Controllers\Admin\DepartmentMemberController;
use App\Http\Controllers\Admin\FormController as AdminFormController;
use App\Http\Controllers\Admin\FormEntryController as AdminEntryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EntryApprovalController;

// FRONT Controllers
use App\Http\Controllers\Front\FormBrowseController;
use App\Http\Controllers\Front\FormEntryController as FrontEntryController; // ← alias agar jelas

// Model untuk route model binding pada download lampiran
use App\Http\Controllers\Admin\UserActiveController;

Route::get('/', fn () => redirect()->route('admin.dashboard'));
Route::get('/dashboard', fn () => redirect()->route('admin.dashboard'))
    ->middleware('auth')
    ->name('dashboard');

require __DIR__ . '/auth.php';

Route::middleware('auth')->group(function () {

    // ==============================
    // FRONT (user)
    // ==============================
    Route::prefix('forms')->name('front.forms.')->group(function () {
        Route::get('/', [FormBrowseController::class, 'index'])->name('index');
        Route::get('/{form:slug}', [FormBrowseController::class, 'show'])->name('show');
        Route::post('/{form:slug}', [FrontEntryController::class, 'store'])->name('store');
        Route::get('/{form:slug}/thanks', fn () => view('front.forms.thanks'))->name('thanks');
    });

    // Download lampiran entry (front)
    Route::get('/entry-file/{file}', [FrontEntryController::class, 'downloadAttachment'])
        ->name('front.entry.download.attachment')
        ->whereNumber('file'); // pastikan {file} numerik (ID FormEntryFile)

    // (Opsional) Jika user front boleh unduh PDF isian sendiri:
    // Route::get('/entry/{entry}/download-pdf', [FrontEntryController::class, 'downloadPdf'])
    //     ->name('front.entry.download_pdf')
    //     ->whereNumber('entry');

    // ==============================
    // ADMIN
    // ==============================
    Route::prefix('admin')->name('admin.')->group(function () {

        // ==== DASHBOARD ====
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/data/summary', [DashboardController::class, 'summary'])->name('dashboard.data.summary');
        Route::get('dashboard/data/entries-by-day', [DashboardController::class, 'entriesByDay'])->name('dashboard.data.entries_by_day');
        Route::get('dashboard/data/top-forms', [DashboardController::class, 'topForms'])->name('dashboard.data.top_forms');
        Route::get('dashboard/data/by-department', [DashboardController::class, 'byDepartment'])->name('dashboard.data.by_department');
        // ==== END DASHBOARD ====
        
         Route::get('/users/active', [UserActiveController::class, 'index'])->name('users.active.index');
    Route::patch('/users/{user}/toggle', [UserActiveController::class, 'toggle'])->name('users.active.toggle');
    Route::patch('/users/{user}/update', [UserActiveController::class, 'update'])->name('users.active.update');
        // Departments CRUD
        Route::resource('departments', DepartmentController::class);

        // Forms CRUD (admin)
        Route::resource('forms', AdminFormController::class)->except('show');
        Route::get('forms/{form}/builder', [AdminFormController::class, 'builder'])->name('forms.builder');
        Route::put('forms/{form}/builder', [AdminFormController::class, 'saveSchema'])->name('forms.builder.save');

        // Kelola anggota & role per department
        Route::get('departments/{department}/members', [DepartmentMemberController::class, 'index'])->name('departments.members');
        Route::post('departments/{department}/members', [DepartmentMemberController::class, 'store'])->name('departments.members.store');
        Route::delete('departments/{department}/members/{user}', [DepartmentMemberController::class, 'destroy'])->name('departments.members.destroy');

        // Entries (admin): list/detail/hapus
        Route::resource('entries', AdminEntryController::class)
            ->only(['index', 'show', 'destroy'])
            ->where(['entry' => '[0-9]+']); // pastikan {entry} numerik

        // Export CSV entries
        Route::get('entries/export', [AdminEntryController::class, 'export'])->name('entries.export');

        // Unduh PDF jawaban (admin)
        Route::get('entries/{entry}/download-pdf', [AdminEntryController::class, 'downloadPdf'])
            ->name('entries.download_pdf')
            ->whereNumber('entry');

        // Approval (admin) — PERBAIKAN: jangan double "admin." di name()
        Route::post('entries/{entry}/approval', [EntryApprovalController::class, 'act'])
            ->name('entries.approval')
            ->whereNumber('entry');

         Route::get('/entries/{entry}/download-all', [AdminEntryController::class, 'downloadAll'])
        ->name('entries.download_all');
    });
});
