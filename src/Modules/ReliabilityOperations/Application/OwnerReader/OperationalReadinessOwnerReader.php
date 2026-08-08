<?php

namespace Appart\Modules\ReliabilityOperations\Application\OwnerReader;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsReadStatus;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsScopeKey;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsStream;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\OperationalReadinessReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\OperationalReadinessResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\OperationalReadinessStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

final readonly class OperationalReadinessOwnerReader implements OperationalReadinessReaderV1
{
    private const SCOPE = 'platform:primary';

    public function __construct(private ReliabilityOperationsOwnerSource $source) {}

    public function read(ReliabilityOperationsObservedAt $observedAt): OperationalReadinessResultV1
    {
        $result = $this->source->read(new ReliabilityOperationsScopeKey(self::SCOPE), ReliabilityOperationsStream::OperationalReadiness, $observedAt);
        $status = match ($result->status) {
            ReliabilityOperationsReadStatus::Found => OperationalReadinessStatusV1::from($result->revision->decision),
            ReliabilityOperationsReadStatus::Missing => OperationalReadinessStatusV1::Missing,
            ReliabilityOperationsReadStatus::Corrupted => OperationalReadinessStatusV1::Corrupted,
            ReliabilityOperationsReadStatus::DependencyUnavailable => OperationalReadinessStatusV1::DependencyUnavailable,
        };

        return new OperationalReadinessResultV1($status, $observedAt);
    }
}
