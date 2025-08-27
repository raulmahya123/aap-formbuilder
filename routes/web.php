<?php

use Illuminate\Support\Facades\Route;

// ADMIN Controllers
use App\Http\Controllers\Admin\DepartmentController;               // pastikan namespace & huruf besar benar
use App\Http\Controllers\Admin\DepartmentMemberController;
use App\Http\Controllers\Admin\FormController as AdminFormController;
use App\Http\Controllers\Admin\FormEntryController as AdminEntryController;
use App\Http\Controllers\Admin\DashboardController; 
// FRONT Controllers
use App\Http\Controllers\Front\FormBrowseController;
use App\Http\Controllers\Front\FormEntryController;
use App\Http\Controllers\Admin\EntryApprovalController;

// Model untuk route model binding pada download lampiran
use App\Models\FormEntryFile;

Route::get('/', fn() => redirect()->route('admin.dashboard'));
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
        Route::post('/{form:slug}', [FormEntryController::class, 'store'])->name('store');
        Route::get('/{form:slug}/thanks', fn() => view('front.forms.thanks'))->name('thanks');
    });

    // >>> ROUTE TAMBAHAN (FRONT) — DOWNLOAD LAMPIRAN ENTRY <<<
    // Mengunduh file lampiran yang diunggah user saat submit form builder.
    // - Uses: Front\FormEntryController@downloadAttachment
    // - Binding: {file} → App\Models\FormEntryFile
    // - Keamanan: tetap cek policy view terhadap form dari entry terkait di dalam controller.
    Route::get('/entry-file/{file}', [FormEntryController::class, 'downloadAttachment'])
        ->name('front.entry.download.attachment')
        ->whereNumber('file'); // pastikan hanya angka (ID FormEntryFile)

    // (Opsional) Jika juga ingin user front bisa unduh PDF bukti isian sendiri:
    // Route::get('/entry/{entry}/download-pdf', [FormEntryController::class, 'downloadPdf'])
    //     ->name('front.entry.download_pdf')
    //     ->whereNumber('entry');

    // ==============================
    // ADMIN
    // ==============================
    Route::prefix('admin')->name('admin.')->group(function () {


        // ==== DASHBOARD (BARU) ====
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/data/summary', [DashboardController::class, 'summary'])->name('dashboard.data.summary');
        Route::get('dashboard/data/entries-by-day', [DashboardController::class, 'entriesByDay'])->name('dashboard.data.entries_by_day');
        Route::get('dashboard/data/top-forms', [DashboardController::class, 'topForms'])->name('dashboard.data.top_forms');
        Route::get('dashboard/data/by-department', [DashboardController::class, 'byDepartment'])->name('dashboard.data.by_department');
        // ==== END DASHBOARD ====

        // Departments CRUD
        Route::resource('departments', DepartmentController::class);

        // Forms CRUD (admin)
        Route::resource('forms', AdminFormController::class)->except('show');
        Route::get('forms/{form}/builder', [AdminFormController::class, 'builder'])
            ->name('forms.builder');
        Route::put('forms/{form}/builder', [AdminFormController::class, 'saveSchema'])
            ->name('forms.builder.save');
        // Kelola anggota & role per department
        Route::get('departments/{department}/members', [DepartmentMemberController::class, 'index'])
            ->name('departments.members');
        Route::post('departments/{department}/members', [DepartmentMemberController::class, 'store'])
            ->name('departments.members.store');
        Route::delete('departments/{department}/members/{user}', [DepartmentMemberController::class, 'destroy'])
            ->name('departments.members.destroy');

        // Entries (admin): list/detail/hapus + export CSV + unduh PDF
        Route::resource('entries', AdminEntryController::class)->only(['index', 'show', 'destroy']);
        Route::get('entries-export', [AdminEntryController::class, 'export'])->name('entries.export');

        // Unduh PDF jawaban (admin)
        Route::get('entries/{entry}/download-pdf', [AdminEntryController::class, 'downloadPdf'])
            ->name('entries.download_pdf')
            ->whereNumber('entry');
            Route::post('entries/{entry}/approval', [EntryApprovalController::class,'act'])
  ->name('admin.entries.approval');
    });
});
