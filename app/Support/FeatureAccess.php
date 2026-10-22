<?php

namespace App\Support;

use Illuminate\Support\Str;

class FeatureAccess
{
    public const FEATURES = [
        'dashboard'=>'Dashboard', 'wellness'=>'Wellness', 'departments'=>'Departments',
        'mandala'=>'Mandala / Form & Entries', 'chat'=>'Mandala Chat', 'contracts'=>'Contracts',
        'users'=>'Manage Users', 'ccm'=>'Critical Control Management', 'hipo'=>'HIPO / Nearmiss',
        'sites'=>'Sites', 'companies'=>'Companies', 'groups'=>'Indicator Groups', 'indicators'=>'Indicators',
        'daily'=>'Input Harian', 'notes'=>'Catatan Harian', 'reports'=>'Rekap / Laporan',
        'site_access'=>'Assign Site, Role & Fitur', 'documents'=>'Documents & Templates',
    ];

    public static function forRoute(?string $route): ?string
    {
        if (!$route || $route === 'admin.sites.switch') return null;
        $patterns = [
            'dashboard'=>['dashboard','admin.dashboard'], 'wellness'=>['admin.wellness.*'],
            'departments'=>['admin.departments.*'], 'mandala'=>['admin.forms.*','admin.entries.*','front.forms.*','front.entry.*','api.admin.forms.*'],
            'chat'=>['admin.qa.*'], 'contracts'=>['admin.contracts.*','user.contracts.*','contracts.*'],
            'users'=>['admin.users.*'], 'ccm'=>['ccm-reports.*','api.admin.ccm_reports.*'],
            'hipo'=>['admin.hipo.*','user.hipo.*','api.admin.hipo.*'], 'sites'=>['admin.sites.*'],
            'companies'=>['admin.companies.*'], 'groups'=>['admin.groups.*','api.admin.groups.*'],
            'indicators'=>['admin.indicators.*','api.admin.indicators.*'], 'daily'=>['admin.daily.*','daily.*','api.admin.daily.*'],
            'notes'=>['user.daily_notes.*'], 'reports'=>['admin.reports.*'], 'site_access'=>['admin.site_access.*'],
            'documents'=>['admin.documents.*','admin.document_templates.*'],
        ];
        foreach ($patterns as $feature => $names) if (Str::is($names, $route)) return $feature;
        return null;
    }
}
