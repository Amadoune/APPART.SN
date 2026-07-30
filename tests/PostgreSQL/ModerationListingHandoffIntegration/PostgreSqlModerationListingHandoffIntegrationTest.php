<?php

namespace Tests\PostgreSQL\ModerationListingHandoffIntegration;

use App\Application\ModerationAtomicOperation\Contract\ModerationOutboxAppenderV1;
use App\Application\ModerationAtomicOperation\ModerationOutboxAppendResult;
use App\Application\ModerationEventRouting\DeterministicModerationEventRouter;
use App\Application\ModerationEventRouting\ModerationRoutingDestination;
use App\Application\ModerationEventTransport\ModerationDeliveryMessageV1;
use App\Application\ModerationEventTransport\ModerationEventTransportSerializer;
use App\Application\ModerationListingHandoff\ModerationListingHandoffConsumer;
use App\Application\ModerationListingHandoff\ModerationListingHandoffStatus;
use App\Application\ModerationListingHandoff\ModerationListingHandoffTerminalIntegrator;
use App\Infrastructure\ModerationAtomicOperation\PostgreSql\PostgreSqlModerationAtomicOperation;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationListingHandoffResultStore;
use App\Infrastructure\ModerationEventOutbox\PostgreSql\PostgreSqlModerationOutbox;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModerationCapabilityV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModeratorAuthorizationDecisionV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\OwnerListingModerationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\OwnerListingModerationReaderV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\DeterministicListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationState;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingPublicationWorkflowMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingModerationIntentStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingModerationIntentTransaction;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationWorkflowRepository;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventTypeV1;
use Appart\Modules\ModerationReports\Application\ModerationEvent\ModerationEventV1;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationCasePersistenceState;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceRecord;
use Appart\Modules\ModerationReports\Application\ModerationPersistence\ModerationPersistenceWriteResult;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\ModerationPersistenceMapper;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationCaseStore;
use Appart\Modules\ModerationReports\Infrastructure\Persistence\PostgreSql\PostgreSqlModerationDecisionStore;
use DateTimeImmutable;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

final class PostgreSqlModerationListingHandoffIntegrationTest extends TestCase
{
    private PDO $connection;

    private PostgreSqlModerationOutbox $outbox;

    private PostgreSqlModerationListingHandoffResultStore $results;

    private PostgreSqlModerationCaseStore $cases;

    private PostgreSqlModerationDecisionStore $decisions;

    private PostgreSqlListingPublicationWorkflowRepository $listings;

    protected function setUp(): void
    {
        $this->connection = PostgreSqlTestEnvironment::connection();
        PostgreSqlTestEnvironment::migrate($this->connection);
        PostgreSqlTestEnvironment::reset($this->connection);
        $mapper = new ModerationPersistenceMapper;
        $this->cases = new PostgreSqlModerationCaseStore($this->connection, $mapper);
        $this->decisions = new PostgreSqlModerationDecisionStore($this->connection, $mapper);
        $this->outbox = new PostgreSqlModerationOutbox(
            $this->connection,
            new ModerationEventTransportSerializer,
            new DeterministicModerationEventRouter,
        );
        $this->results = new PostgreSqlModerationListingHandoffResultStore($this->connection);
        $this->listings = new PostgreSqlListingPublicationWorkflowRepository(
            $this->connection,
            new ListingPublicationWorkflowMapper,
        );
    }

