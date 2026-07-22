<?php

use App\Http\Controllers\Api\PublicDataController;
use App\Http\Middleware\ApiAuth;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(ApiAuth::class)->group(function () {

    // Companies
    Route::get('/companies', [PublicDataController::class, 'companies'])->name('api.companies.index');
    Route::get('/companies/{company}', [PublicDataController::class, 'company'])->name('api.companies.show');

    // Indicator Groups
    Route::get('/indicator-groups', [PublicDataController::class, 'indicatorGroups'])->name('api.indicator-groups.index');
    Route::get('/indicator-groups/{group}', [PublicDataController::class, 'indicatorGroup'])->name('api.indicator-groups.show');

    // Indicators
    Route::get('/indicators', [PublicDataController::class, 'indicators'])->name('api.indicators.index');
    Route::get('/indicators/{indicator}', [PublicDataController::class, 'indicator'])->name('api.indicators.show');

    // CCM Reports
    Route::get('/ccm-reports', [PublicDataController::class, 'ccmReports'])->name('api.ccm-reports.index');
    Route::get('/ccm-reports/{ccmReport}', [PublicDataController::class, 'ccmReport'])->name('api.ccm-reports.show');

    // HIPO / Nearmiss
    Route::get('/hipo-reports', [PublicDataController::class, 'hipoReports'])->name('api.hipo-reports.index');
    Route::get('/hipo-reports/{hipoReport}', [PublicDataController::class, 'hipoReport'])->name('api.hipo-reports.show');

    // Forms (Mandala)
    Route::get('/forms', [PublicDataController::class, 'forms'])->name('api.forms.index');
    Route::get('/forms/{form}', [PublicDataController::class, 'form'])->name('api.forms.show');
});
