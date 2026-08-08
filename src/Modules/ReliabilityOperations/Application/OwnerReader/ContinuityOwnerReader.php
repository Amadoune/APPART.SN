<?php

namespace Appart\Modules\ReliabilityOperations\Application\OwnerReader;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsReadStatus;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsScopeKey;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsStream;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ContinuityResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ContinuityStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\ContinuityReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

final readonly class ContinuityOwnerReader implements ContinuityReaderV1
{
    private const SCOPE = 'platform:primary';

    public function __construct(private ReliabilityOperationsOwnerSource $source) {}

    public function read(ReliabilityOperationsObservedAt $observedAt): ContinuityResultV1
    {
        $result = $this->source->read(new ReliabilityOperationsScopeKey(self::SCOPE), ReliabilityOperationsStream::Continuity, $observedAt);
        $status = match ($result->status) {
            ReliabilityOperationsReadStatus::Found => ContinuityStatusV1::from($result->revision->decision),
            ReliabilityOperationsReadStatus::Missing => ContinuityStatusV1::Missing,
            ReliabilityOperationsReadStatus::Corrupted => ContinuityStatusV1::Corrupted,
            ReliabilityOperationsReadStatus::DependencyUnavailable => ContinuityStatusV1::DependencyUnavailable,
        };

        return new ContinuityResultV1($status, $observedAt);
    }
}
