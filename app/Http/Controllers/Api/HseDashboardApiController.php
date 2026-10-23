<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Hse\HseDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HseDashboardApiController extends Controller
{
    public function __construct(private readonly HseDashboardService $service) {}

    public function overview(Request $request): JsonResponse
    {
        return $this->respond($request, 'overview');
    }

    public function safety(Request $request): JsonResponse
    {
        return $this->respond($request, 'safety');
    }

    public function incidents(Request $request): JsonResponse
    {
        return $this->respond($request, 'incidents');
    }

    public function inspections(Request $request): JsonResponse
    {
        return $this->respond($request, 'inspections');
    }

    public function training(Request $request): JsonResponse
    {
        return $this->respond($request, 'training');
    }

    public function environment(Request $request): JsonResponse
    {
        return $this->respond($request, 'environment');
    }

    public function risk(Request $request): JsonResponse
    {
        return $this->respond($request, 'riskCompliance');
    }

    public function actions(Request $request): JsonResponse
    {
        return $this->respond($request, 'actions');
    }

    public function sites(Request $request): JsonResponse
    {
        return $this->respond($request, 'sites');
    }

    public function reports(Request $request): JsonResponse
    {
        return $this->respond($request, 'reports');
    }

    private function respond(Request $request, string $method): JsonResponse
    {
        $filters = $this->service->filters($request);
        $data = $this->service->{$method}($filters);

        return response()->json([
            'data' => $data,
            'meta' => [
                'period' => $filters['period'],
                'label' => $filters['label'],
                'from' => $filters['from']->toIso8601String(),
                'to' => $filters['to']->toIso8601String(),
                'site_id' => $filters['site_id'],
                'company_id' => $filters['company_id'],
                'department_id' => $filters['department_id'],
            ],
        ]);
    }
}
