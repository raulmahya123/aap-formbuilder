<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Form;
use App\Models\FormEntry;

echo "=== FORMS LIST ===\n";
$forms = Form::all();
foreach ($forms as $f) {
    $entryCount = FormEntry::where('form_id', $f->id)->count();
    echo "ID: {$f->id} | Title: {$f->title} | Slug: {$f->slug} | Type: {$f->type} | Active: {$f->is_active} | DocType: {$f->doc_type} | Entries: {$entryCount}\n";
}

echo "\n=== HSE INCIDENTS COUNT ===\n";
echo "HseIncident count: " . DB::table('hse_incidents')->count() . "\n";
echo "HseInspection count: " . DB::table('hse_inspections')->count() . "\n";
echo "HseFinding count: " . DB::table('hse_findings')->count() . "\n";
echo "HseCorrectiveAction count: " . DB::table('hse_corrective_actions')->count() . "\n";
echo "HseRiskAssessment count: " . DB::table('hse_risk_assessments')->count() . "\n";
echo "HseExposureHour count: " . DB::table('hse_exposure_hours')->count() . "\n";
echo "HseKpiTarget count: " . DB::table('hse_kpi_targets')->count() . "\n";
echo "HipoReport count: " . DB::table('hipo_reports')->count() . "\n";
echo "CcmReport count: " . DB::table('ccm_reports')->count() . "\n";
echo "IndicatorDaily count: " . DB::table('indicator_daily')->count() . "\n";
echo "Indicator count: " . DB::table('indicators')->count() . "\n";
