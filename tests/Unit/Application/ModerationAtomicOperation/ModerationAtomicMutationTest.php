<?php

namespace Tests\Unit\Application\ModerationAtomicOperation;

use App\Application\ModerationAtomicOperation\Contract\ModerationAtomicOperationV1;
use App\Application\ModerationAtomicOperation\Contract\ModerationOutboxAppenderV1;
use App\Application\ModerationAtomicOperation\ModerationAtomicMutation;
use App\Application\ModerationAtomicOperation\ModerationAtomicWorkResult;
use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationOrchestration\Contract\ModerationCommandResult;
use App\Application\ModerationOrchestration\Contract\ModerationCommandStatus;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationAtomicMutationTest extends TestCase
{
    #[Test]
    public function it_commits_only_committable_mutations_and_successful_appends(): void
    {
        $transaction = new class implements ModerationAtomicOperationV1
        {
            public function execute(callable $operation): ModerationAtomicWorkResult
            {
                return $operation();
            }
        };
        $outbox = new class implements ModerationOutboxAppenderV1
        {
            public int $calls = 0;

            public ModerationOutboxAppendResult $result = ModerationOutboxAppendResult::Stored;

            public function append(ModerationDeliveryMessageV1 $message): ModerationOutboxAppendResult
            {
                $this->calls++;

                return $this->result;
            }
        };
        $atomic = new ModerationAtomicMutation($transaction, $outbox);

        $applied = $atomic->execute(
            static fn (): ModerationCommandResult => new ModerationCommandResult(ModerationCommandStatus::Applied),
            $this->message(),
        );
        self::assertSame('commit', $applied->decision->value);
        self::assertSame(1, $outbox->calls);

        $rejected = $atomic->execute(
            static fn (): ModerationCommandResult => new ModerationCommandResult(ModerationCommandStatus::VersionConflict),
            $this->message(),
        );
        self::assertSame('rollback', $rejected->decision->value);
        self::assertSame(1, $outbox->calls);

        $outbox->result = ModerationOutboxAppendResult::DivergentMessage;
        $divergent = $atomic->execute(
            static fn (): ModerationCommandResult => new ModerationCommandResult(ModerationCommandStatus::Applied),
            $this->message(),
        );
        self::assertSame('rollback', $divergent->decision->value);
        self::assertSame(ModerationOutboxAppendResult::DivergentMessage, $divergent->value);
    }

    private function message(): ModerationDeliveryMessageV1
    {
        $time = new DateTimeImmutable('2026-07-30T12:00:00+00:00');

        return new ModerationDeliveryMessageV1(new ModerationEventV1(
            ModerationEventTypeV1::ReportSubmitted,
            '53a10000-0000-4000-8000-000000000001',
            1,
            [],
            'v1',
            $time,
            $time,
            '53a10000-0000-4000-8000-000000000002',
            '53a10000-0000-4000-8000-000000000003',
        ));
    }
}
