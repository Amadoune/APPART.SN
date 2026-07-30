<?php

namespace Appart\Modules\IdentityAccess\Infrastructure\Persistence\HistoricalAccount;

use Appart\Modules\IdentityAccess\Application\ProfileClaimsCutover\ProfileClaimsSeedSourceState;

final readonly class HistoricalAccountProfileClaimsSeedMapper
{
    public function map(HistoricalAccountPersistenceSnapshotV1 $snapshot): ProfileClaimsSeedSourceState
    {
        return new ProfileClaimsSeedSourceState(
            $snapshot->accountId->value,
            $snapshot->email->value,
            $snapshot->phone->value,
            $snapshot->name->value,
            $snapshot->historicalVersion,
        );
    }
}
