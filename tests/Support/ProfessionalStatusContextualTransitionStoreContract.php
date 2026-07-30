<?php

namespace Tests\Support;

use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusAction;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusState;
use Appart\Modules\Professionals\Application\ProfessionalStatusLifecycle\ProfessionalStatusTransition;
use Appart\Modules\Professionals\Application\ProfessionalStatusPersistence\ProfessionalStatusId;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\Contract\ProfessionalStatusContextualTransitionStore;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualAppend;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusContextualWriteResult;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusExpectedVersion;
use Appart\Modules\Professionals\Application\ProfessionalStatusTransitionContext\ProfessionalStatusTransitionContext;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusActorId;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalStatusOccurredAt;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

abstract class ProfessionalStatusContextualTransitionStoreContract extends TestCase
{
    abstract protected function contextualStore(): ProfessionalStatusContextualTransitionStore;

    abstract protected function seedActive(ProfessionalStatusId $professionalId): void;

    public function test_contract_applies_and_replays_identically(): void
    {
        $id = $this->contractId();
        $this->seedActive($id);
        $append = $this->contractAppend($id, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');

        self::assertSame(ProfessionalStatusContextualWriteResult::Applied, $this->contextualStore()->append($append));
        self::assertSame(ProfessionalStatusContextualWriteResult::AlreadyApplied, $this->contextualStore()->append($append));
    }

    public function test_contract_detects_context_divergence(): void
    {
        $id = $this->contractId();
        $this->seedActive($id);

        self::assertSame(ProfessionalStatusContextualWriteResult::Applied, $this->contextualStore()->append($this->contractAppend($id, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')));
        self::assertSame(ProfessionalStatusContextualWriteResult::ContextDivergence, $this->contextualStore()->append($this->contractAppend($id, 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb')));
    }

    protected function contractId(): ProfessionalStatusId
    {
        return ProfessionalStatusId::fromString('a4500000-0000-4000-8000-000000000028');
    }

    protected function contractAppend(ProfessionalStatusId $id, string $actor): ProfessionalStatusContextualAppend
    {
        return new ProfessionalStatusContextualAppend(
            $id,
            new ProfessionalStatusTransition(ProfessionalStatusState::Active, ProfessionalStatusState::Suspended, ProfessionalStatusAction::Suspend),
            new ProfessionalStatusTransitionContext(ProfessionalStatusActorId::fromString($actor), ProfessionalStatusOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-22T12:00:00+00:00')), new ProfessionalStatusExpectedVersion(1)),
        );
    }
}
