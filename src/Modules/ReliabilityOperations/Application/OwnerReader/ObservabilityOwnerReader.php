<?php

namespace Appart\Modules\ReliabilityOperations\Application\OwnerReader;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsReadStatus;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsScopeKey;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsStream;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ObservabilityReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ObservabilityResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ObservabilityStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

final readonly class ObservabilityOwnerReader implements ObservabilityReaderV1
{
    private const SCOPE = 'platform:primary';

    public function __construct(private ReliabilityOperationsOwnerSource $source) {}

    public function read(ReliabilityOperationsObservedAt $observedAt): ObservabilityResultV1
    {
        $result = $this->source->read(new ReliabilityOperationsScopeKey(self::SCOPE), ReliabilityOperationsStream::Observability, $observedAt);
        $status = match ($result->status) {
            ReliabilityOperationsReadStatus::Found => ObservabilityStatusV1::from($result->revision->decision),
            ReliabilityOperationsReadStatus::Missing => ObservabilityStatusV1::Missing,
            ReliabilityOperationsReadStatus::Corrupted => ObservabilityStatusV1::Corrupted,
            ReliabilityOperationsReadStatus::DependencyUnavailable => ObservabilityStatusV1::DependencyUnavailable,
        };

        return new ObservabilityResultV1($status, $observedAt);
    }
}
