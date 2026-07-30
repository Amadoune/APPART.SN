<?php

namespace Tests\Unit\Application\ModerationListingHandoff;

use App\Application\ModerationAtomicOperation\Contract\ModerationAtomicOperationV1;
use App\Application\ModerationAtomicOperation\Contract\ModerationOutboxAppenderV1;
use App\Application\ModerationAtomicOperation\ModerationAtomicWorkResult;
use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventOutbox\Contract\ModerationOutboxReaderV1;
use App\Application\ModerationEventOutbox\ModerationOutboxDelivery;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationListingHandoff\Contract\ModerationListingHandoffResultStore;
use App\Application\ModerationListingHandoff\ModerationListingHandoffConsumer;
use App\Application\ModerationListingHandoff\ModerationListingHandoffRecord;
use App\Application\ModerationListingHandoff\ModerationListingHandoffStatus;
use App\Application\ModerationListingHandoff\ModerationListingHandoffTerminalIntegrator;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModeratorAuthorizationDecisionV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\Contract\ListingModerationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\Contract\ListingModerationReaderV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationCommandResultV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationEligibilityV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationCaseStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\Contract\ModerationDecisionStore;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceReadResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceState;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationDecisionPersistenceReadResult;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceRecord;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModerationListingHandoffConsumerTest extends TestCase
{
    #[Test]
    public function allowed_and_eligible_decision_is_applied_and_recorded(): void
    {
        $fixture = $this->fixture(
            ModeratorAuthorizationDecisionV1::Allowed,
            ListingModerationEligibilityV1::Eligible,
            ListingModerationCommandResultV1::Applied,
        );

        self::assertSame(
            ModerationListingHandoffStatus::Applied,
            $fixture->consumer->consumeNext('worker-a', $this->now()),
        );
        self::assertSame(1, $fixture->gatewayCalls);
        self::assertSame([
            ModerationListingHandoffStatus::Requested,
            ModerationListingHandoffStatus::Applied,
        ], array_column($fixture->results->records, 'status'));
        self::assertTrue($fixture->outbox->delivered);
    }

    #[Test]
    public function denied_authorization_and_ineligible_target_never_call_gateway(): void
    {
        foreach ([
            [ModeratorAuthorizationDecisionV1::Denied, ListingModerationEligibilityV1::Eligible, ModerationListingHandoffStatus::AuthorizationDenied],
            [ModeratorAuthorizationDecisionV1::Allowed, ListingModerationEligibilityV1::Ineligible, ModerationListingHandoffStatus::TargetIneligible],
        ] as [$authorization, $eligibility, $expected]) {
            $fixture = $this->fixture($authorization, $eligibility, ListingModerationCommandResultV1::Applied);
            self::assertSame($expected, $fixture->consumer->consumeNext('worker-a', $this->now()));
            self::assertSame(0, $fixture->gatewayCalls);
            self::assertTrue($fixture->outbox->delivered);
        }
    }

    #[Test]
    public function dependency_is_retried_and_divergence_is_quarantined(): void
    {
        $retry = $this->fixture(
            ModeratorAuthorizationDecisionV1::Allowed,
            ListingModerationEligibilityV1::Eligible,
            ListingModerationCommandResultV1::DependencyUnavailable,
        );
        self::assertSame(
            ModerationListingHandoffStatus::DependencyUnavailable,
            $retry->consumer->consumeNext('worker-a', $this->now()),
        );
        self::assertTrue($retry->outbox->retried);

        $divergent = $this->fixture(
            ModeratorAuthorizationDecisionV1::Allowed,
            ListingModerationEligibilityV1::Eligible,
            ListingModerationCommandResultV1::DivergentIntent,
        );
        self::assertSame(
            ModerationListingHandoffStatus::Quarantined,
            $divergent->consumer->consumeNext('worker-a', $this->now()),
        );
        self::assertTrue($divergent->outbox->quarantined);
    }

    #[Test]
    public function gateway_terminal_results_are_closed_and_delivered(): void
    {
        foreach ([
            [ListingModerationCommandResultV1::AlreadyApplied, ModerationListingHandoffStatus::AlreadyApplied],
            [ListingModerationCommandResultV1::Rejected, ModerationListingHandoffStatus::Rejected],
            [ListingModerationCommandResultV1::VersionConflict, ModerationListingHandoffStatus::VersionConflict],
        ] as [$gatewayResult, $expected]) {
            $fixture = $this->fixture(
                ModeratorAuthorizationDecisionV1::Allowed,
                ListingModerationEligibilityV1::Eligible,
                $gatewayResult,
            );
            self::assertSame($expected, $fixture->consumer->consumeNext('worker-a', $this->now()));
            self::assertTrue($fixture->outbox->delivered);
        }
    }

    #[Test]
    public function corrupted_authorization_or_target_is_quarantined_without_gateway_call(): void
    {
        foreach ([
            [ModeratorAuthorizationDecisionV1::Corrupted, ListingModerationEligibilityV1::Eligible],
            [ModeratorAuthorizationDecisionV1::Allowed, ListingModerationEligibilityV1::Corrupted],
        ] as [$authorization, $eligibility]) {
            $fixture = $this->fixture(
                $authorization,
                $eligibility,
                ListingModerationCommandResultV1::Applied,
            );
            self::assertSame(
                ModerationListingHandoffStatus::Quarantined,
                $fixture->consumer->consumeNext('worker-a', $this->now()),
            );
            self::assertSame(0, $fixture->gatewayCalls);
            self::assertTrue($fixture->outbox->quarantined);
        }
    }

    private function fixture(
        ModeratorAuthorizationDecisionV1 $authorizationDecision,
        ListingModerationEligibilityV1 $eligibility,
        ListingModerationCommandResultV1 $gatewayResult,
    ): HandoffFixture {
        $delivery = new ModerationOutboxDelivery(
            new ModerationDeliveryMessageV1(new ModerationEventV1(
                ModerationEventTypeV1::DecisionIssued,
                $this->id(1),
                1,
                ['decisionId' => $this->id(2), 'targetAction' => 'suspend'],
                'v1',
                $this->now(),
                $this->now(),
                $this->id(3),
                $this->id(4),
            )),
            ModerationRoutingDestination::ListingHandoff,
            1,
            'worker-a',
        );
        $outbox = new HandoffOutboxFake($delivery);
        $results = new HandoffResultStoreFake;
        $fixture = new HandoffFixture($outbox, $results);
        $authorization = $this->createMock(ModeratorAuthorizationReaderV1::class);
        $authorization->method('authorize')->willReturn($authorizationDecision);
        $reader = $this->createMock(ListingModerationReaderV1::class);
        $reader->method('read')->willReturn($eligibility);
        $gateway = $this->createMock(ListingModerationCommandGatewayV1::class);
        $gateway->method('apply')->willReturnCallback(function () use ($fixture, $gatewayResult): ListingModerationCommandResultV1 {
            $fixture->gatewayCalls++;

            return $gatewayResult;
        });
        $cases = $this->createMock(ModerationCaseStore::class);
        $cases->method('read')->willReturn(ModerationCasePersistenceReadResult::found(
            new ModerationCasePersistenceState(
                $this->id(1),
                'Listing',
                $this->id(5),
                'Decided',
                $this->id(2),
                1,
                $this->id(6),
                str_repeat('a', 64),
                $this->now(),
                [],
                [],
                [],
            ),
        ));
        $decisions = $this->createMock(ModerationDecisionStore::class);
        $decisions->method('read')->willReturn(ModerationDecisionPersistenceReadResult::found(
            new ModerationPersistenceRecord(
                $this->id(2),
                ['actorAccountId' => $this->id(7), 'targetAction' => 'suspend'],
                $this->now(),
            ),
        ));
        $transaction = $this->createMock(ModerationAtomicOperationV1::class);
        $transaction->method('execute')->willReturnCallback(
            static fn (callable $work): ModerationAtomicWorkResult => $work(),
        );
        $eventOutbox = $this->createMock(ModerationOutboxAppenderV1::class);
        $eventOutbox->method('append')->willReturn(ModerationOutboxAppendResult::Stored);
        $fixture->consumer = new ModerationListingHandoffConsumer(
            $outbox,
            $authorization,
            $reader,
            $gateway,
            $cases,
            $decisions,
            $results,
            new ModerationListingHandoffTerminalIntegrator($transaction, $results, $eventOutbox),
        );

        return $fixture;
    }

    private function id(int $suffix): string
    {
        return sprintf('53f10000-0000-4000-8000-%012d', $suffix);
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}

final class HandoffFixture
{
    public ModerationListingHandoffConsumer $consumer;

    public int $gatewayCalls = 0;

    public function __construct(
        public HandoffOutboxFake $outbox,
        public HandoffResultStoreFake $results,
    ) {}
}

final class HandoffResultStoreFake implements ModerationListingHandoffResultStore
{
    /** @var list<ModerationListingHandoffRecord> */
    public array $records = [];

    public function append(ModerationListingHandoffRecord $record): bool
    {
        $this->records[] = $record;

        return true;
    }

    public function latest(string $messageId): ?ModerationListingHandoffRecord
    {
        return $this->records[array_key_last($this->records)] ?? null;
    }
}

final class HandoffOutboxFake implements ModerationOutboxReaderV1
{
    public bool $delivered = false;

    public bool $retried = false;

    public bool $quarantined = false;

    public function __construct(private ModerationOutboxDelivery $delivery) {}

    public function read(string $messageId, string $destination): ?ModerationOutboxDelivery
    {
        return $this->delivery;
    }

    public function claimNext(string $owner, DateTimeImmutable $now): ?ModerationOutboxDelivery
    {
        return $this->delivery;
    }

    public function claimNextForDestination(string $owner, ModerationRoutingDestination $destination, DateTimeImmutable $now): ?ModerationOutboxDelivery
    {
        return $this->delivery;
    }

    public function release(ModerationOutboxDelivery $delivery, DateTimeImmutable $availableAt): bool
    {
        return true;
    }

    public function retry(ModerationOutboxDelivery $delivery, DateTimeImmutable $availableAt, string $errorCode): bool
    {
        return $this->retried = true;
    }

    public function markDelivered(ModerationOutboxDelivery $delivery, DateTimeImmutable $at): bool
    {
        return $this->delivered = true;
    }

    public function quarantine(ModerationOutboxDelivery $delivery, string $errorCode): bool
    {
        return $this->quarantined = true;
    }

    public function replay(string $messageId, string $destination, DateTimeImmutable $at): bool
    {
        return true;
    }
}
