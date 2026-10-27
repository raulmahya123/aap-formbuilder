<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TableExportController extends Controller
{
    public function __invoke(Request $request)
    {
        // Only the visible table is supplied; this endpoint never queries other users' records.
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'columns' => ['required', 'array', 'min:1', 'max:40'],
            'columns.*' => ['required', 'string', 'max:200'],
            'rows' => ['required', 'array', 'min:1', 'max:100'],
            'rows.*' => ['required', 'array', 'max:40'],
            'rows.*.*' => ['nullable', 'string', 'max:4000'],
        ]);
        foreach ($data['rows'] as $row) {
            abort_unless(count($row) === count($data['columns']), 422, 'Kolom tabel tidak sesuai.');
        }

        return Pdf::loadView('exports.table', $data + ['exportedAt' => now()])
            ->setPaper('a4', count($data['columns']) > 5 ? 'landscape' : 'portrait')
            ->setOptions(['isRemoteEnabled' => false, 'isPhpEnabled' => false, 'defaultFont' => 'DejaVu Sans'])
            ->download((Str::slug($data['title']) ?: 'mandala') . '-' . now()->format('Ymd-His') . '.pdf');
    }
}
