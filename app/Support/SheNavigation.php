<?php

namespace App\Support;

class SheNavigation
{
    public static function groups(): array
    {
        $link = fn ($label, $route, $params = []) => compact('label', 'route', 'params');
        $section = fn ($label, $children) => compact('label', 'children');
        $h = 'hse.dashboard.';
        return [
            ['label'=>'SHE Performance', 'icon'=>'chart', 'children'=>[
                $section('Dashboard', [$link('Performance Dashboard', $h.'safety')]),
                $section('Monitoring', array_merge(array_map(fn ($key, $label) => $link($label, $h.'performance.indicator', [$key]),
                    ['fatality','lti','non-lti','property-damage','pdfr','pd-cost','environmental','pak','kaptk','near-miss'],
                    ['Fatality','LTI','Injury Non-LTI','Property Damage','PDFR','Property Damage Cost','Environmental Accident','PAK','KAPTK','Near Miss']),
                    [$link('Daftar Manhours', $h.'manhours.index'), $link('Daftar Target KPI', $h.'targets.index')])),
                $section('Form Input', [$link('Laporan Insiden', $h.'incidents.create'), $link('Input Manhours', $h.'manhours.create'), $link('Tetapkan Target KPI', $h.'targets.create')]),
            ]],
            ['label'=>'Safety Accountability (SAP)', 'icon'=>'users', 'children'=>[
                $section('Dashboard', [$link('SAP Dashboard', $h.'sap.index'), $link('Dashboard Inspeksi', $h.'sap.inspeksi.dashboard'), $link('Dashboard PTO', $h.'sap.pto.dashboard'), $link('Dashboard Hazard', $h.'sap.hazard.dashboard')]),
                $section('Monitoring', [$link('Daftar Inspeksi', $h.'sap.inspeksi.index'), $link('Daftar PTO', $h.'sap.pto.index'), $link('Daftar Hazard Report', $h.'sap.hazard.index'), $link('Inspection Achievement', $h.'sap.achievement', ['inspection']), $link('PTO Achievement', $h.'sap.achievement', ['pto']), $link('Hazard Achievement', $h.'sap.achievement', ['hazard']), $link('Employee Performance', $h.'sap.employee'), $link('Department Performance', $h.'sap.department'), $link('Daftar Target SAP', $h.'sap.targets.index')]),
                $section('Form Input', [
                    $section('Form Inspeksi SHE/HSE', self::inspectionForms()),
                    $link('Form PTO — SHE-FM-019', $h.'sap.pto.create'), $link('Form Hazard — SHE-FM-007', $h.'sap.hazard.create'), $link('Tetapkan Target SAP', $h.'sap.targets.create'),
                ]),
            ]],
            ['label'=>'Risk Register', 'icon'=>'warning', 'children'=>[
                $section('Dashboard', [$link('Risk Dashboard', $h.'risk.dashboard'), $link('Risk Assessment Analytics', $h.'risk')]),
                $section('Monitoring', [$link('Daftar Risk Register', $h.'risk.list'), $link('High Risk Activities', $h.'risk.high_activities'), $link('HIPO Monitoring', 'admin.hipo.index'), $link('Daftar CCM', 'ccm-reports.index'), $link('Risk Control Monitoring', $h.'risk.controls')]),
                $section('Form Input', [$link('Form Risk Assessment', $h.'risk.create'), $link('Form HIPO / Nearmiss', 'admin.hipo.create'), $link('Form CCM', 'ccm-reports.create')]),
            ]],
            ['label'=>'PICA Management', 'icon'=>'access', 'children'=>[
                $section('Dashboard', [$link('PICA Dashboard', $h.'pica.dashboard')]),
                $section('Monitoring', array_map(fn ($key, $label) => $link($label, $h.'pica.list', $key ? [$key] : []), ['', 'audit','investigation','inspection','pto','hazard','verification','overdue'], ['Semua PICA','PICA Audit','PICA Investigation','PICA Inspection','PICA PTO','PICA Hazard Report','Verifikasi PICA','Overdue PICA'])),
                $section('Form Input', [$link('Form PICA Baru', $h.'pica.create')]),
            ]],
            ['label'=>'Document Repository', 'icon'=>'mandala', 'children'=>[
                $section('Dashboard', [$link('Document Dashboard', $h.'documents.dashboard')]),
                $section('Daftar Dokumen', array_map(fn ($key, $label) => $link($label, $h.'documents.index', [$key]), ['policy','sop','ik','form','approval','expiring'], ['Policy / Kebijakan','SOP / Prosedur','Instruksi Kerja','Form Documents','Antrian Approval','Review Due'])),
                $section('Form Input', [$link('Upload Dokumen', $h.'documents.create')]),
            ]],
            ['label'=>'Analytics & Reports', 'icon'=>'report', 'children'=>[
                $section('Dashboard', [$link('SHE Overview', $h.'overview'), $link('Incident & Nearmiss', $h.'incidents'), $link('Inspection Analytics', $h.'inspections'), $link('Training Matrix', $h.'training'), $link('Environment & Waste', $h.'environment'), $link('Site Performance', $h.'sites')]),
                $section('Laporan & Notifikasi', [$link('SHE Report Center', $h.'reports'), $link('Notification Center', $h.'notifications.index')]),
                $section('Form Input', [$link('Form Pelatihan', $h.'training.create'), $link('Form Lingkungan & Limbah', $h.'environment.create')]),
            ]],
        ];
    }

    private static function inspectionForms(): array
    {
        $items = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('sap_inspection_templates')) {
            foreach (\App\Models\SapInspectionTemplate::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']) as $template) {
                $items[] = ['label'=>$template->code.' · '.$template->name, 'route'=>'hse.dashboard.sap.inspeksi.create', 'params'=>['template_id'=>$template->id]];
            }
        }
        $items[] = ['label'=>'Semua Form Inspeksi', 'route'=>'hse.dashboard.sap.inspeksi.templates', 'params'=>[]];
        $items[] = ['label'=>'Jadwalkan Inspeksi', 'route'=>'hse.dashboard.sap.inspeksi.schedule.create', 'params'=>[]];
        return $items;
    }

    public static function visible(array $items, \App\Models\User $user): array
    {
        $result = [];
        foreach ($items as $item) {
            if (isset($item['children'])) {
                $item['children'] = self::visible($item['children'], $user);
                if (!$item['children']) continue;
                $item['active'] = collect($item['children'])->contains('active', true);
            } else {
                if (!\Illuminate\Support\Facades\Route::has($item['route']) || !$user->canUseFeatureRoute($item['route'])) continue;
                $item['active'] = request()->routeIs($item['route']);
                if ($item['active']) {
                    $actual = array_values(request()->route()?->parameters() ?? []);
                    foreach ($item['params'] as $key => $value) {
                        $current = is_string($key) ? request()->query($key) : ($actual[$key] ?? null);
                        if ((string)$current !== (string)$value) $item['active'] = false;
                    }
                }
            }
            $result[] = $item;
        }
        return $result;
    }
}
