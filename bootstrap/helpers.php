<?php

use Illuminate\Support\Str;


if (!function_exists('generateProductCode')) {
    function generateProductCode() {
        return Str::random(6);
    }
}
