<?php

namespace App\Application\IdentityAccessEventIntegration;

use Appart\Modules\IdentityAccess\Application\IdentityAccessEvent\IdentityAccessEventV1;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicCommand;
use Appart\Modules\IdentityAccess\Application\IdentityAccessOrchestration\IdentityAccessAtomicWorkResult;
use Closure;
use InvalidArgumentException;

final readonly class IdentityAccessAtomicEventRequest
{
    /**
     * @param  list<IdentityAccessEventV1>  $events
     * @param  Closure(): IdentityAccessAtomicWorkResult  $work
     */
    public function __construct(
        public IdentityAccessAtomicCommand $command,
        public array $events,
        public Closure $work,
    ) {
        foreach ($events as $event) {
            if ($event->accountId->value !== $command->accountId->value
                || $event->causationId !== $command->intentId) {
                throw new InvalidArgumentException('IAM event evidence does not match the atomic command.');
            }
        }
    }
}
