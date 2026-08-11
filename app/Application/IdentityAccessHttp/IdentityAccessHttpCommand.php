<?php

namespace App\Application\IdentityAccessHttp;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;

final readonly class IdentityAccessHttpCommand
{
    /** @param array<string, bool|int|string|null> $input */
    public function __construct(
        public IdentityAccessHttpOperation $operation,
        public string $intentId,
        public array $input,
        public ?AccountId $authenticatedAccount,
        public DateTimeImmutable $requestedAt,
        public ?AuthenticatedSessionContext $authenticatedSession = null,
    ) {}
}
