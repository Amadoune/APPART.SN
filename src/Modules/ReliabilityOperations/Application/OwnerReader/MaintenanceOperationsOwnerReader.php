<?php

namespace Appart\Modules\ReliabilityOperations\Application\OwnerReader;

use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsOwnerSource;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsReadStatus;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsScopeKey;
use Appart\Modules\ReliabilityOperations\Application\OwnerSource\ReliabilityOperationsStream;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\Contract\MaintenanceOperationsReaderV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\MaintenanceOperationsResultV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\MaintenanceOperationsStatusV1;
use Appart\Modules\ReliabilityOperations\Application\PublicRead\ReliabilityOperationsObservedAt;

final readonly class MaintenanceOperationsOwnerReader implements MaintenanceOperationsReaderV1
{
    private const SCOPE = 'platform:primary';

    public function __construct(private ReliabilityOperationsOwnerSource $source) {}

    public function read(ReliabilityOperationsObservedAt $observedAt): MaintenanceOperationsResultV1
    {
        $result = $this->source->read(new ReliabilityOperationsScopeKey(self::SCOPE), ReliabilityOperationsStream::MaintenanceOperations, $observedAt);
        $status = match ($result->status) {
            ReliabilityOperationsReadStatus::Found => MaintenanceOperationsStatusV1::from($result->revision->decision),
            ReliabilityOperationsReadStatus::Missing => MaintenanceOperationsStatusV1::Missing,
            ReliabilityOperationsReadStatus::Corrupted => MaintenanceOperationsStatusV1::Corrupted,
            ReliabilityOperationsReadStatus::DependencyUnavailable => MaintenanceOperationsStatusV1::DependencyUnavailable,
        };

        return new MaintenanceOperationsResultV1($status, $observedAt);
    }
}
