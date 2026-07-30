<?php

namespace App\Application\ModerationHttpReadBoundaries;

use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModerationCapabilityV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModeratorAuthorizationDecisionV1;
use Appart\Modules\IdentityAccess\Domain\Exception\InvalidIdentityValue;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\Contract\ReadModerationQueueV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationQueueQueryV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadModerationQueueResultV1;
use Appart\Modules\ModerationReports\Application\HttpReadBoundaries\ReadStatusV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\Contract\ModerationQueueOwnerReadSourceV1;
use Appart\Modules\ModerationReports\Application\QueueOwnerReadSource\ModerationQueueReadStatusV1;

final readonly class OwnerReadModerationQueueV1 implements ReadModerationQueueV1
{
    public function __construct(
        private ModeratorAuthorizationReaderV1 $authorization,
        private ModerationQueueOwnerReadSourceV1 $queue,
    ) {}

    public function read(ReadModerationQueueQueryV1 $query): ReadModerationQueueResultV1
    {
        try {
            $authorization = $this->authorization->authorize(
                AccountId::fromString($query->actorAccountId),
                ModerationCapabilityV1::Investigate,
                $query->observedAt,
            );
        } catch (InvalidIdentityValue) {
            return ReadModerationQueueResultV1::status(ReadStatusV1::ForbiddenActor);
        }

        if ($authorization !== ModeratorAuthorizationDecisionV1::Allowed) {
            return ReadModerationQueueResultV1::status(
                $authorization === ModeratorAuthorizationDecisionV1::DependencyUnavailable
                    ? ReadStatusV1::DependencyUnavailable
                    : ReadStatusV1::ForbiddenActor,
            );
        }

        $result = $this->queue->read($query->filter, $query->cursor, $query->limit);

        return match ($result->status) {
            ModerationQueueReadStatusV1::PageAvailable => $result->page === null
                ? ReadModerationQueueResultV1::status(ReadStatusV1::QueueUnavailable)
                : ReadModerationQueueResultV1::available($result->page),
            ModerationQueueReadStatusV1::Empty => ReadModerationQueueResultV1::status(ReadStatusV1::Empty),
            ModerationQueueReadStatusV1::InvalidCursor => ReadModerationQueueResultV1::status(ReadStatusV1::InvalidCursor),
            ModerationQueueReadStatusV1::Corrupted => ReadModerationQueueResultV1::status(ReadStatusV1::QueueUnavailable),
            ModerationQueueReadStatusV1::DependencyUnavailable => ReadModerationQueueResultV1::status(ReadStatusV1::DependencyUnavailable),
        };
    }
}
