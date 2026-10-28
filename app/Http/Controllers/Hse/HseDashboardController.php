<?php

namespace App\Http\Controllers\Hse;

use App\Http\Controllers\Controller;
use App\Models\HseReportRun;
use App\Services\Hse\HseDashboardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HseDashboardController extends Controller
{
    public function __construct(private readonly HseDashboardService $service) {}

    public function overview(Request $request)
    {
        return $this->render($request, 'overview', 'overview');
    }

    public function safety(Request $request)
    {
        return $this->render($request, 'safety', 'safety');
    }

    public function incidents(Request $request)
    {
        return $this->render($request, 'incidents', 'incidents');
    }

    public function inspections(Request $request)
    {
        return $this->render($request, 'inspections', 'inspections');
    }

    public function training(Request $request)
    {
        return $this->render($request, 'training', 'training');
    }

    public function environment(Request $request)
    {
        return $this->render($request, 'environment', 'environment');
    }

    public function risk(Request $request)
    {
        return $this->render($request, 'risk', 'riskCompliance');
    }

    public function actions(Request $request)
    {
        return $this->render($request, 'actions', 'actions');
    }

    public function sites(Request $request)
    {
        return $this->render($request, 'sites', 'sites');
    }

    public function reports(Request $request)
    {
        $filters = $this->service->filters($request);
        $options = $this->service->options($filters);
        $data = $this->service->reports($filters);

        return view('hse.dashboard.reports', compact('filters', 'options', 'data'));
    }

    public function export(string $report, Request $request): StreamedResponse
    {
        $filters = $this->service->filters($request);
        $data = $this->service->reportData($report, $filters);
        $title = $this->reportTitle($report);

        HseReportRun::create([
            'report_key' => $report,
            'title' => $title,
            'period_from' => $filters['from']->toDateString(),
            'period_to' => $filters['to']->toDateString(),
            'filters' => $this->service->filters($request),
            'format' => 'csv',
            'status' => 'Completed',
            'generated_by' => $request->user()?->id,
        ]);

        $filename = 'hse-'.$report.'-'.$filters['from']->format('Ymd').'-'.$filters['to']->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($data, $title, $filters) {
            $out = fopen('php://output', 'w');
            // Excel on Windows needs a UTF-8 BOM to read Indonesian text and symbols correctly.
            fwrite($out, "\xEF\xBB\xBF");
            $writeRow = static function (array $row) use ($out): void {
                $row = array_map(static function ($value) {
                    if ($value === null) {
                        return '—';
                    }
                    if (is_bool($value)) {
                        return $value ? 'Ya' : 'Tidak';
                    }
                    if (is_scalar($value) || $value instanceof \Stringable) {
                        return (string) $value;
                    }

                    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
                }, $row);

                // Explicit RFC-compatible escaping and Windows line endings keep CSV import predictable.
                fputcsv($out, $row, ',', '"', '', "\r\n");
            };

            $writeRow([$title]);
            $writeRow(['Period', $filters['label']]);
            $writeRow([]);

            if (! empty($data['kpis'])) {
                $writeRow(['KPI', 'Value', 'Previous', 'Change %', 'Target', 'Unit', 'Formula']);
                foreach ($data['kpis'] as $kpi) {
                    $writeRow([
                        $kpi['label'],
                        $kpi['value'],
                        $kpi['previous'],
                        $kpi['change'],
                        $kpi['target'],
                        $kpi['unit'],
                        $kpi['formula'],
                    ]);
                }
                $writeRow([]);
            }

            foreach ($this->reportSections($data) as $section) {
                $writeRow([$section['title']]);
                $writeRow($section['columns']);
                foreach ($section['rows'] as $row) {
                    $writeRow($row);
                }
                $writeRow([]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    public function exportExcel(string $report, Request $request): BinaryFileResponse
    {
        $filters = $this->service->filters($request);
        $data = $this->service->reportData($report, $filters);
        $title = $this->reportTitle($report);
        $sections = $this->reportSections($data);
        $spreadsheet = new Spreadsheet();

        $summary = $spreadsheet->getActiveSheet();
        $summary->setTitle('Ringkasan KPI');
        $summary->mergeCells('A1:G1');
        $summary->setCellValue('A1', $title);
        $summary->mergeCells('A2:G2');
        $summary->setCellValue('A2', 'Periode: '.$filters['label']);
        $summary->mergeCells('A3:G3');
        $summary->setCellValue('A3', 'Dibuat: '.now()->format('d/m/Y H:i'));
        $summary->fromArray(['KPI', 'Nilai', 'Sebelumnya', 'Perubahan %', 'Target', 'Satuan', 'Keterangan'], null, 'A5');

        $kpiRows = array_map(fn (array $kpi) => [
            $kpi['label'] ?? '—', $kpi['value'] ?? '—', $kpi['previous'] ?? '—',
            $kpi['change'] ?? '—', $kpi['target'] ?? '—', $kpi['unit'] ?? '—', $kpi['formula'] ?? '—',
        ], $data['kpis'] ?? []);
        if ($kpiRows !== []) {
        foreach ($kpiRows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $this->setExcelValue($summary, $this->excelColumn($columnIndex + 1).(6 + $rowIndex), $value);
            }
        }
        } else {
            $summary->mergeCells('A6:G6');
            $summary->setCellValue('A6', 'Tidak ada ringkasan KPI untuk laporan ini.');
        }

        $this->styleReportSheet($summary, 'A1:G'.max(6, 5 + count($kpiRows)), 7);
        $summary->getColumnDimension('A')->setWidth(30);
        foreach (['B', 'C', 'D', 'E', 'F'] as $column) {
            $summary->getColumnDimension($column)->setWidth(16);
        }
        $summary->getColumnDimension('G')->setWidth(68);
        $summary->getStyle('G6:G'.max(6, 5 + count($kpiRows)))->getAlignment()->setWrapText(true);
        $summary->freezePane('A6');
        $summary->setAutoFilter('A5:G'.max(5, 5 + count($kpiRows)));

        $details = $spreadsheet->createSheet();
        $details->setTitle('Data Laporan');
        $detailColumnCount = max(7, collect($sections)->max(fn ($section) => count($section['columns'])) ?? 0);
        $detailLastColumn = $this->excelColumn($detailColumnCount);
        $details->mergeCells("A1:{$detailLastColumn}1");
        $details->setCellValue('A1', $title.' — Data Pendukung');
        $details->mergeCells("A2:{$detailLastColumn}2");
        $details->setCellValue('A2', 'Periode: '.$filters['label']);
        $details->mergeCells("A3:{$detailLastColumn}3");
        $details->setCellValue('A3', 'Dibuat: '.now()->format('d/m/Y H:i'));

        $rowNumber = 5;
        foreach ($sections as $section) {
            $lastColumn = $this->excelColumn(count($section['columns']));
            $details->mergeCells("A{$rowNumber}:{$lastColumn}{$rowNumber}");
            $details->setCellValue("A{$rowNumber}", $section['title']);
            $details->getStyle("A{$rowNumber}:{$lastColumn}{$rowNumber}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '18181B'], 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4F4F5']],
            ]);
            $rowNumber++;

            foreach ($section['columns'] as $columnIndex => $columnName) {
                $cell = $this->excelColumn($columnIndex + 1).$rowNumber;
                $details->setCellValueExplicit($cell, (string) $columnName, DataType::TYPE_STRING);
            }
            $details->getStyle("A{$rowNumber}:{$lastColumn}{$rowNumber}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '713F12']],
            ]);
            $headerRow = $rowNumber;
            $rowNumber++;

            foreach ($section['rows'] as $values) {
                foreach (array_values($values) as $columnIndex => $value) {
                    $cell = $this->excelColumn($columnIndex + 1).$rowNumber;
                    $this->setExcelValue($details, $cell, $value);
                }
                $rowNumber++;
            }
            $details->getStyle("A{$headerRow}:{$lastColumn}".($rowNumber - 1))->applyFromArray([
                'borders' => ['bottom' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'D4D4D8']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
            ]);
            $rowNumber++;
        }

        $details->getStyle("A1:{$detailLastColumn}3")->getFont()->setName('Arial');
        $details->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('713F12');
        foreach (range(1, $detailColumnCount) as $columnIndex) {
            $details->getColumnDimension($this->excelColumn($columnIndex))->setWidth($columnIndex === 1 ? 30 : 22);
        }
        $details->freezePane('A5');

        $filename = 'hse-'.$report.'-'.$filters['from']->format('Ymd').'-'.$filters['to']->format('Ymd').'.xlsx';
        $temporaryFile = tempnam(sys_get_temp_dir(), 'hse-report-');
        (new Xlsx($spreadsheet))->save($temporaryFile);
        $spreadsheet->disconnectWorksheets();
        $this->recordReportRun($report, $title, $filters, 'xlsx', $request);

        return response()->download($temporaryFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function exportPdf(string $report, Request $request)
    {
        $filters = $this->service->filters($request);
        $options = $this->service->options($filters);
        $data = $this->service->reportData($report, $filters);
        $title = $this->reportTitle($report);
        $sections = $this->reportSections($data);
        $maxColumns = max(7, collect($sections)->max(fn ($section) => count($section['columns'])) ?? 0);
        $orientation = $maxColumns > 5 ? 'landscape' : 'portrait';
        $filterLabels = [
            'site' => $options['sites']->firstWhere('id', $filters['site_id'])?->name,
            'company' => $options['companies']->firstWhere('id', $filters['company_id'])?->name,
            'department' => $options['departments']->firstWhere('id', $filters['department_id'])?->name,
        ];

        $pdf = Pdf::loadView('hse.dashboard.report-pdf', [
            'title' => $title,
            'filters' => $filters,
            'filterLabels' => $filterLabels,
            'kpis' => $data['kpis'] ?? [],
            'sections' => $sections,
            'generatedAt' => now(),
        ])->setPaper('a4', $orientation)
            ->setOptions(['isRemoteEnabled' => false, 'isPhpEnabled' => false, 'defaultFont' => 'DejaVu Sans']);
        $binary = $pdf->output();
        $this->recordReportRun($report, $title, $filters, 'pdf', $request);
        $filename = 'hse-'.$report.'-'.$filters['from']->format('Ymd').'-'.$filters['to']->format('Ymd').'.pdf';

        return response()->streamDownload(static function () use ($binary): void {
            echo $binary;
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    private function reportSections(array $data): array
    {
        $labels = [
            'rows' => 'Data', 'table' => 'Data', 'history' => 'Riwayat', 'findings' => 'Temuan',
            'upcoming' => 'Jadwal Mendatang', 'upcoming_inspections' => 'Inspeksi Mendatang',
            'recent' => 'Data Terbaru', 'overdue_actions' => 'Corrective Action Terlambat',
            'high_risk' => 'Risiko Tinggi', 'extreme_risk' => 'Risiko Ekstrem',
            'overdue_treatment' => 'Treatment Terlambat', 'expiring_permits' => 'Permit Akan Kedaluwarsa',
            'non_compliance' => 'Requirement Belum Terpenuhi', 'cards' => 'Perbandingan Site', 'totals' => 'Metrik Lingkungan',
        ];
        $sections = [];

        foreach ($labels as $key => $title) {
            $items = $data[$key] ?? [];
            if (! is_array($items) || $items === []) {
                continue;
            }

            if ($key === 'totals') {
                $rows = [];
                foreach ($items as $metric => $values) {
                    if (is_array($values)) {
                        $rows[] = array_merge(['metric' => $metric], $values);
                    }
                }
                $items = $rows;
            }

            $first = $items[0] ?? null;
            if (! is_array($first) || $first === []) {
                continue;
            }
            $columns = array_keys($first);
            $rows = [];
            foreach ($items as $item) {
                if (is_array($item)) {
                    $rows[] = array_map(fn ($column) => $this->reportValue($item[$column] ?? null), $columns);
                }
            }

            if ($rows !== []) {
                $sections[] = ['title' => $title, 'columns' => $columns, 'rows' => $rows];
            }
        }

        return $sections;
    }

    private function reportValue(mixed $value): string|int|float
    {
        if ($value === null || $value === '') {
            return '—';
        }
        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        if (is_scalar($value) || $value instanceof \Stringable) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '—';
    }

    private function setExcelValue($sheet, string $cell, string|int|float $value): void
    {
        if (is_int($value) || is_float($value)) {
            $sheet->setCellValue($cell, $value);
        } else {
            $sheet->setCellValueExplicit($cell, $value, DataType::TYPE_STRING);
        }
    }

    private function styleReportSheet($sheet, string $range, int $columns): void
    {
        $sheet->getStyle($range)->getFont()->setName('Arial')->setSize(10);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('713F12');
        $sheet->getStyle('A5:'.$this->excelColumn($columns).'5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('A5:'.$this->excelColumn($columns).substr($range, strpos($range, ':') + 1))->getBorders()->getBottom()->setBorderStyle(Border::BORDER_HAIR)->getColor()->setRGB('D4D4D8');
    }

    private function excelColumn(int $number): string
    {
        $column = '';
        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $column = chr(65 + $remainder).$column;
            $number = intdiv($number - 1, 26);
        }

        return $column;
    }

    private function recordReportRun(string $report, string $title, array $filters, string $format, Request $request): void
    {
        HseReportRun::create([
            'report_key' => $report,
            'title' => $title,
            'period_from' => $filters['from']->toDateString(),
            'period_to' => $filters['to']->toDateString(),
            'filters' => $filters,
            'format' => $format,
            'status' => 'Completed',
            'generated_by' => $request->user()?->id,
        ]);
    }

    private function render(Request $request, string $view, string $method)
    {
        $filters = $this->service->filters($request);
        $options = $this->service->options($filters);
        $data = $this->service->{$method}($filters);

        return view('hse.dashboard.'.$view, compact('filters', 'options', 'data'));
    }

    private function reportTitle(string $report): string
    {
        return match ($report) {
            'safety' => 'Monthly Performance',
            'incidents' => 'Incident Report',
            'sap' => 'SAP Achievement Report',
            'inspections' => 'Inspection Summary',
            'audit' => 'Audit Finding Report',
            'training' => 'Training Compliance Report',
            'environment' => 'Environmental Performance Report',
            'risk' => 'Risk Register Report',
            'actions' => 'PICA Report',
            'sites' => 'Site Comparison Report',
            'manhours' => 'Manhours Report',
            'property_damage' => 'Property Damage Report',
            default => 'Executive SHE Report',
        };
    }
}
