<?php

namespace Appart\Modules\ReliabilityOperations\Application\OwnerReader;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsReadStatus;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsScopeKey;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsStream;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\CapacityPlanningResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\CapacityPlanningStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\CapacityPlanningReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

final readonly class CapacityPlanningOwnerReader implements CapacityPlanningReaderV1
{
    private const SCOPE = 'platform:primary';

    public function __construct(private ReliabilityOperationsOwnerSource $source) {}

    public function read(ReliabilityOperationsObservedAt $observedAt): CapacityPlanningResultV1
    {
        $result = $this->source->read(new ReliabilityOperationsScopeKey(self::SCOPE), ReliabilityOperationsStream::CapacityPlanning, $observedAt);
        $status = match ($result->status) {
            ReliabilityOperationsReadStatus::Found => CapacityPlanningStatusV1::from($result->revision->decision),
            ReliabilityOperationsReadStatus::Missing => CapacityPlanningStatusV1::Missing,
            ReliabilityOperationsReadStatus::Corrupted => CapacityPlanningStatusV1::Corrupted,
            ReliabilityOperationsReadStatus::DependencyUnavailable => CapacityPlanningStatusV1::DependencyUnavailable,
        };

        return new CapacityPlanningResultV1($status, $observedAt);
    }
}
