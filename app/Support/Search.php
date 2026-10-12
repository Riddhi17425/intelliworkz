<?php

namespace App\Support;

class Search
{
    /** "%term%" for a LIKE query with the user's own % _ \ characters escaped. */
    public static function like(string $term): string
    {
        $term = trim($term);
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';
    }
}
