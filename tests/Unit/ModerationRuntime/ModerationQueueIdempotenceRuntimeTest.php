<?php

namespace Tests\Unit\ModerationRuntime;

use App\Application\ModerationRuntime\DeterministicModerationQueueRuntimeV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationQueueStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationQueueClaimResult;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationQueueIdempotenceRuntimeTest extends TestCase
{
    #[Test]
    public function runtime_transports_intent_identity_and_checksum_unchanged(): void
    {
        $claimedAt = new DateTimeImmutable('2026-07-30T12:00:00+00:00');
        $expiresAt = new DateTimeImmutable('2026-07-30T12:05:00+00:00');
        $checksum = hash('sha256', 'claim-v1');
        $store = $this->createMock(ModerationQueueStore::class);
        $store->expects(self::once())->method('claim')->with(
            '53a10000-0000-4000-8000-000000000001',
            '53a10000-0000-4000-8000-000000000002',
            '53a10000-0000-4000-8000-000000000003',
            $expiresAt,
            $claimedAt,
            '53a10000-0000-4000-8000-000000000004',
            $checksum,
        )->willReturn(ModerationQueueClaimResult::Applied);

        $result = (new DeterministicModerationQueueRuntimeV1($store))->claim(
            '53a10000-0000-4000-8000-000000000001',
            '53a10000-0000-4000-8000-000000000002',
            '53a10000-0000-4000-8000-000000000003',
            $expiresAt,
            $claimedAt,
            '53a10000-0000-4000-8000-000000000004',
            $checksum,
        );

        self::assertSame(ModerationQueueClaimResult::Applied, $result);
    }
}
