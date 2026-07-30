<?php

namespace Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover;

final readonly class ProfileClaimsSeedSourceState
{
    public function __construct(
        public string $accountId,
        public string $email,
        public string $phone,
        public string $name,
        public int $historicalVersion,
    ) {}
}
