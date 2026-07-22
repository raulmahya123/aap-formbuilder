<?php
declare(strict_types=1);

namespace App\Support;

use Carbon\Carbon;
use DateTimeInterface;

class ShiftWindow
{
    public static function detect(?string $forDate = null, ?DateTimeInterface $now = null): array
    {
        $tz      = config('shifts.timezone', 'Asia/Jakarta');
        $windows = config('shifts.windows', [
            1 => ['start' => '06:00', 'end' => '10:00'],
            2 => ['start' => '01:00', 'end' => '03:00'],
        ]);
        $grace   = (int) config('shifts.grace_minutes', 0);

        $now = $now ? Carbon::instance($now)->tz($tz) : Carbon::now($tz);
        $day = $forDate ? Carbon::parse($forDate, $tz) : $now->copy();

        $ranges = [];
        foreach ($windows as $no => $w) {
            $s = Carbon::parse($day->toDateString()." ".$w['start'], $tz);
            $e = Carbon::parse($day->toDateString()." ".$w['end'],   $tz);

            if ($e->lt($s)) {
                $e->addDay();
                if ($now->lt(Carbon::parse($day->toDateString()." ".$w['end'], $tz))) {
                    $s->subDay();
                }
            }

            if ($grace > 0) {
                $s = $s->copy()->subMinutes($grace);
                $e = $e->copy()->addMinutes($grace);
            }

            $ranges[(int)$no] = [$s, $e];
        }

        $within = false;
        $shift  = null;
        foreach ($ranges as $no => [$s, $e]) {
            if ($now->between($s, $e, true)) {
                $within = true;
                $shift  = (int)$no;
                break;
            }
        }

        $closestShift = null;
        $minDiff      = null;
        foreach ($ranges as $no => [$s, $e]) {
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
            'shift'         => $shift,
            'closest_shift' => $closestShift,
            'now'           => $now,
            'ranges'        => $ranges,
            'is_late'       => !$within,
        ];
    }
}
