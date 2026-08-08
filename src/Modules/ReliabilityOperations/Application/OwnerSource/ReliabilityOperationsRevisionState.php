<?php

namespace Appart\Modules\ReliabilityOperations\Application\OwnerSource;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final readonly class ReliabilityOperationsRevisionState
{
    public string $scopeKey;

    public DateTimeImmutable $effectiveAt;

    public DateTimeImmutable $recordedAt;

    public function __construct(ReliabilityOperationsScopeKey|string $scope, public ReliabilityOperationsStream $stream, public int $revision, public string $decision, DateTimeImmutable $effectiveAt, DateTimeImmutable $recordedAt)
    {
        if ($revision < 1 || ! in_array($decision, self::decisions($stream), true)) {
            throw new InvalidArgumentException('Reliability Operations revision is invalid.');
        }
        $this->scopeKey = ($scope instanceof ReliabilityOperationsScopeKey ? $scope : new ReliabilityOperationsScopeKey($scope))->value;
        $utc = new DateTimeZone('UTC');
        $this->effectiveAt = $effectiveAt->setTimezone($utc);
        $this->recordedAt = $recordedAt->setTimezone($utc);
        if ($this->recordedAt < $this->effectiveAt) {
            throw new InvalidArgumentException('Reliability Operations chronology is invalid.');
        }
    }

    /** @return non-empty-list<string> */
    private static function decisions(ReliabilityOperationsStream $stream): array
    {
        return match ($stream) {
            ReliabilityOperationsStream::Observability => ['available', 'degraded', 'missing', 'dependency_unavailable'],
            ReliabilityOperationsStream::ServiceHealth => ['healthy', 'degraded', 'unavailable', 'dependency_unavailable'],
            ReliabilityOperationsStream::Alerting => ['ready', 'degraded', 'unavailable', 'dependency_unavailable'],
            ReliabilityOperationsStream::Continuity => ['ready', 'at_risk', 'blocked', 'dependency_unavailable'],
            ReliabilityOperationsStream::MaintenanceOperations => ['ready', 'degraded', 'blocked', 'dependency_unavailable'],
            ReliabilityOperationsStream::CapacityPlanning => ['sufficient', 'at_risk', 'exhausted', 'dependency_unavailable'],
            ReliabilityOperationsStream::OperationalReadiness => ['ready', 'at_risk', 'blocked', 'dependency_unavailable'],
        };
    }
}
