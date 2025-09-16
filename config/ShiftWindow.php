<?php

namespace App\Support;

use Carbon\Carbon;

class ShiftWindow
{
    public static function detect(?string $forDate = null, ?\DateTimeInterface $now = null): array
    {
        $tz      = config('shifts.timezone','Asia/Jakarta');
        $windows = config('shifts.windows',[
            1 => ['start' => '06:00','end' => '10:00'],
            2 => ['start' => '01:00','end' => '03:00'],
        ]);
        $grace   = (int) config('shifts.grace_minutes', 0);

        $now = $now ? Carbon::instance($now)->tz($tz) : Carbon::now($tz);
        $day = $forDate ? Carbon::parse($forDate, $tz) : $now->copy();

        // build windows
        $ranges = [];
        foreach ($windows as $no => $w) {
            $s = Carbon::parse($day->toDateString().' '.$w['start'], $tz);
            $e = Carbon::parse($day->toDateString().' '.$w['end'],   $tz);
            if ($e->lessThan($s)) $e->addDay();
            if ($grace > 0) { $s = $s->copy()->subMinutes($grace); $e = $e->copy()->addMinutes($grace); }
            $ranges[$no] = [$s,$e];
        }

        // within?
        $within = false; $shift = null;
        foreach ($ranges as $no => [$s,$e]) {
            if ($now->between($s,$e)) { $within = true; $shift = $no; break; }
        }

        // closest shift (biar tetap KEISI walau telat)
        $closestShift = null; $minDiff = null;
        foreach ($ranges as $no => [$s,$e]) {
            // pakai jarak ke tepi window
            $d = min(abs($now->diffInMinutes($s,false)), abs($now->diffInMinutes($e,false)));
            $d = abs($d);
            if ($minDiff === null || $d < $minDiff) { $minDiff = $d; $closestShift = $no; }
        }

        return [
            'within'        => $within,
            'shift'         => $shift,          // 1/2/null
            'closest_shift' => $closestShift,   // selalu 1/2
            'now'           => $now,
            'ranges'        => $ranges,
            'is_late'       => !$within,
        ];
    }
}
