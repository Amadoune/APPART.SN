<?php

namespace Appart\Modules\ReliabilityOperations\Application\Runtime;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsReadStatus;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsScopeKey;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsStream;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;
use DateTimeImmutable;
use Throwable;

final readonly class DeterministicReliabilityOperationsRuntimeAvailabilityPolicy implements ReliabilityOperationsRuntimeAvailabilityPolicy
{
    private const PROBE_SCOPE = 'runtime/reliability-operations-owner-source';

    public function __construct(private ReliabilityOperationsOwnerSource $source) {}

    public function inspect(): ReliabilityOperationsRuntimeAvailability
    {
        try {
            $scope = new ReliabilityOperationsScopeKey(self::PROBE_SCOPE);
            $observedAt = new ReliabilityOperationsObservedAt(new DateTimeImmutable('9999-12-31T23:59:59.999999Z'));
            $corrupted = false;
            foreach (ReliabilityOperationsStream::cases() as $stream) {
                $status = $this->source->read($scope, $stream, $observedAt)->status;
                if ($status === ReliabilityOperationsReadStatus::DependencyUnavailable) {
                    return ReliabilityOperationsRuntimeAvailability::DependencyUnavailable;
                }
                $corrupted = $corrupted || $status === ReliabilityOperationsReadStatus::Corrupted;
            }

            return $corrupted ? ReliabilityOperationsRuntimeAvailability::Corrupted : ReliabilityOperationsRuntimeAvailability::Available;
        } catch (Throwable) {
            return ReliabilityOperationsRuntimeAvailability::DependencyUnavailable;
        }
    }
}
