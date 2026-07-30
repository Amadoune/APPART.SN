<?php

namespace Tests\Support;

use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleAction;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleState;
use Appart\Modules\Media\Application\MediaItemLifecycle\MediaItemLifecycleTransition;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\Contract\MediaItemLifecycleContextualTransitionStore;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionDecisionVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaCollectionTransitionDecision;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleActorId;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualAppend;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextualWriteResult;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleContextVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleExpectedVersion;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleOccurredAt;
use Appart\Modules\Media\Application\MediaItemLifecycleContext\MediaItemLifecycleTransitionContext;
use Appart\Modules\Media\Application\MediaItemLifecyclePersistence\MediaItemLifecycleId;
use Appart\Modules\Media\Domain\Exception\InvalidMediaValue;
use Appart\Modules\Media\Domain\ValueObject\MediaCollectionId;
use Appart\Modules\Media\Domain\ValueObject\MediaId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

abstract class MediaItemLifecycleContextualTransitionStoreContract extends TestCase
{
    abstract protected function contextualStore(): MediaItemLifecycleContextualTransitionStore;

    abstract protected function seedActive(MediaItemLifecycleId $mediaId): void;

    public function test_contract_applies_and_replays_identically(): void
    {
        $id = $this->contractId();
        $this->seedActive($id);
        $append = $this->contractAppend($id);

        self::assertSame(MediaItemLifecycleContextualWriteResult::Applied, $this->contextualStore()->append($append));
        self::assertSame(MediaItemLifecycleContextualWriteResult::AlreadyApplied, $this->contextualStore()->append($append));
    }

    public function test_contract_detects_context_divergence(): void
    {
        $id = $this->contractId();
        $this->seedActive($id);

        self::assertSame(MediaItemLifecycleContextualWriteResult::Applied, $this->contextualStore()->append($this->contractAppend($id)));
        self::assertSame(MediaItemLifecycleContextualWriteResult::ContextDivergence, $this->contextualStore()->append($this->contractAppend($id, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb')));
    }

    protected function contractId(): MediaItemLifecycleId
    {
        return MediaItemLifecycleId::fromString('a4600000-0000-4000-8000-000000000032');
    }

    protected function contractAppend(MediaItemLifecycleId $id, string $actor = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'): MediaItemLifecycleContextualAppend
    {
        return new MediaItemLifecycleContextualAppend(
            $id,
            new MediaItemLifecycleTransition(MediaItemLifecycleState::Active, MediaItemLifecycleState::Removed, MediaItemLifecycleAction::Remove),
            new MediaItemLifecycleTransitionContext(
                MediaItemLifecycleContextVersion::V1,
                MediaCollectionId::fromString('a4601000-0000-4000-8000-000000000001'),
                $this->media($id->value),
                new MediaItemLifecycleExpectedVersion(1),
                new MediaCollectionDecisionVersion(7),
                MediaItemLifecycleActorId::fromString($actor),
                MediaItemLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-23T12:00:00.123456Z')),
                MediaCollectionTransitionDecision::notPrimary(),
            ),
        );
    }

    private function media(string $value): MediaId
    {
        try {
            return MediaId::fromString($value);
        } catch (InvalidMediaValue $error) {
            self::fail($error->getMessage());
        }
    }
}
