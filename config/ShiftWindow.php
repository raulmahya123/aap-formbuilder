<?php
declare(strict_types=1);

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;

/**
 * ShiftWindow
 * - Hitung window shift harian berdasar konfigurasi di config/shifts.php
 * - Menentukan apakah "sekarang" berada di salah satu window (within),
 *   shift aktif (shift), shift terdekat (closest_shift), dan flag keterlambatan (is_late).
 *
 * @return array{
 *   within: bool,
 *   shift: int|null,
 *   closest_shift: int|null,
 *   now: \Carbon\Carbon,
 *   ranges: array<int, array{0:\Carbon\Carbon,1:\Carbon\Carbon}>,
 *   is_late: bool
 * }
 */
class ShiftWindow
{
    /**
     * @param  string|null $forDate  Tanggal acuan (YYYY-MM-DD). Null = pakai hari ini.
     * @param  DateTimeInterface|null $now  Waktu “sekarang” injeksi (untuk testing). Null = now().
     */
    public static function detect(?string $forDate = null, ?DateTimeInterface $now = null): array
    {
        $tz      = config('shifts.timezone', 'Asia/Jakarta');
        $windows = config('shifts.windows', [
            // Contoh default; override di config/shifts.php
            1 => ['start' => '06:00', 'end' => '10:00'],
            2 => ['start' => '01:00', 'end' => '03:00'],
        ]);
        $grace   = (int) config('shifts.grace_minutes', 0);

        $now = $now ? Carbon::instance($now)->tz($tz) : Carbon::now($tz);
        $day = $forDate ? Carbon::parse($forDate, $tz) : $now->copy();

        // Build window range per shift
        $ranges = [];
        foreach ($windows as $no => $w) {
            $s = Carbon::parse($day->toDateString()." ".$w['start'], $tz);
            $e = Carbon::parse($day->toDateString()." ".$w['end'],   $tz);

            // Jika end < start → lintas hari (misal 22:00–06:00)
            if ($e->lt($s)) {
                $e->addDay();
                // Jika now berada setelah tengah malam namun sebelum end,
                // start perlu dimundurkan 1 hari (agar range benar)
                if ($now->lt(Carbon::parse($day->toDateString()." ".$w['end'], $tz))) {
                    $s->subDay();
                }
            }

            // Tambahkan grace di tepi window (optional)
            if ($grace > 0) {
                $s = $s->copy()->subMinutes($grace);
                $e = $e->copy()->addMinutes($grace);
            }

            $ranges[(int)$no] = [$s, $e];
        }

        // Apakah now berada di salah satu window?
        $within = false;
        $shift  = null;
        foreach ($ranges as $no => [$s, $e]) {
            if ($now->between($s, $e, true)) {
                $within = true;
                $shift  = (int)$no;
                break;
            }
        }

        // Cari shift terdekat (fallback jika tidak within)
        $closestShift = null;
        $minDiff      = null;
        foreach ($ranges as $no => [$s, $e]) {
            // Jarak minimum ke tepi window (menit)
            $ds = abs($now->diffInMinutes($s, false));
            $de = abs($now->diffInMinutes($e, false));
            $d  = min($ds, $de);

            if ($minDiff === null || $d < $minDiff) {
                $minDiff      = $d;
                $closestShift = (int)$no;
            }
        }

        return [
            'within'        => $within,
            'shift'         => $shift,          // 1/2/…/null
            'closest_shift' => $closestShift,   // selalu int kalau ada windows
            'now'           => $now,
            'ranges'        => $ranges,         // [no => [Carbon $start, Carbon $end]]
            'is_late'       => !$within,        // simple rule: di luar window = telat
        ];
    }
}
