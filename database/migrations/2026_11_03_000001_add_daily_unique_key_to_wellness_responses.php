<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wellness_responses', function (Blueprint $table) {
            $table->string('nik_normalized', 50)->nullable()->after('nik');
            $table->date('submission_date')->nullable()->after('nik_normalized');
        });

        // Preserve historical submissions, including any duplicates already
        // present. Keep the first row as the daily uniqueness anchor; leave
        // subsequent legacy duplicates unkeyed so the migration stays safe.
        $seen = [];
        DB::table('wellness_responses')
            ->select(['id', 'nik', 'created_at'])
            ->orderBy('id')
            ->chunkById(500, function ($responses) use (&$seen): void {
                foreach ($responses as $response) {
                    $date = $response->created_at
                        ? substr((string) $response->created_at, 0, 10)
                        : null;
                    $normalizedNik = trim((string) $response->nik);
                    $normalizedNik = (string) preg_replace('/\s+/u', '', $normalizedNik);
                    $normalizedNik = mb_strtolower($normalizedNik);

                    if (!$date || $normalizedNik === '') {
                        continue;
                    }

                    $key = $date.'|'.$normalizedNik;
                    if (isset($seen[$key])) {
                        continue;
                    }

                    $seen[$key] = true;
                    DB::table('wellness_responses')->where('id', $response->id)->update([
                        'nik_normalized' => $normalizedNik,
                        'submission_date' => $date,
                    ]);
                }
            });

        Schema::table('wellness_responses', function (Blueprint $table) {
            $table->unique(['nik_normalized', 'submission_date'], 'wellness_responses_nik_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('wellness_responses', function (Blueprint $table) {
            $table->dropUnique('wellness_responses_nik_date_unique');
            $table->dropColumn(['nik_normalized', 'submission_date']);
        });
    }
};
