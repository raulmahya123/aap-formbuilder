<?php

namespace App\Support;

final class Listing
{
    public static function perPage(): int
    {
        $value = (int) request()->input('per_page', request()->input('per', request()->input('perPage', 10)));
        return in_array($value, [10, 25, 50, 100], true) ? $value : 10;
    }
}
