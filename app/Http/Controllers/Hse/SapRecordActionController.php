<?php

namespace App\Http\Controllers\Hse;

use App\Http\Controllers\Controller;
use App\Models\{SapInspection, SapPtoRecord, SapHazardReport};
use App\Support\SapRecordActions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SapRecordActionController extends Controller
{
    private function record(Request $request, string $module, int $id)
    {
        $class = match ($module) {
            'inspeksi' => SapInspection::class,
            'pto' => SapPtoRecord::class,
            'hazard' => SapHazardReport::class,
            default => abort(404),
        };
        $record = $class::findOrFail($id);
        abort_unless($request->user()->isSuperAdmin() || $request->user()->hasSiteAccess((int) $record->site_id), 403);
        return $record;
    }

    public function destroy(Request $request, string $module, int $id)
    {
        $record = $this->record($request, $module, $id);
        abort_unless(SapRecordActions::canDelete($request->user(), $record), 403);
        DB::transaction(function () use ($record, $request) {
            $record->logs()->create(['user_id'=>$request->user()->id, 'action'=>'deleted', 'description'=>'Draft dihapus melalui daftar SAP.']);
            $record->delete();
        });
        return redirect()->route('hse.dashboard.sap.'.$module.'.index')->with('ok', 'Draft berhasil dihapus.');
    }

    public function export(Request $request, string $module, int $id, string $format)
    {
        abort_unless(in_array($format, ['pdf', 'print'], true), 404);
        $record = $this->record($request, $module, $id);
        $record->load(['site', 'department']);
        $title = match ($module) {'inspeksi'=>'Inspeksi SHE/HSE', 'pto'=>'Planned Task Observation', 'hazard'=>'Hazard Report'};
        $number = $record->inspection_no ?? $record->pto_no ?? $record->hazard_no;
        $fields = match ($module) {
            'inspeksi' => ['location'=>'Lokasi', 'started_at'=>'Mulai', 'completed_at'=>'Selesai', 'score'=>'Skor', 'max_score'=>'Skor maksimum', 'percentage'=>'Persentase', 'notes'=>'Catatan', 'review_notes'=>'Catatan review'],
            'pto' => ['document_code'=>'Kode dokumen', 'observation_date'=>'Tanggal observasi', 'observation_time'=>'Waktu', 'work_activity'=>'Pekerjaan', 'work_location'=>'Lokasi', 'related_procedure'=>'Prosedur terkait', 'observation_description'=>'Deskripsi', 'safe_behavior'=>'Perilaku aman', 'risky_behavior'=>'Perilaku berisiko', 'unsafe_condition'=>'Kondisi tidak aman', 'potential_impact'=>'Potensi dampak', 'immediate_action'=>'Tindakan segera', 'recommendation'=>'Rekomendasi', 'follow_up_action'=>'Tindak lanjut', 'safe_rate'=>'Safe rate', 'review_notes'=>'Catatan review'],
            'hazard' => ['document_code'=>'Kode dokumen', 'report_date'=>'Tanggal laporan', 'report_time'=>'Waktu', 'location'=>'Lokasi', 'location_detail'=>'Detail lokasi', 'activity_related'=>'Aktivitas', 'hazard_description'=>'Deskripsi bahaya', 'potential_impact'=>'Potensi dampak', 'hazard_type'=>'Jenis bahaya', 'probability'=>'Probabilitas', 'severity'=>'Keparahan', 'risk_score'=>'Skor risiko', 'risk_level'=>'Tingkat risiko', 'risk_notes'=>'Catatan risiko', 'work_stopped'=>'Stop work', 'work_stoppage_reason'=>'Alasan stop work', 'review_notes'=>'Catatan review'],
        };
        $details = ['Site'=>$record->site?->name, 'Department'=>$record->department?->name, 'Status'=>$record->status];
        $person = match ($module) {'inspeksi'=>'inspector', 'pto'=>'observer', 'hazard'=>'reporter'};
        $details['Pelapor / pengamat'] = $record->$person?->name;
        foreach ($fields as $key=>$label) {
            $value = $record->$key;
            $details[$label] = $value instanceof \DateTimeInterface ? $value->format('d/m/Y H:i') : (is_bool($value) ? ($value ? 'Ya' : 'Tidak') : $value);
        }
        $sections = [];
        if ($module !== 'hazard') {
            $record->load(['answers.item', 'findings']);
            $sections[] = ['title'=>'Jawaban checklist', 'columns'=>['Pertanyaan', 'Jawaban', 'Catatan'], 'rows'=>$record->answers->map(fn ($answer)=>[$answer->item?->question, $answer->answer, $answer->remarks])->all()];
            $sections[] = ['title'=>'Temuan', 'columns'=>['Temuan', 'Deskripsi', 'Status'], 'rows'=>$record->findings->map(fn ($finding)=>[$finding->title ?? $finding->finding_no, $finding->description, $finding->status])->all()];
        } else {
            $record->load('correctiveActions.pic');
            $sections[] = ['title'=>'Tindakan perbaikan', 'columns'=>['Tindakan', 'PIC', 'Target', 'Status'], 'rows'=>$record->correctiveActions->map(fn ($action)=>[$action->corrective_action, $action->pic?->name, $action->target_completion_date?->format('d/m/Y'), $action->status])->all()];
        }
        $data = compact('title', 'number', 'details', 'sections', 'format');
        if ($format === 'print') return response()->view('exports.sap-record', $data);
        return Pdf::loadView('exports.sap-record', $data)->setPaper('a4')
            ->setOptions(['isRemoteEnabled'=>false, 'isPhpEnabled'=>false, 'defaultFont'=>'DejaVu Sans'])
            ->download(Str::slug($number ?: $title).'.pdf');
    }
}
