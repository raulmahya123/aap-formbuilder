<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// ==============================
// ADMIN Controllers (existing)
// ==============================
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DepartmentMemberController;
use App\Http\Controllers\Admin\FormController as AdminFormController;
use App\Http\Controllers\Admin\FormEntryController as AdminEntryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EntryApprovalController;
use App\Http\Controllers\Admin\DocumentAclController;
use App\Http\Controllers\Admin\UserActiveController;

// ==============================
// FRONT Controllers
// ==============================
use App\Http\Controllers\Front\FormBrowseController;
use App\Http\Controllers\Front\FormEntryController as FrontEntryController;

// ==============================
// QA Controllers
// ==============================
use App\Http\Controllers\QA\QaThreadController;
use App\Http\Controllers\QA\QaMessageController;

// ==============================
// HSE / KPI Controllers (baru)
// ==============================
use App\Http\Controllers\Admin\SiteController as AdminSiteController;
use App\Http\Controllers\Admin\IndicatorGroupController as AdminIndicatorGroupController;
use App\Http\Controllers\Admin\IndicatorController as AdminIndicatorController;
use App\Http\Controllers\Admin\DailyInputController as AdminDailyInputController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;

// ==============================
// USER Controllers (baru HSE)
// ==============================
use App\Http\Controllers\User\IndicatorDailyController;
use App\Http\Controllers\User\DailyNoteController;

// ==============================
// Admin kelola akses user↔site (baru)
// ==============================
use App\Http\Controllers\Admin\UserSiteAccessController;

// ==============================
// CONTRACTS (Admin & User)
// ==============================
use App\Http\Controllers\Admin\ContractController as AdminContractController;
use App\Http\Controllers\User\ContractController as UserContractController;

// ==============================
// COMPANIES (Super Admin CRUD)
// ==============================
use App\Http\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Admin\HipoReportController as AdminHipoReportController;
use App\Http\Controllers\User\HipoReportController as UserHipoReportController;
use App\Http\Controllers\Admin\CcmReportController as CcmReportController;
Route::middleware('can:is-admin')
    ->prefix('ccm-reports')
    ->name('ccm-reports.')
    ->group(function () {

        Route::get('/', [CcmReportController::class, 'index'])
            ->name('index');

        Route::get('/create', [CcmReportController::class, 'create'])
            ->name('create');

        Route::post('/', [CcmReportController::class, 'store'])
            ->name('store');

        Route::get('/{ccm}', [CcmReportController::class, 'show'])
            ->name('show')
            ->whereNumber('ccm');

        Route::get('/{ccm}/edit', [CcmReportController::class, 'edit'])
            ->name('edit')
            ->whereNumber('ccm');

        Route::put('/{ccm}', [CcmReportController::class, 'update'])
            ->name('update')
            ->whereNumber('ccm');

        Route::delete('/{ccm}', [CcmReportController::class, 'destroy'])
            ->name('destroy')
            ->whereNumber('ccm');
    });

// Redirect root ke dashboard
Route::get('/', fn() => redirect()->route('admin.dashboard'));
Route::get('/dashboard', fn() => redirect()->route('admin.dashboard'))
    ->middleware('auth')
    ->name('dashboard');

require __DIR__ . '/auth.php';

Route::get('/pubfile/{path}', function (string $path) {
    $path = ltrim($path, '/');
    if (Str::contains($path, ['..', "\0"])) abort(404);
    $disk = Storage::disk('public');
    abort_unless($disk->exists($path), 404);
    $absolute = $disk->path($path);
    $mime = @mime_content_type($absolute) ?: ($disk->mimeType($path) ?? 'application/octet-stream');
    return response()->file($absolute, ['Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff']);
})->where('path', '.*')->name('pubfile.stream');

