<?php

namespace Appart\Modules\ReliabilityOperations\Application\OwnerReader;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsReadStatus;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsScopeKey;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsStream;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\AlertingResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\AlertingStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\AlertingReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

final readonly class AlertingOwnerReader implements AlertingReaderV1
{
    private const SCOPE = 'platform:primary';

    public function __construct(private ReliabilityOperationsOwnerSource $source) {}

    public function read(ReliabilityOperationsObservedAt $observedAt): AlertingResultV1
    {
        $result = $this->source->read(new ReliabilityOperationsScopeKey(self::SCOPE), ReliabilityOperationsStream::Alerting, $observedAt);
        $status = match ($result->status) {
            ReliabilityOperationsReadStatus::Found => AlertingStatusV1::from($result->revision->decision),
            ReliabilityOperationsReadStatus::Missing => AlertingStatusV1::Missing,
            ReliabilityOperationsReadStatus::Corrupted => AlertingStatusV1::Corrupted,
            ReliabilityOperationsReadStatus::DependencyUnavailable => AlertingStatusV1::DependencyUnavailable,
        };

        return new AlertingResultV1($status, $observedAt);
    }
}
