<?php

namespace Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover;

final readonly class ProfileClaimsCutoverResult
{
    /** @param list<string> $divergences */
    public function __construct(
        public ProfileClaimsCutoverStatus $status,
        public int $profiles,
        public int $claims,
        public array $divergences = [],
    ) {}
}
