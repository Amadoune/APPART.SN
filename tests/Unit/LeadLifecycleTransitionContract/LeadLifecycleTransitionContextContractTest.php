<?php

namespace Tests\Unit\LeadLifecycleTransitionContract;

use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleAction;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleState;
use Appart\Modules\ContactsLeads\Application\LeadLifecycle\LeadLifecycleTransition;
use Appart\Modules\ContactsLeads\Application\LeadLifecyclePersistence\LeadId;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualAppend;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleContextualWriteResult;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleEligibilityRequirement;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleTransitionContext;
use Appart\Modules\ContactsLeads\Application\LeadLifecycleTransitionContract\LeadLifecycleTransitionEligibilityPolicy;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleActorId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadLifecycleOccurredAt;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class LeadLifecycleTransitionContextContractTest extends TestCase
{
    #[DataProvider('applicationActions')]
    public function test_transition_actions_do_not_requalify_creation_eligibility(LeadLifecycleAction $action): void
    {
        self::assertSame(LeadLifecycleEligibilityRequirement::NotRequired, (new LeadLifecycleTransitionEligibilityPolicy)->requirementFor($action));
    }

    public function test_unknown_is_not_an_application_action(): void
    {
        $this->expectException(DomainException::class);
        (new LeadLifecycleTransitionEligibilityPolicy)->requirementFor(LeadLifecycleAction::Unknown);
    }

    public function test_context_is_explicit_immutable_and_versions_the_append(): void
    {
        $context = new LeadLifecycleTransitionContext(
            LeadLifecycleActorId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
            LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-21T12:00:00+00:00')),
        );
        $append = new LeadLifecycleContextualAppend(
            LeadId::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'),
            new LeadLifecycleTransition(LeadLifecycleState::Created, LeadLifecycleState::Delivered, LeadLifecycleAction::Deliver),
            1,
            $context,
        );

        self::assertSame(2, $append->nextVersion());
        self::assertSame($context, $append->context);
        foreach ([$context, $append, $context->actor, $context->occurredAt] as $model) {
            self::assertTrue((new ReflectionClass($model))->isReadOnly());
        }
    }

    public function test_context_rejects_non_utc_instant(): void
    {
        $this->expectException(InvalidArgumentException::class);
        LeadLifecycleOccurredAt::fromExplicitUtc(new DateTimeImmutable('2026-07-21T14:00:00+02:00'));
    }

    public function test_write_result_is_closed_and_exhaustive(): void
    {
        self::assertSame(['applied', 'already_applied', 'version_conflict', 'state_conflict', 'transition_rejected', 'context_divergence', 'corrupted'], array_column(LeadLifecycleContextualWriteResult::cases(), 'value'));
    }

    public static function applicationActions(): array
    {
        return ['deliver' => [LeadLifecycleAction::Deliver], 'reject' => [LeadLifecycleAction::Reject], 'close' => [LeadLifecycleAction::Close]];
    }
}
