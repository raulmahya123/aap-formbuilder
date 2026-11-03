<?php

namespace App\Http\Controllers;

use App\Models\WellnessResponse;
use App\Models\Department;
use App\Models\Site;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WellnessController extends Controller
{
    public function create(): View
    {
        return view('wellness.create', [
            'departments'=>Department::orderBy('name')->get(['id', 'name']),
            'sites'=>Site::orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'nik' => ['required', 'string', 'max:50'],
            'position' => ['required', 'string', 'max:120'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'site_id' => ['required', 'integer', 'exists:sites,id'],
            'shift' => ['required', 'in:pagi,malam'],
            'sleep_before_shift' => ['required', 'in:lebih_6_jam,6_jam,kurang_6_jam'],
            'sleep_quality' => ['required', 'in:baik,cukup,buruk'],
            'sleep_quality_reason' => ['exclude_unless:sleep_quality,buruk', 'required', 'string', 'max:2000'],
            'heavy_drowsiness' => ['required', 'in:ya,tidak'],
            'health_complaint' => ['required', 'in:tidak,ya'],
            'health_complaint_details' => ['exclude_unless:health_complaint,ya', 'required', 'string', 'max:2000'],
            'drowsy_medication' => ['required', 'in:ya,tidak'],
            'drowsy_medication_details' => ['exclude_unless:drowsy_medication,ya', 'required', 'string', 'max:2000'],
            'work_readiness' => ['required', 'in:ya,tidak,off_day'],
        ], [
            'sleep_quality.required' => 'Pilih kualitas tidur Anda.',
            'department_id.required' => 'Pilih departemen Anda.',
            'department_id.exists' => 'Departemen yang dipilih tidak tersedia. Pilih kembali.',
            'site_id.required' => 'Pilih site Anda.',
            'site_id.exists' => 'Site yang dipilih tidak tersedia. Pilih kembali.',
            'drowsy_medication_details.required' => 'Tuliskan nama obat yang sedang Anda konsumsi.',
            'health_complaint_details.required' => 'Jelaskan keluhan atau gejala yang Anda rasakan.',
            'sleep_quality_reason.required' => 'Sertakan alasan kualitas tidur buruk.',
            'heavy_drowsiness.required' => 'Pilih apakah Anda mengantuk berat atau sulit berkonsentrasi.',
        ]);

        // A public link has no authenticated user identity. Use the required
        // employee NIK and Jakarta calendar date as the submission key instead
        // of the IP address (many employees may share one office IP).
        $normalizedNik = $this->normalizeNik($validated['nik']);
        $submissionDate = now(config('app.timezone', 'Asia/Jakarta'))->toDateString();

        if (WellnessResponse::query()
            ->where('nik_normalized', $normalizedNik)
            ->where('submission_date', $submissionDate)
            ->exists()) {
            return back()->withInput()->withErrors([
                'nik' => 'NIK ini sudah mengirim Health Assessment hari ini. Setiap karyawan hanya dapat mengirim satu kali per hari.',
            ]);
        }

        $department = Department::findOrFail($validated['department_id']);
        $site = Site::findOrFail($validated['site_id']);
        // Preserve the existing site-code representation for legacy reports and filters.
        $validated['department'] = $department->name;
        $validated['site'] = $site->code ?: $site->name;
        $validated['nik_normalized'] = $normalizedNik;
        $validated['submission_date'] = $submissionDate;

        try {
            WellnessResponse::create($validated);
        } catch (QueryException $exception) {
            // The unique database key is the final guard against concurrent
            // duplicate requests arriving after the preflight check above.
            $message = strtolower($exception->getMessage());
            $isDuplicateSubmission = in_array((string) $exception->getCode(), ['23000', '23505'], true)
                && (str_contains($message, 'wellness_responses_nik_date_unique')
                    || (str_contains($message, 'nik_normalized') && str_contains($message, 'submission_date')));

            if (!$isDuplicateSubmission) {
                throw $exception;
            }

            return back()->withInput()->withErrors([
                'nik' => 'NIK ini sudah mengirim Health Assessment hari ini. Setiap karyawan hanya dapat mengirim satu kali per hari.',
            ]);
        }

        return redirect()->route('wellness.create')->with('success', 'Terima kasih, Health Assesment Checklist Anda sudah tersimpan.');
    }

    private function normalizeNik(string $nik): string
    {
        return mb_strtolower((string) preg_replace('/\s+/u', '', trim($nik)));
    }
}
