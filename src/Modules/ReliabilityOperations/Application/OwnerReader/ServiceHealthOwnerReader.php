<?php

namespace Appart\Modules\ReliabilityOperations\Application\OwnerReader;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsReadStatus;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsScopeKey;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsStream;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ServiceHealthReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ServiceHealthResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ServiceHealthStatusV1;

final readonly class ServiceHealthOwnerReader implements ServiceHealthReaderV1
{
    private const SCOPE = 'platform:primary';

    public function __construct(private ReliabilityOperationsOwnerSource $source) {}

    public function read(ReliabilityOperationsObservedAt $observedAt): ServiceHealthResultV1
    {
        $result = $this->source->read(new ReliabilityOperationsScopeKey(self::SCOPE), ReliabilityOperationsStream::ServiceHealth, $observedAt);
        $status = match ($result->status) {
            ReliabilityOperationsReadStatus::Found => ServiceHealthStatusV1::from($result->revision->decision),
            ReliabilityOperationsReadStatus::Missing => ServiceHealthStatusV1::Missing,
            ReliabilityOperationsReadStatus::Corrupted => ServiceHealthStatusV1::Corrupted,
            ReliabilityOperationsReadStatus::DependencyUnavailable => ServiceHealthStatusV1::DependencyUnavailable,
        };

        return new ServiceHealthResultV1($status, $observedAt);
    }
}
