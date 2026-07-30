<?php

namespace Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover\Contract;

interface ProfileClaimsSeedProtector
{
    public function protect(string $normalizedValue): string;

    public function fingerprint(string $normalizedValue): string;
}
