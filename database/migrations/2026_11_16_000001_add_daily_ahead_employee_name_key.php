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
            $table->char('employee_name_key', 64)->nullable()->after('nik_normalized');
        });

        $seen = [];
        DB::table('wellness_responses')
            ->select(['id', 'name', 'created_at', 'submission_date'])
            ->orderBy('id')
            ->chunkById(500, function ($responses) use (&$seen): void {
                foreach ($responses as $response) {
                    $date = $response->submission_date
                        ?: ($response->created_at ? substr((string) $response->created_at, 0, 10) : null);
                    $normalizedName = mb_strtolower((string) preg_replace('/\s+/u', ' ', trim((string) $response->name)));

                    if (!$date || $normalizedName === '') {
                        continue;
                    }

                    $nameKey = hash('sha256', $normalizedName);
                    $uniqueKey = $date.'|'.$nameKey;
                    if (isset($seen[$uniqueKey])) {
                        continue;
                    }

                    $seen[$uniqueKey] = true;
                    DB::table('wellness_responses')->where('id', $response->id)->update([
                        'employee_name_key' => $nameKey,
                        'submission_date' => $date,
                    ]);
                }
            });

        Schema::table('wellness_responses', function (Blueprint $table) {
            $table->unique(['employee_name_key', 'submission_date'], 'wellness_responses_employee_name_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('wellness_responses', function (Blueprint $table) {
            $table->dropUnique('wellness_responses_employee_name_date_unique');
            $table->dropColumn('employee_name_key');
        });
    }
};
