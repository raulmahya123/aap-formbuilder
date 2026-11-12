<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\WellnessResponse;
use App\Support\SiteSelection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WellnessController extends Controller
{
    public function index(Request $request): View
    {
        $base = $this->filteredResponses($request);
        $responses = (clone $base)
            ->latest()
            ->paginate(\App\Support\Listing::perPage())->withQueryString();

        $stats = [
            'total' => (clone $base)->count(),
            'today' => (clone $base)->whereDate('created_at', today())->count(),
            'not_ready' => (clone $base)->whereIn('work_readiness', ['tidak', 'off_day'])->count(),
            'complaints' => (clone $base)->whereIn('health_complaint', ['ya', 'ringan', 'berat'])->count(),
        ];

        return view('admin.wellness.index', compact('responses', 'stats'));
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $responses = $this->filteredResponses($request)->latest()->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Health Assessment');

        $columns = [
            'Waktu pengisian', 'Nama karyawan', 'NIK', 'Email', 'Jabatan', 'Departemen', 'Site', 'Shift',
            'Durasi tidur', 'Kualitas tidur', 'Alasan tidur buruk', 'Mengantuk berat / sulit fokus',
            'Keluhan kesehatan', 'Rincian keluhan / gejala', 'Obat berefek kantuk', 'Nama obat',
            'Kesiapan kerja', 'Mood (data lama)', 'Tingkat stres (data lama)', 'Tidur (jam, data lama)',
            'Aktivitas fisik (data lama)', 'Catatan (data lama)',
        ];
        $lastColumn = $this->excelColumn(count($columns));

        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->setCellValue('A1', 'AHEAD — Andalan Health Evaluation And Detection');
        $sheet->mergeCells("A2:{$lastColumn}2");
        $sheet->setCellValue('A2', 'Diekspor: '.now(config('app.timezone', 'Asia/Jakarta'))->format('d/m/Y H:i').' WIB · Mengikuti filter dan site yang diizinkan');
        $sheet->fromArray($columns, null, 'A4');

        foreach ($responses as $index => $response) {
            $row = $index + 5;
            $values = [
                $response->created_at?->format('d/m/Y H:i') ?? '—',
                $response->name,
                $response->nik,
                $response->email,
                $response->position,
                $response->department,
                $response->site,
                $this->label($response->shift, ['pagi' => 'Pagi', 'malam' => 'Malam']),
                $this->label($response->sleep_before_shift, ['lebih_6_jam' => 'Lebih dari 6 jam', '6_jam' => '6 jam', 'kurang_6_jam' => 'Kurang dari 6 jam']),
                $this->label($response->sleep_quality, ['baik' => 'Baik', 'cukup' => 'Cukup', 'buruk' => 'Buruk']),
                $response->sleep_quality_reason,
                $this->label($response->heavy_drowsiness, ['ya' => 'Iya', 'tidak' => 'Tidak']),
                $this->label($response->health_complaint, ['ya' => 'Ada keluhan', 'tidak' => 'Tidak ada keluhan', 'ringan' => 'Keluhan ringan', 'berat' => 'Keluhan berat', 'tidak_ada' => 'Tidak ada keluhan']),
                $response->health_complaint_details,
                $this->label($response->drowsy_medication, ['ya' => 'Iya', 'tidak' => 'Tidak']),
                $response->drowsy_medication_details,
                $this->label($response->work_readiness, ['ya' => 'Siap bekerja', 'tidak' => 'Tidak siap', 'off_day' => 'Off day']),
                $response->mood,
                $response->stress_level,
                $response->sleep_hours,
                $response->physical_activity,
                $response->notes,
            ];

            foreach ($values as $columnIndex => $value) {
                // Explicit text cells prevent user-entered answers from being
                // interpreted as spreadsheet formulas.
                $sheet->setCellValueExplicit($this->excelColumn($columnIndex + 1).$row, (string) ($value ?? '—'), DataType::TYPE_STRING);
            }
        }

        $lastRow = max(5, $responses->count() + 4);
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setName('Arial')->setSize(16)->setBold(true)->getColor()->setRGB('713F12');
        $sheet->getStyle("A2:{$lastColumn}2")->getFont()->setName('Arial')->setSize(10)->getColor()->setRGB('52525B');
        $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
            'font' => ['name' => 'Arial', 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '713F12']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getStyle("A5:{$lastColumn}{$lastRow}")->getFont()->setName('Arial')->setSize(10);
        $sheet->getStyle("A4:{$lastColumn}{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
        $sheet->getRowDimension(4)->setRowHeight(34);
        $sheet->freezePane('A5');
        if ($responses->isNotEmpty()) {
            $sheet->setAutoFilter("A4:{$lastColumn}{$lastRow}");
        }

        $widths = [19, 24, 18, 30, 22, 22, 18, 12, 20, 16, 34, 28, 22, 38, 22, 32, 20, 18, 18, 20, 24, 36];
        foreach ($widths as $index => $width) {
            $sheet->getColumnDimension($this->excelColumn($index + 1))->setWidth($width);
        }

        $temporaryFile = tempnam(sys_get_temp_dir(), 'wellness-export-');
        (new Xlsx($spreadsheet))->save($temporaryFile);
        $spreadsheet->disconnectWorksheets();

        return response()->download($temporaryFile, 'health-assessment-'.now()->format('Ymd-His').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function filteredResponses(Request $request): Builder
    {
        $siteId = SiteSelection::id($request);
        $allowed = $request->user()->allowedSiteIds();
        $sites = Site::query()
            ->when(is_array($allowed), fn ($query) => $query->whereIn('id', $allowed ?: [-1]))
            ->when($siteId, fn ($query) => $query->where('id', $siteId))
            ->get(['id', 'code', 'name']);
        $labels = $sites->flatMap(fn ($site) => [$site->name, $site->code])->filter()->unique()->values()->all();

        return WellnessResponse::query()
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($employees) => $employees
                ->where('name', 'like', '%'.$request->string('q').'%')
                ->orWhere('nik', 'like', '%'.$request->string('q').'%')))
            ->when($siteId || is_array($allowed), fn ($query) => $query->whereIn('site', $labels ?: ['__none__']))
            ->when($request->filled('site'), fn ($query) => $query->where('site', 'like', '%'.(string) $request->string('site').'%'))
            ->when($request->filled('work_readiness'), fn ($query) => $query->where('work_readiness', (string) $request->string('work_readiness')));
    }

    private function label(?string $value, array $labels): string
    {
        if (!$value) {
            return '—';
        }

        return $labels[$value] ?? ucfirst(str_replace('_', ' ', $value));
    }

    private function excelColumn(int $number): string
    {
        $column = '';
        while ($number > 0) {
            $number--;
            $column = chr(65 + ($number % 26)).$column;
            $number = intdiv($number, 26);
        }

        return $column;
    }
}