// Download (attachment)
Route::get('/pubfile-dl', function (\Illuminate\Http\Request $request) {
    $path = ltrim($request->query('path'), '/');

    abort_if(
        !$path || str_contains($path, '..'),
        404
    );

    $disk = Storage::disk('public');
    abort_unless($disk->exists($path), 404);

    // ekstensi asli
    $ext = pathinfo($path, PATHINFO_EXTENSION);

    // nama dari blade
    $name = $request->query('name', 'file');

    // nama final
    $filename = \Illuminate\Support\Str::slug($name, '-') . '.' . $ext;

    return response()->download(
        $disk->path($path),
        $filename
    );
})->name('pubfile.download');


Route::middleware('auth')->group(function () {
Route::prefix('user/hipo')->name('user.hipo.')->group(function () {
    Route::get('/', [UserHipoReportController::class, 'index'])->name('index');
    Route::get('/create', [UserHipoReportController::class, 'create'])->name('create');
    Route::post('/', [UserHipoReportController::class, 'store'])->name('store');
});
    // ==============================
    // FRONT (user)
    // ==============================
    Route::prefix('daily-notes')->name('user.daily_notes.')->group(function () {
        Route::get('/', [DailyNoteController::class, 'index'])->name('index');
        Route::get('/create', [DailyNoteController::class, 'create'])->name('create');
        Route::post('/', [DailyNoteController::class, 'store'])->name('store');
    });

    Route::prefix('forms')->name('front.forms.')->group(function () {
        Route::get('/', [FormBrowseController::class, 'index'])->name('index');

        // >>> HARUS sebelum /{form:slug}
        Route::get('/type/{doc_type}', [FormBrowseController::class, 'index'])
            ->whereIn('doc_type', ['SOP', 'IK', 'FORM'])
            ->name('index.type');
        // <<<

        // Riwayat entries user
        Route::get('/entries', [FrontEntryController::class, 'index'])->name('entries.index');
        Route::get('/entries/{entry}', [FrontEntryController::class, 'show'])
            ->name('entries.show')->whereNumber('entry');

        // Compatibility: /forms/{id} redirect ke slug
        Route::get('/{id}', function ($id) {
            $form = \App\Models\Form::query()
                ->select(['id', 'slug'])
                ->whereKey($id)
                ->firstOrFail();

            return redirect()->route('front.forms.show', $form->slug);
        })->whereNumber('id')->name('by_id');

        // Show/Fill (pakai slug)
        Route::get('/{form:slug}', [FormBrowseController::class, 'show'])->name('show');
        Route::get('/{form:slug}/fill', [FormBrowseController::class, 'show'])->name('fill');

        // Submit
        Route::post('/{form:slug}', [FrontEntryController::class, 'store'])->name('store');
        Route::post('/{form:slug}/submit', [FrontEntryController::class, 'store'])->name('submit');

        // Preview
        Route::get('/{form:slug}/preview', [FormBrowseController::class, 'preview'])->name('preview');

        // Thanks
        Route::get('/{form:slug}/thanks', fn() => view('front.forms.thanks'))->name('thanks');
    });

    // Download lampiran entry (front)
    Route::get('/entry-file/{file}', [FrontEntryController::class, 'downloadAttachment'])
        ->name('front.entry.download.attachment')
        ->whereNumber('file');

    // ==============================
    // USER — Daily HSE
    // ==============================
    Route::get('/daily', [IndicatorDailyController::class, 'index'])->name('daily.index');
    Route::post('/daily', [IndicatorDailyController::class, 'store'])->name('daily.store');
    Route::put('/daily/{daily}', [IndicatorDailyController::class, 'update'])->name('daily.update')->whereNumber('daily');
    Route::delete('/daily/{daily}', [IndicatorDailyController::class, 'destroy'])->name('daily.destroy')->whereNumber('daily');

    // ==============================
    // USER — Contracts (menu "KONTRAK SAYA")
    // ==============================
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('contracts', [UserContractController::class, 'index'])->name('contracts.index');
        Route::get('contracts/{contract}', [UserContractController::class, 'show'])->name('contracts.show')->whereNumber('contract');
        Route::get('contracts/{contract}/download', [UserContractController::class, 'download'])->name('contracts.download')->whereNumber('contract');
        Route::get('contracts/{contract}/preview', [\App\Http\Controllers\User\ContractController::class, 'preview'])
            ->name('contracts.preview')->whereNumber('contract');
    });

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
        Route::get('dashboard/data/by-aggregate', [DashboardController::class, 'byAggregate'])->name('dashboard.data.by_aggregate');

        // ==== COMPANIES (CRUD only; Super Admin) ====
        Route::middleware('can:is-admin')->prefix('companies')->name('companies.')->group(function () {
            Route::get('/',               [AdminCompanyController::class, 'index'])->name('index');
            Route::get('/create',         [AdminCompanyController::class, 'create'])->name('create');
            Route::post('/',              [AdminCompanyController::class, 'store'])->name('store');
            Route::get('/{company}',      [AdminCompanyController::class, 'show'])->name('show')->whereNumber('company');
            Route::get('/{company}/edit', [AdminCompanyController::class, 'edit'])->name('edit')->whereNumber('company');
            Route::put('/{company}',      [AdminCompanyController::class, 'update'])->name('update')->whereNumber('company');
            Route::delete('/{company}',   [AdminCompanyController::class, 'destroy'])->name('destroy')->whereNumber('company');
        });

        // ==== ACTIVE SITE SWITCH ====
        Route::post('sites/switch', [AdminSiteController::class, 'switch'])->name('sites.switch');

        // Manage Users (active/toggle)
        Route::get('/users/active', [UserActiveController::class, 'index'])
            ->name('users.active.index');
        Route::patch('/users/{user}/toggle', [UserActiveController::class, 'toggle'])
            ->name('users.active.toggle');
        Route::put('/users/{user}/active', [UserActiveController::class, 'update'])
            ->name('users.active.update');

        // Departments CRUD
        Route::resource('departments', DepartmentController::class);

        // Forms CRUD (admin) + Builder
        Route::resource('forms', AdminFormController::class)->except('show');
        Route::get('forms/{form}/builder', [AdminFormController::class, 'builder'])->name('forms.builder');
        Route::put('forms/{form}/builder', [AdminFormController::class, 'saveSchema'])->name('forms.builder.save');

        /* ⇩⇩ Tambahkan ini ⇩⇩ */
        // Lihat file (inline) dan unduh file — tidak bergantung ke /public/storage
        Route::get('forms/{form}/file', [AdminFormController::class, 'file'])->name('forms.file');
        Route::get('forms/{form}/download', [AdminFormController::class, 'download'])->name('forms.download');
        /* ⇧⇧ Tambahkan ini ⇧⇧ */

        // Kelola anggota & role per department
        Route::get('departments/{department}/members', [DepartmentMemberController::class, 'index'])->name('departments.members');
        Route::post('departments/{department}/members', [DepartmentMemberController::class, 'store'])->name('departments.members.store');
        Route::delete('departments/{department}/members/{user}', [DepartmentMemberController::class, 'destroy'])->name('departments.members.destroy');

        // ==============================
        // HSE / KPI
        // ==============================
        Route::middleware('can:is-admin')->group(function () {
            Route::resource('sites', AdminSiteController::class)->except(['show']);
            Route::resource('groups', AdminIndicatorGroupController::class)->except(['show']);
            Route::resource('indicators', AdminIndicatorController::class)->except(['show']);

            // Kelola akses user↔site
            Route::get('site-access', [UserSiteAccessController::class, 'index'])->name('site_access.index');
            Route::post('site-access', [UserSiteAccessController::class, 'store'])->name('site_access.store');
            Route::post('site-access/bulk', [UserSiteAccessController::class, 'bulk'])->name('site_access.bulk');
            Route::post('site-access/bulk-detach', [UserSiteAccessController::class, 'bulkDetachSites'])->name('site_access.bulk_detach');
            Route::delete('site-access/{userSiteAccess}', [UserSiteAccessController::class, 'destroy'])->name('site_access.destroy')->whereNumber('userSiteAccess');
            Route::delete('site-access', [UserSiteAccessController::class, 'destroySelected'])->name('site_access.destroy_selected');
        });

        // Operasional Daily (admin)
        Route::get('daily',        [AdminDailyInputController::class, 'index'])
            ->name('daily.index')
            ->middleware('can:is-admin'); // listing hanya admin (opsional)

        Route::get('daily/create', [AdminDailyInputController::class, 'create'])
            ->name('daily.create')
            ->middleware('can:daily.manage'); // tanpa site_id ⇒ Gate kamu meloloskan admin

        Route::post('daily',       [AdminDailyInputController::class, 'store'])
            ->name('daily.store')
            ->middleware('can:daily.manage'); // StoreDailyRequest juga meng-autorize

        // Rekap utama (pakai aggregate.blade.php)
        Route::get('reports/monthly', [AdminReportController::class, 'report'])
            ->name('reports.monthly');

        // ==== Edit / Override Total (khusus super_admin) ====
        Route::get('reports/totals/edit', [AdminReportController::class, 'editTotal'])
            ->name('report-totals.edit');   // → admin.report-totals.edit

        Route::post('reports/totals/update', [AdminReportController::class, 'updateTotal'])
            ->name('report-totals.update'); // → admin.report-totals.update

        // ==============================
        // DOCUMENTS
        // ==============================
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\DocumentController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\DocumentController::class, 'create'])
                ->name('create')->middleware('can:create,App\Models\Document');
            Route::post('/', [\App\Http\Controllers\Admin\DocumentController::class, 'store'])
                ->name('store')->middleware('can:create,App\Models\Document');

            Route::get('/{document}', [\App\Http\Controllers\Admin\DocumentController::class, 'show'])
                ->name('show')->middleware('can:view,document');
            Route::get('/{document}/edit', [\App\Http\Controllers\Admin\DocumentController::class, 'edit'])
                ->name('edit')->middleware('can:update,document');
            Route::put('/{document}', [\App\Http\Controllers\Admin\DocumentController::class, 'update'])
                ->name('update')->middleware('can:update,document');
            Route::delete('/{document}', [\App\Http\Controllers\Admin\DocumentController::class, 'destroy'])
                ->name('destroy')->middleware('can:delete,document');
            Route::get('/{document}/export', [\App\Http\Controllers\Admin\DocumentController::class, 'export'])
                ->name('export')->middleware('can:export,document');

            // === ACL ===
            Route::get('/{document}/acl', [DocumentAclController::class, 'index'])
                ->name('acl.index')->middleware('can:share,document');
            Route::post('/{document}/acl', [DocumentAclController::class, 'store'])
                ->name('acl.store.single')->middleware('can:share,document');
            Route::delete('/{document}/acl/{acl}', [DocumentAclController::class, 'destroy'])
                ->name('acl.destroy')->middleware('can:share,document')->whereNumber('acl');

            // BULK store ACL (tanpa {document})
            Route::post('/acl', [DocumentAclController::class, 'storeBulk'])->name('acl.store');
        });

        // ==============================
        // DOCUMENT TEMPLATES
        // ==============================
        Route::prefix('document-templates')->name('document_templates.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'store'])->name('store');
            Route::get('/{template}/edit', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'edit'])->name('edit')->whereNumber('template');
            Route::put('/{template}', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'update'])->name('update')->whereNumber('template');
            Route::delete('/{template}', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'destroy'])->name('destroy')->whereNumber('template');
            Route::get('/{template}', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'show'])->name('show')->whereNumber('template');
        });

        Route::post('/upload-temp', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'uploadTemp'])->name('upload_temp');
        Route::post('/uploads/image', [\App\Http\Controllers\Admin\DocumentTemplateController::class, 'storeImage'])->name('uploads.image');

        // Entries
        Route::resource('entries', AdminEntryController::class)
            ->only(['index', 'show', 'destroy'])
            ->where(['entry' => '[0-9]+']);

        Route::get('entries/export', [AdminEntryController::class, 'export'])->name('entries.export');
        Route::get('entries/{entry}/download-pdf', [AdminEntryController::class, 'downloadPdf'])->name('entries.download_pdf')->whereNumber('entry');
        Route::post('entries/{entry}/approval', [EntryApprovalController::class, 'act'])->name('entries.approval')->whereNumber('entry');
        Route::get('entries/{entry}/download-all', [AdminEntryController::class, 'downloadAll'])->name('entries.download_all')->whereNumber('entry');
        Route::get('entries/{entry}/data.pdf', [AdminEntryController::class, 'downloadDataPdf'])->name('entries.data_pdf')->whereNumber('entry');
        Route::get('entries/export-zip', [AdminEntryController::class, 'exportZip'])->name('entries.export_zip');

        // ==============================
        // QA
        // ==============================
        Route::prefix('qa')->name('qa.')->group(function () {
            Route::get('/', [QaThreadController::class, 'index'])->name('index');
            Route::get('/public', [QaThreadController::class, 'public'])->name('public');
            Route::get('/create', [QaThreadController::class, 'create'])->name('create');
            Route::post('/', [QaThreadController::class, 'store'])->name('store');
            Route::get('/{thread}', [QaThreadController::class, 'show'])->name('show')->whereNumber('thread');
            Route::post('/{thread}/messages', [QaMessageController::class, 'store'])->name('messages.store')->whereNumber('thread');
            Route::post('/{thread}/resolve', [QaThreadController::class, 'resolve'])->name('resolve')->whereNumber('thread');
        });

        // ==============================
        // CONTRACTS (ADMIN)
        // ==============================
        Route::prefix('contracts')->name('contracts.')->group(function () {
            Route::get('/', [AdminContractController::class, 'index'])->name('index');
            Route::get('/create', [AdminContractController::class, 'create'])
                ->name('create')->middleware('can:create,App\Models\Contract');
            Route::post('/', [AdminContractController::class, 'store'])
                ->name('store')->middleware('can:create,App\Models\Contract');
            Route::delete('/{contract}', [AdminContractController::class, 'destroy'])
                ->name('destroy')->whereNumber('contract');

            Route::get('/{contract}', [AdminContractController::class, 'show'])
                ->name('show')->middleware('can:view,contract')->whereNumber('contract');
            Route::get('/{contract}/download', [AdminContractController::class, 'download'])
                ->name('download')->middleware('can:view,contract')->whereNumber('contract');

            // Preview
            Route::get('/{contract}/preview', [AdminContractController::class, 'preview'])
                ->name('preview')->middleware('can:view,contract')->whereNumber('contract');

            Route::post('/{contract}/share', [AdminContractController::class, 'share'])
                ->name('share')->middleware('can:share,contract')->whereNumber('contract');
            Route::delete('/{contract}/revoke', [AdminContractController::class, 'revoke'])
                ->name('revoke')->middleware('can:share,contract')->whereNumber('contract');
        });

        // ==============================
        // ADMIN — HIPO / Nearmiss
        // ==============================
        Route::middleware('can:is-admin')
            ->prefix('hipo')
            ->name('hipo.')
            ->group(function () {

                Route::get('/', [AdminHipoReportController::class, 'index'])->name('index');
                Route::get('/{hipo}', [AdminHipoReportController::class, 'show'])->name('show');
                Route::put('/{hipo}', [AdminHipoReportController::class, 'update'])->name('update');
                Route::delete('/{hipo}', [AdminHipoReportController::class, 'destroy'])->name('destroy');
            });
    }); // end prefix admin
}); // end middleware auth
