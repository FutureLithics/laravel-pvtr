<?php

namespace App\Support;

class TextNormalizer
{
    public static function string(mixed $value): string
    {
        return trim((string) $value);
    }

    public static function email(mixed $value): ?string
    {
        $email = strtolower(self::string($value));

        return $email === '' ? null : $email;
    }
}
