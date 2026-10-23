<?php

namespace App\Http\Controllers\Hse;

use App\Http\Controllers\Controller;
use App\Models\HseReportRun;
use App\Services\Hse\HseDashboardService;
use Illuminate\Http\Request;
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
            fputcsv($out, [$title]);
            fputcsv($out, ['Period', $filters['label']]);
            fputcsv($out, []);

            if (! empty($data['kpis'])) {
                fputcsv($out, ['KPI', 'Value', 'Previous', 'Change %', 'Target', 'Unit', 'Formula']);
                foreach ($data['kpis'] as $kpi) {
                    fputcsv($out, [
                        $kpi['label'],
                        $kpi['value'],
                        $kpi['previous'],
                        $kpi['change'],
                        $kpi['target'],
                        $kpi['unit'],
                        $kpi['formula'],
                    ]);
                }
                fputcsv($out, []);
            }

            foreach (['rows', 'table', 'history', 'findings', 'upcoming', 'high_risk', 'extreme_risk', 'overdue_treatment', 'expiring_permits', 'non_compliance'] as $section) {
                if (empty($data[$section]) || ! is_array($data[$section])) {
                    continue;
                }
                $first = $data[$section][0] ?? null;
                if (! is_array($first)) {
                    continue;
                }
                fputcsv($out, [ucwords(str_replace('_', ' ', $section))]);
                fputcsv($out, array_keys($first));
                foreach ($data[$section] as $row) {
                    fputcsv($out, array_values($row));
                }
                fputcsv($out, []);
            }

            if (! empty($data['totals'])) {
                fputcsv($out, ['Environmental Metric', 'Value', 'Unit']);
                foreach ($data['totals'] as $metric => $row) {
                    fputcsv($out, [$metric, $row['value'] ?? 0, $row['unit'] ?? '']);
                }
                fputcsv($out, []);
            }

            if (! empty($data['cards'])) {
                fputcsv($out, ['Site', 'Code', 'Company', 'Incidents', 'Near Miss', 'Inspection Score', 'Open Actions', 'Training Completion', 'High Risk']);
                foreach ($data['cards'] as $card) {
                    fputcsv($out, [
                        $card['name'], $card['code'], $card['company'], $card['incidents'], $card['near_miss'],
                        $card['inspection_score'], $card['open_actions'], $card['training_completion'], $card['high_risk'],
                    ]);
                }
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
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
            'safety' => 'Safety Performance Report',
            'incidents' => 'Incident & Near Miss Report',
            'inspections' => 'Inspection Summary',
            'audit' => 'Audit Finding Report',
            'training' => 'Training Compliance Report',
            'environment' => 'Environmental Performance Report',
            'risk' => 'Risk Assessment Summary',
            'actions' => 'Corrective Action Report',
            'sites' => 'Site Performance Comparison',
            default => 'HSE Executive Monthly Report',
        };
    }
}