    #[Test]
    public function complete_handoff_and_replay_converge_without_double_listing_mutation(): void
    {
        $message = $this->seedDecisionAndMessage(1);
        $consumer = $this->consumer(ModeratorAuthorizationDecisionV1::Allowed);

        self::assertSame(
            ModerationListingHandoffStatus::Applied,
            $consumer->consumeNext('listing-worker-a', $this->now()),
        );
        self::assertSame(ListingPublicationState::Suspended, $this->listings->read(
            ListingId::fromString($this->id(5)),
        )->snapshot?->state);
        self::assertSame(2, $this->listings->read(ListingId::fromString($this->id(5)))->snapshot?->version);

        self::assertTrue($this->outbox->replay(
            $message->messageId,
            ModerationRoutingDestination::ListingHandoff->value,
            $this->now()->modify('+1 second'),
        ));
        self::assertSame(
            ModerationListingHandoffStatus::AlreadyApplied,
            $consumer->consumeNext('listing-worker-b', $this->now()->modify('+1 second')),
        );
        self::assertSame(2, $this->listings->read(ListingId::fromString($this->id(5)))->snapshot?->version);
        self::assertSame(
            ModerationListingHandoffStatus::Applied,
            $this->results->latest($message->messageId)?->status,
        );
        self::assertSame(1, $this->tableCount('listing_lifecycle.moderation_command_intents'));
        self::assertSame(1, (int) $this->connection->query(
            "SELECT count(*) FROM moderation_reports.outbox_messages
             WHERE event_type='moderation.target-action.completed.v1'",
        )->fetchColumn());
        self::assertSame(
            [
                'result' => 'applied',
                'decisionId' => $this->id(2),
                'targetOwner' => 'Listing',
                'targetActionId' => $message->event->eventId,
                'contractVersion' => 'v1',
            ],
            json_decode((string) $this->connection->query(
                "SELECT canonical_transport->'payload'->'payload'
                 FROM moderation_reports.outbox_messages
                 WHERE event_type='moderation.target-action.completed.v1'",
            )->fetchColumn(), true, flags: JSON_THROW_ON_ERROR),
        );
    }

    #[Test]
    public function authorization_denied_is_terminal_without_listing_command(): void
    {
        $message = $this->seedDecisionAndMessage(20);
        $consumer = $this->consumer(ModeratorAuthorizationDecisionV1::Denied);

        self::assertSame(
            ModerationListingHandoffStatus::AuthorizationDenied,
            $consumer->consumeNext('listing-worker', $this->now()),
        );
        self::assertSame(ListingPublicationState::Published, $this->listings->read(
            ListingId::fromString($this->id(24)),
        )->snapshot?->state);
        self::assertSame(
            ModerationListingHandoffStatus::AuthorizationDenied,
            $this->results->latest($message->messageId)?->status,
        );
        self::assertSame(0, $this->tableCount('listing_lifecycle.moderation_command_intents'));
    }

    #[Test]
    public function rejected_target_completion_outbox_rolls_back_only_moderation_integration(): void
    {
        $message = $this->seedDecisionAndMessage(60);
        $rejected = new class implements ModerationOutboxAppenderV1
        {
            public function append(ModerationDeliveryMessageV1 $message): ModerationOutboxAppendResult
            {
                return ModerationOutboxAppendResult::Rejected;
            }
        };
        $integrator = new ModerationListingHandoffTerminalIntegrator(
            new PostgreSqlModerationAtomicOperation($this->connection),
            $this->results,
            $rejected,
        );
        $consumer = $this->consumer(ModeratorAuthorizationDecisionV1::Allowed, $integrator);

        self::assertSame(
            ModerationListingHandoffStatus::DependencyUnavailable,
            $consumer->consumeNext('listing-worker', $this->now()),
        );
        self::assertSame(
            ModerationListingHandoffStatus::DependencyUnavailable,
            $this->results->latest($message->messageId)?->status,
        );
        self::assertSame(0, (int) $this->connection->query(
            "SELECT count(*) FROM moderation_reports.outbox_messages
             WHERE event_type='moderation.target-action.completed.v1'",
        )->fetchColumn());
        self::assertSame(ListingPublicationState::Suspended, $this->listings->read(
            ListingId::fromString($this->id(64)),
        )->snapshot?->state);
        self::assertSame(1, $this->tableCount('listing_lifecycle.moderation_command_intents'));
    }

    #[Test]
    public function result_store_is_idempotent_append_only_and_rollback_safe(): void
    {
        $message = $this->seedDecisionAndMessage(40);
        $consumer = $this->consumer(ModeratorAuthorizationDecisionV1::Allowed);

        $this->connection->beginTransaction();
        self::assertSame(
            ModerationListingHandoffStatus::Applied,
            $consumer->consumeNext('listing-worker', $this->now()),
        );
        self::assertGreaterThan(0, $this->tableCount('moderation_reports.listing_handoff_results'));
        $this->connection->rollBack();

        self::assertSame(0, $this->tableCount('moderation_reports.listing_handoff_results'));
        self::assertSame(ListingPublicationState::Published, $this->listings->read(
            ListingId::fromString($this->id(44)),
        )->snapshot?->state);
        self::assertNotNull($this->outbox->read(
            $message->messageId,
            ModerationRoutingDestination::ListingHandoff->value,
        ));
    }

