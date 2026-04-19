<?php

namespace App\Services;

class VersionComparisonService
{
    public function normalize(?string $version): string
    {
        $value = trim((string) $version);

        if ($value === '') {
            return '0.0.0';
        }

        return ltrim(strtolower($value), 'v');
    }

    public function compare(?string $left, ?string $right): int
    {
        $leftNormalized = $this->normalize($left);
        $rightNormalized = $this->normalize($right);

        return version_compare($leftNormalized, $rightNormalized);
    }

    public function isOutdated(?string $current, ?string $latest): bool
    {
        return $this->compare($current, $latest) < 0;
    }
}
