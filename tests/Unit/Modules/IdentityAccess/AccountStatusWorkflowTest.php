<?php

namespace Tests\Unit\Modules\IdentityAccess;

use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusAction;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusActorId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextV1;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusContextVersion;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusCurrentState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusDecision;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusIntentId;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusOccurredAt;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusState;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusTransition;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusVersion;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusWorkflow;
use Appart\Modules\IdentityAccess\Application\AccountStatusLifecycle\AccountStatusWorkflowResult;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AccountStatusWorkflowTest extends TestCase
{
    /** @return iterable<string, array{AccountStatusState, AccountStatusAction, AccountStatusDecision, AccountStatusState}> */
    public static function decisionMatrix(): iterable
    {
        yield 'active suspend' => [
            AccountStatusState::Active,
            AccountStatusAction::Suspend,
            AccountStatusDecision::Applied,
            AccountStatusState::Suspended,
        ];
        yield 'active reactivate' => [
            AccountStatusState::Active,
            AccountStatusAction::Reactivate,
            AccountStatusDecision::AlreadyInState,
            AccountStatusState::Active,
        ];
        yield 'suspended suspend' => [
            AccountStatusState::Suspended,
            AccountStatusAction::Suspend,
            AccountStatusDecision::AlreadyInState,
            AccountStatusState::Suspended,
        ];
        yield 'suspended reactivate' => [
            AccountStatusState::Suspended,
            AccountStatusAction::Reactivate,
            AccountStatusDecision::Applied,
            AccountStatusState::Active,
        ];
    }

    #[DataProvider('decisionMatrix')]
    public function test_the_closed_matrix_is_exhaustively_decided(
        AccountStatusState $state,
        AccountStatusAction $action,
        AccountStatusDecision $decision,
        AccountStatusState $resultingState,
    ): void {
        $current = self::current($state);
        $result = (new AccountStatusWorkflow)->decide($current, $action, self::context($current, $action));

        self::assertSame($decision, $result->decision);
        self::assertSame($resultingState, $result->state);

        if ($decision === AccountStatusDecision::Applied) {
            self::assertEquals(new AccountStatusTransition($state, $action, $resultingState), $result->transition);
        } else {
            self::assertNull($result->transition);
        }
    }

    public function test_initial_state_and_contract_sets_are_closed(): void
    {
        self::assertSame(AccountStatusState::Active, (new AccountStatusWorkflow)->initialState());
        self::assertSame(['active', 'suspended'], array_column(AccountStatusState::cases(), 'value'));
        self::assertSame(['suspend', 'reactivate'], array_column(AccountStatusAction::cases(), 'value'));
        self::assertSame(
            ['applied', 'already_in_state', 'invalid_context'],
            array_column(AccountStatusDecision::cases(), 'value'),
        );
        self::assertSame(AccountStatusContextVersion::V1, self::context(self::current(), AccountStatusAction::Suspend)->contractVersion);
    }

    public function test_context_identity_state_version_and_action_must_match_the_prepared_current_state(): void
    {
        $current = self::current();
        $workflow = new AccountStatusWorkflow;

        $otherAccount = AccountId::fromString('018f47c2-6b4f-7ab8-9abc-1234567890ab');
        self::assertSame(
            AccountStatusDecision::InvalidContext,
            $workflow->decide($current, AccountStatusAction::Suspend, self::context($current, AccountStatusAction::Suspend, accountId: $otherAccount))->decision,
        );
        self::assertSame(
            AccountStatusDecision::InvalidContext,
            $workflow->decide($current, AccountStatusAction::Suspend, self::context($current, AccountStatusAction::Suspend, state: AccountStatusState::Suspended))->decision,
        );
        self::assertSame(
            AccountStatusDecision::InvalidContext,
            $workflow->decide($current, AccountStatusAction::Suspend, self::context($current, AccountStatusAction::Suspend, observedVersion: new AccountStatusVersion(8)))->decision,
        );
        self::assertSame(
            AccountStatusDecision::InvalidContext,
            $workflow->decide($current, AccountStatusAction::Suspend, self::context($current, AccountStatusAction::Reactivate))->decision,
        );
    }

    public function test_invalid_metadata_is_a_closed_invalid_context_refusal(): void
    {
        $current = self::current();
        $workflow = new AccountStatusWorkflow;

        foreach ([
            self::context($current, AccountStatusAction::Suspend, expectedVersion: new AccountStatusVersion(-1)),
            self::context($current, AccountStatusAction::Suspend, actorId: new AccountStatusActorId(' ')),
            self::context($current, AccountStatusAction::Suspend, intentId: new AccountStatusIntentId('')),
        ] as $context) {
            $result = $workflow->decide($current, AccountStatusAction::Suspend, $context);
            self::assertSame(AccountStatusDecision::InvalidContext, $result->decision);
            self::assertSame(AccountStatusState::Active, $result->state);
            self::assertNull($result->transition);
        }
    }

    public function test_expected_version_is_not_compared_by_the_workflow(): void
    {
        $current = self::current();
        $context = self::context(
            $current,
            AccountStatusAction::Suspend,
            expectedVersion: new AccountStatusVersion(99),
        );

        self::assertSame(
            AccountStatusDecision::Applied,
            (new AccountStatusWorkflow)->decide($current, AccountStatusAction::Suspend, $context)->decision,
        );
    }

    public function test_every_decision_is_deterministic_and_models_are_immutable(): void
    {
        $workflow = new AccountStatusWorkflow;

        foreach (AccountStatusState::cases() as $state) {
            foreach (AccountStatusAction::cases() as $action) {
                $current = self::current($state);
                $context = self::context($current, $action);
                self::assertEquals(
                    $workflow->decide($current, $action, $context),
                    $workflow->decide($current, $action, $context),
                );
            }
        }

        foreach ([
            AccountStatusWorkflow::class,
            AccountStatusCurrentState::class,
            AccountStatusContextV1::class,
            AccountStatusTransition::class,
            AccountStatusWorkflowResult::class,
            AccountStatusActorId::class,
            AccountStatusIntentId::class,
            AccountStatusOccurredAt::class,
            AccountStatusVersion::class,
        ] as $class) {
            $reflection = new ReflectionClass($class);
            self::assertTrue($reflection->isFinal(), $class);
            self::assertTrue($reflection->isReadOnly(), $class);
        }
    }

    private static function current(AccountStatusState $state = AccountStatusState::Active): AccountStatusCurrentState
    {
        return new AccountStatusCurrentState(
            AccountId::fromString('018f47c2-6b4f-7ab8-9abc-1234567890aa'),
            $state,
            new AccountStatusVersion(7),
        );
    }

    private static function context(
        AccountStatusCurrentState $current,
        AccountStatusAction $action,
        ?AccountId $accountId = null,
        ?AccountStatusState $state = null,
        ?AccountStatusVersion $expectedVersion = null,
        ?AccountStatusVersion $observedVersion = null,
        ?AccountStatusActorId $actorId = null,
        ?AccountStatusIntentId $intentId = null,
    ): AccountStatusContextV1 {
        return new AccountStatusContextV1(
            $accountId ?? $current->accountId,
            $state ?? $current->state,
            $expectedVersion ?? new AccountStatusVersion(7),
            $observedVersion ?? $current->observedVersion,
            $action,
            $actorId ?? new AccountStatusActorId('operator-1'),
            new AccountStatusOccurredAt(new DateTimeImmutable('2026-07-26T10:00:00+00:00')),
            $intentId ?? new AccountStatusIntentId('intent-1'),
        );
    }
}
