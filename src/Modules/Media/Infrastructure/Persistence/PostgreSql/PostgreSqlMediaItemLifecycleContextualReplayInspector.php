<?php

namespace Appart\Modules\Media\Infrastructure\Persistence\PostgreSql;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualReplayInspector;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionDecisionVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionTransitionDecision;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextChecksum;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualAppendInspection;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualInspectionResult;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleExpectedVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleTransitionContext;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaPrimaryTransitionDisposition;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use Appart\Modules\Media\Infrastructure\Persistence\MediaItemLifecycleWorkflowMapper;
use DateTimeImmutable;
use DateTimeZone;
use PDO;
use Throwable;

final readonly class PostgreSqlMediaItemLifecycleContextualReplayInspector implements MediaItemLifecycleContextualReplayInspector
{
    public function __construct(private PDO $connection, private MediaItemLifecycleWorkflowMapper $workflowMapper) {}

    public function inspectLatest(MediaItemLifecycleId $mediaId): MediaItemLifecycleContextualInspectionResult
    {
        $statement = $this->connection->prepare('SELECT t.media_id::text,t.version,t.previous_state,t.current_state,t.action,t.transition_checksum,c.contract_version,c.collection_id::text,c.collection_version,c.actor_id::text,c.occurred_at,c.primary_disposition,c.replacement_media_id::text,c.context_checksum FROM media.media_item_lifecycle_transitions t JOIN media.media_item_lifecycle_transition_contexts c USING(media_id,version) WHERE t.media_id=:media_id ORDER BY t.version DESC LIMIT 1');
        $statement->execute(['media_id' => $mediaId->value]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return MediaItemLifecycleContextualInspectionResult::missing($mediaId);
        }

        try {
            $occurredAt = (new DateTimeImmutable((string) $row['occurred_at']))->setTimezone(new DateTimeZone('UTC'));
            $context = $this->restoreContext($row, $occurredAt);
            if (! hash_equals((string) $row['transition_checksum'], $this->workflowMapper->checksum($row))
                || ! hash_equals((string) $row['context_checksum'], $context->checksum()->value)) {
                return MediaItemLifecycleContextualInspectionResult::corrupted($mediaId);
            }

            return MediaItemLifecycleContextualInspectionResult::found(new MediaItemLifecycleContextualAppendInspection(
                $mediaId,
                (int) $row['version'],
                new MediaItemLifecycleTransition(
                    MediaItemLifecycleState::from((string) $row['previous_state']),
                    MediaItemLifecycleState::from((string) $row['current_state']),
                    MediaItemLifecycleAction::from((string) $row['action']),
                ),
                $context,
                MediaItemLifecycleContextChecksum::fromString((string) $row['context_checksum']),
            ));
        } catch (Throwable) {
            return MediaItemLifecycleContextualInspectionResult::corrupted($mediaId);
        }
    }

    /** @param array<string, mixed> $row */
    private function restoreContext(array $row, DateTimeImmutable $occurredAt): MediaItemLifecycleTransitionContext
    {
        $media = MediaId::fromString((string) $row['media_id']);
        $disposition = MediaPrimaryTransitionDisposition::from((string) $row['primary_disposition']);
        $decision = $disposition === MediaPrimaryTransitionDisposition::NotPrimary
            ? MediaCollectionTransitionDecision::notPrimary()
            : MediaCollectionTransitionDecision::replacementSelected($media, MediaId::fromString((string) $row['replacement_media_id']));

        return new MediaItemLifecycleTransitionContext(
            MediaItemLifecycleContextVersion::from((int) $row['contract_version']),
            MediaCollectionId::fromString((string) $row['collection_id']),
            $media,
            new MediaItemLifecycleExpectedVersion((int) $row['version'] - 1),
            new MediaCollectionDecisionVersion((int) $row['collection_version']),
            MediaItemLifecycleActorId::fromString((string) $row['actor_id']),
            MediaItemLifecycleOccurredAt::fromExplicitUtc($occurredAt),
            $decision,
        );
    }
}
