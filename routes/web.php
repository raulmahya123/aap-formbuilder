<?php

use Illuminate\Support\Facades\Route;

// ADMIN Controllers
use App\Http\Controllers\Admin\DepartmentController;               // pastikan namespace & huruf besar benar
use App\Http\Controllers\Admin\DepartmentMemberController;
use App\Http\Controllers\Admin\FormController as AdminFormController;
use App\Http\Controllers\Admin\FormEntryController as AdminEntryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EntryApprovalController;
use App\Http\Controllers\Admin\DocumentAclController; // <-- PENTING: import ACL controller

// FRONT Controllers
use App\Http\Controllers\Front\FormBrowseController;
use App\Http\Controllers\Front\FormEntryController as FrontEntryController; // ← alias agar jelas

// Model untuk route model binding pada download lampiran
use App\Http\Controllers\Admin\UserActiveController;

// QA Controllers
use App\Http\Controllers\QA\QaThreadController;
use App\Http\Controllers\QA\QaMessageController;

Route::get('/', fn() => redirect()->route('admin.dashboard'));
Route::get('/dashboard', fn() => redirect()->route('admin.dashboard'))
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
        Route::get('/{form:slug}/thanks', fn() => view('front.forms.thanks'))->name('thanks');
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

        // ==== DOCUMENTS ====
        Route::prefix('documents')->name('documents.')->group(function () {

            Route::get('/', [\App\Http\Controllers\Admin\DocumentController::class, 'index'])
                ->name('index');

            Route::get('/create', [\App\Http\Controllers\Admin\DocumentController::class, 'create'])
                ->name('create')
                ->middleware('can:create,App\Models\Document');

            Route::post('/', [\App\Http\Controllers\Admin\DocumentController::class, 'store'])
                ->name('store')
                ->middleware('can:create,App\Models\Document');

            Route::get('/{document}', [\App\Http\Controllers\Admin\DocumentController::class, 'show'])
                ->name('show')->middleware('can:view,document');

            Route::get('/{document}/edit', [\App\Http\Controllers\Admin\DocumentController::class, 'edit'])
                ->name('edit')->middleware('can:update,document');

            Route::put('/{document}', [\App\Http\Controllers\Admin\DocumentController::class, 'update'])
                ->name('update')->middleware('can:update,document');

            Route::delete('/{document}', [\App\Http\Controllers\Admin\DocumentController::class, 'destroy'])
                ->name('destroy')->middleware('can:delete,document');

            // Export dokumen (PDF/HTML fallback)
            Route::get('/{document}/export', [\App\Http\Controllers\Admin\DocumentController::class, 'export'])
                ->name('export')->middleware('can:export,document');

            /**
             * === ACL (Kelola akses dokumen) ===
             * NOTE: Ditaruh DI DALAM grup 'documents' supaya name() -> 'admin.documents.acl.*'
             * dan path-nya /admin/documents/{document}/acl[/{acl}]
             */
            Route::get('/{document}/acl', [DocumentAclController::class, 'index'])
                ->name('acl.index')
                ->middleware('can:share,document');

            Route::post('/{document}/acl', [DocumentAclController::class, 'store'])
                ->name('acl.store')
                ->middleware('can:share,document');

            Route::delete('/{document}/acl/{acl}', [DocumentAclController::class, 'destroy'])
                ->name('acl.destroy')
                ->middleware('can:share,document')
                ->whereNumber('acl');

            /**
             * OPSIONAL: Kalau sebelumnya kamu punya:
             *   POST /{document}/share   -> DocumentController@share
             *   DELETE /{document}/acl/{acl} -> DocumentController@revoke
             * HAPUS keduanya agar tidak bentrok path & name.
             */
        })->whereNumber('document'); // pastikan {document} numerik (kalau ID)

        // ==== DOCUMENT TEMPLATES ====
        Route::prefix('document-templates')->name('document_templates.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'store'])->name('store');
            Route::get('/{template}/edit', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'destroy'])->name('destroy');
            Route::get('/{template}', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'show'])->name('show');
        })->whereNumber('template');

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

        // Approval (admin)
        Route::post('entries/{entry}/approval', [EntryApprovalController::class, 'act'])
            ->name('entries.approval')
            ->whereNumber('entry');

        Route::get('/entries/{entry}/download-all', [AdminEntryController::class, 'downloadAll'])
            ->name('entries.download_all');
        Route::get('/entries/{entry}/data.pdf', [AdminEntryController::class, 'downloadDataPdf'])
            ->name('entries.data_pdf');
        Route::get('/entries/export-zip', [AdminEntryController::class, 'exportZip'])
            ->name('entries.export_zip');

        // QA
        Route::prefix('qa')->name('qa.')->group(function () {
            Route::get('/', [QaThreadController::class, 'index'])->name('index');
            Route::get('/public', [QaThreadController::class, 'public'])->name('public');
            Route::get('/create', [QaThreadController::class, 'create'])->name('create');
            Route::post('/', [QaThreadController::class, 'store'])->name('store');
            Route::get('/{thread}', [QaThreadController::class, 'show'])->name('show');
            Route::post('/{thread}/messages', [QaMessageController::class, 'store'])->name('messages.store');
            Route::post('/{thread}/resolve', [QaThreadController::class, 'resolve'])->name('resolve');
        });
    });
});
