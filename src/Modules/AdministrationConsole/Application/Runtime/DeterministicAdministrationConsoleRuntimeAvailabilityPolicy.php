<?php

namespace Appart\Modules\AdministrationConsole\Application\Runtime;

use Appart\Modules\AdministrationConsole\Application\OwnerSource\AdministrationConsoleOwnerSource;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationAuditStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationObservedAt;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationOperatorStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationQueueStatusV1;
use Appart\Modules\AdministrationConsole\Application\PublicRead\AdministrationSubjectKey;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicAdministrationConsoleRuntimeAvailabilityPolicy implements AdministrationConsoleRuntimeAvailabilityPolicy
{
    private const PROBE_SUBJECT = 'runtime/administration-console-owner-source';

    public function __construct(private AdministrationConsoleOwnerSource $source) {}

    public function inspect(): AdministrationConsoleRuntimeAvailability
    {
        try {
            $subject = new AdministrationSubjectKey(self::PROBE_SUBJECT);
            $observedAt = new AdministrationObservedAt(new DateTimeImmutable('9999-12-31T23:59:59.999999Z'));
            $operator = $this->source->readOperator($subject, $observedAt);
            $queue = $this->source->readQueue($subject, $observedAt);
            $audit = $this->source->readAudit($subject, $observedAt);

            if ($operator->status === AdministrationOperatorStatusV1::DependencyUnavailable
                || $queue->status === AdministrationQueueStatusV1::DependencyUnavailable
                || $audit->status === AdministrationAuditStatusV1::DependencyUnavailable) {
                return AdministrationConsoleRuntimeAvailability::DependencyUnavailable;
            }
            if ($operator->status === AdministrationOperatorStatusV1::Corrupted
                || $queue->status === AdministrationQueueStatusV1::Corrupted
                || $audit->status === AdministrationAuditStatusV1::Corrupted) {
                return AdministrationConsoleRuntimeAvailability::Corrupted;
            }

            return AdministrationConsoleRuntimeAvailability::Available;
        } catch (Throwable) {
            return AdministrationConsoleRuntimeAvailability::DependencyUnavailable;
        }
    }
}
