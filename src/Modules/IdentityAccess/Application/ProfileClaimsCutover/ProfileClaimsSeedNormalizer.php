<?php

namespace Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover;

final readonly class ProfileClaimsSeedNormalizer
{
    public const string VERSION = 'iam-profile-v1';

    public function name(string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
    }

    public function email(string $value): string
    {
        return mb_strtolower(trim($value), 'UTF-8');
    }

    public function phone(string $value): string
    {
        $prefix = str_starts_with(trim($value), '+') ? '+' : '';

        return $prefix.(preg_replace('/\D+/', '', $value) ?? '');
    }
}
