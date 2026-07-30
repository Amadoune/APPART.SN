<?php

namespace App\Application\ModerationAtomicOperation;

use App\Application\ModerationAtomicOperation\Contract\ModerationAtomicOperationV1;
use App\Application\ModerationAtomicOperation\Contract\ModerationOutboxAppenderV1;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationOrchestration\Contract\ModerationCommandResult;
use App\Application\ModerationOrchestration\Contract\ModerationCommandStatus;

final readonly class ModerationAtomicMutation
{
    public function __construct(private ModerationAtomicOperationV1 $transaction, private ModerationOutboxAppenderV1 $outbox) {}

    /** @param callable(): ModerationCommandResult $mutation */
    public function execute(callable $mutation, ModerationDeliveryMessageV1 $message): ModerationAtomicWorkResult
    {
        return $this->transaction->execute(function () use ($mutation, $message): ModerationAtomicWorkResult {
            $result = $mutation();
            if (! in_array($result->status, [ModerationCommandStatus::Applied, ModerationCommandStatus::AlreadyApplied], true)) {
                return ModerationAtomicWorkResult::rollback($result);
            }
            $append = $this->outbox->append($message);
            if (in_array($append, [ModerationOutboxAppendResult::DivergentMessage, ModerationOutboxAppendResult::Rejected], true)) {
                return ModerationAtomicWorkResult::rollback($append);
            }

            return ModerationAtomicWorkResult::commit($result);
        });
    }
}
