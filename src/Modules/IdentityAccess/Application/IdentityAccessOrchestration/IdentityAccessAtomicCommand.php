<?php

namespace Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class IdentityAccessAtomicCommand
{
    public function __construct(
        public string $intentId,
        public string $intentChecksum,
        public AccountId $accountId,
        public IdentityAccessAtomicOperation $operation,
        public DateTimeImmutable $occurredAt,
    ) {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $intentId) !== 1
            || preg_match('/^[0-9a-f]{64}$/', $intentChecksum) !== 1) {
            throw new InvalidArgumentException('Invalid atomic operation intent.');
        }
    }
}