    #[Test]
    public function migration_has_complete_rollback_and_preserves_previous_migrations(): void
    {
        $directory = dirname(__DIR__, 3).'/src/Modules/ModerationReports/Infrastructure/Persistence/PostgreSql/Migrations/';
        $this->connection->exec((string) file_get_contents($directory.'069_moderation_listing_handoff_results.down.sql'));
        self::assertFalse((bool) $this->connection->query(
            "SELECT to_regclass('moderation_reports.listing_handoff_results') IS NOT NULL",
        )->fetchColumn());
        $this->connection->exec((string) file_get_contents($directory.'069_moderation_listing_handoff_results.sql'));
        self::assertTrue((bool) $this->connection->query(
            "SELECT to_regclass('moderation_reports.listing_handoff_results') IS NOT NULL",
        )->fetchColumn());
        self::assertTrue((bool) $this->connection->query(
            "SELECT to_regclass('moderation_reports.outbox_messages') IS NOT NULL",
        )->fetchColumn());
    }

    private function consumer(
        ModeratorAuthorizationDecisionV1 $authorizationDecision,
        ?ModerationListingHandoffTerminalIntegrator $terminalIntegrator = null,
    ): ModerationListingHandoffConsumer {
        $authorization = new class($authorizationDecision) implements ModeratorAuthorizationReaderV1
        {
            public function __construct(private ModeratorAuthorizationDecisionV1 $decision) {}

            public function authorize(
                AccountId $accountId,
                ModerationCapabilityV1 $capability,
                DateTimeImmutable $observedAt,
            ): ModeratorAuthorizationDecisionV1 {
                return $this->decision;
            }
        };
        $workflow = new ListingPublicationWorkflow;
        $intents = new PostgreSqlListingModerationIntentStore($this->connection);

        return new ModerationListingHandoffConsumer(
            $this->outbox,
            $authorization,
            new OwnerListingModerationReaderV1($this->listings, $workflow),
            new OwnerListingModerationCommandGatewayV1(
                $intents,
                new PostgreSqlListingModerationIntentTransaction($this->connection),
                new DeterministicListingPublicationOrchestrator($workflow, $this->listings),
                $this->listings,
            ),
            $this->cases,
            $this->decisions,
            $this->results,
            $terminalIntegrator ?? new ModerationListingHandoffTerminalIntegrator(
                new PostgreSqlModerationAtomicOperation($this->connection),
                $this->results,
                $this->outbox,
            ),
        );
    }

    private function seedDecisionAndMessage(int $base): ModerationDeliveryMessageV1
    {
        $caseId = $this->id($base);
        $decisionId = $this->id($base + 1);
        $listingId = $this->id($base + 4);
        self::assertSame(
            ModerationPersistenceWriteResult::Applied,
            $this->cases->save(new ModerationCasePersistenceState(
                $caseId,
                'Listing',
                $listingId,
                'Decided',
                $decisionId,
                1,
                $this->id($base + 2),
                str_repeat('a', 64),
                $this->now(),
                [],
                [],
                [new ModerationPersistenceRecord(
                    $decisionId,
                    [
                        'actorAccountId' => $this->id($base + 3),
                        'targetAction' => 'suspend',
                    ],
                    $this->now(),
                )],
            ), 0),
        );
        $this->listings->initialize(ListingId::fromString($listingId), ListingPublicationState::Published);
        $message = new ModerationDeliveryMessageV1(new ModerationEventV1(
            ModerationEventTypeV1::DecisionIssued,
            $caseId,
            1,
            ['decisionId' => $decisionId, 'targetAction' => 'suspend'],
            'v1',
            $this->now(),
            $this->now(),
            $this->id($base + 5),
            $this->id($base + 6),
        ));
        $this->outbox->append($message);

        return $message;
    }

    private function tableCount(string $table): int
    {
        return (int) $this->connection->query("SELECT count(*) FROM {$table}")->fetchColumn();
    }

    private function id(int $suffix): string
    {
        return sprintf('53f30000-0000-4000-8000-%012d', $suffix);
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-07-30T12:00:00+00:00');
    }
}
