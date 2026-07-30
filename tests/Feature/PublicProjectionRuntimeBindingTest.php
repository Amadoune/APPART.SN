<?php

namespace Tests\Feature;

use App\Application\MultiTargetDelivery\Contract\MultiTargetPropagationStrategy;
use App\Application\ProjectionRuntimeSource\Contract\InspectablePublicListingProjectionSource;
use App\Application\PublicGeographySource\Contract\PublicGeographyDecisionReader;
use App\Application\PublicMediaSource\Contract\PublicMediaDecisionReader;
use App\Application\PublicProjectionDelivery\Contract\PublicProjectionDeliveryConsumer;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxClaimManager;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxReader;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxRetryPolicy;
use App\Application\PublicProjectionOutbox\Contract\PublicProjectionOutboxWriter;
use App\Application\PublicProjectionOutbox\PublicProjectionOutboxConsumerId;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionRebuildEnumerator;
use App\Application\PublicProjectionSourceLookup\Contract\MediaCollectionPropertyResolver;
use App\Application\PublicProjectionStore\Contract\PublicListingProjectionWriter;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionSourceLookup;
use App\Application\PublicProjectionUpdaterIntegration\Contract\PublicProjectionUpdateExecutor;
use App\Application\PublicProjectionWorker\Contract\PublicProjectionDeliveryClock;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryConsumerRegistry;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorker;
use App\Application\PublicProjectionWorker\PublicProjectionDeliveryWorkerId;
use App\Application\RuntimeHealth\Contract\RuntimeHealthInspector;
use App\Application\RuntimeHealth\RuntimeHealthStatus;
use App\Infrastructure\ProjectionRebuildRuntimeSource\PostgreSql\PostgreSqlPublicProjectionRebuildEnumerator;
use App\Infrastructure\ProjectionRuntimeSource\CertifiedPublicListingProjectionSource;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxParticipantTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlAggregateOutboxTransaction;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxClaimManager;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxReader;
use App\Infrastructure\PublicProjectionOutbox\PostgreSql\PostgreSqlPublicProjectionOutboxWriter;
use App\Infrastructure\PublicProjectionSourceLookup\CertifiedPublicProjectionSourceLookup;
use App\Infrastructure\PublicProjectionSourceLookup\RegistryMediaCollectionPropertyResolver;
use App\Infrastructure\PublicProjectionStore\PostgreSql\PostgreSqlPublicListingProjectionStore;
use App\Infrastructure\PublicProjectionWorker\SystemPublicProjectionDeliveryClock;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalCanonicalQualificationMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\HistoricalRedirectDecisionMapper;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Infrastructure\Persistence\PostgreSql\PostgreSqlHistoricalRedirectResolver;
use Appart\Modules\ListingLifecycle\Application\Contract\ListingRegistry;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingRepository;
use Appart\Modules\Media\Application\Contract\MediaCollectionRegistry;
use Appart\Modules\Media\Infrastructure\Persistence\PostgreSql\PostgreSqlMediaCollectionRepository;
use Appart\Modules\RealEstateCatalog\Application\Contract\PropertyRegistry;
use Appart\Modules\RealEstateCatalog\Infrastructure\Persistence\PostgreSql\PostgreSqlPropertyRepository;
use PDO;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\TestCase;

final class PublicProjectionRuntimeBindingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->instance(PDO::class, PostgreSqlTestEnvironment::connection());
    }

    public function test_all_certified_runtime_contracts_resolve_to_production_implementations(): void
    {
        $expected = [
            PublicProjectionSourceLookup::class => CertifiedPublicProjectionSourceLookup::class,
            InspectablePublicListingProjectionSource::class => CertifiedPublicListingProjectionSource::class,
            MediaCollectionPropertyResolver::class => RegistryMediaCollectionPropertyResolver::class,
            PublicListingProjectionWriter::class => PostgreSqlPublicListingProjectionStore::class,
            PublicProjectionRebuildEnumerator::class => PostgreSqlPublicProjectionRebuildEnumerator::class,
        ];
        foreach ($expected as $contract => $implementation) {
            self::assertInstanceOf($implementation, $this->app->make($contract));
        }
        foreach ([PublicGeographyDecisionReader::class, PublicMediaDecisionReader::class, MultiTargetPropagationStrategy::class, PublicProjectionUpdateExecutor::class, PublicProjectionDeliveryConsumer::class] as $contract) {
            self::assertInstanceOf($contract, $this->app->make($contract));
        }
    }

    public function test_runtime_health_is_healthy_with_production_bindings(): void
    {
        $health = $this->app->make(RuntimeHealthInspector::class)->inspect();

        self::assertSame(RuntimeHealthStatus::Healthy, $health->status);
        self::assertSame([], $health->diagnostics);
    }

    public function test_historical_redirect_graph_is_lazy_unique_and_fully_resolved_by_laravel(): void
    {
        foreach ([HistoricalRedirectDecisionMapper::class, PostgreSqlHistoricalRedirectResolver::class, HistoricalRedirectResolver::class] as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $mapper = $this->app->make(HistoricalRedirectDecisionMapper::class);
        $adapter = $this->app->make(PostgreSqlHistoricalRedirectResolver::class);
        $resolver = $this->app->make(HistoricalRedirectResolver::class);

        self::assertInstanceOf(HistoricalRedirectDecisionMapper::class, $mapper);
        self::assertInstanceOf(PostgreSqlHistoricalRedirectResolver::class, $resolver);
        self::assertSame($adapter, $resolver);
        self::assertSame($mapper, new \ReflectionProperty($adapter, 'mapper')->getValue($adapter));
        self::assertSame($this->app->make(PDO::class), new \ReflectionProperty($adapter, 'connection')->getValue($adapter));
        self::assertStringNotContainsStringIgnoringCase('fake', $adapter::class);
    }

    public function test_historical_canonical_qualification_graph_is_lazy_unique_and_fully_resolved_by_laravel(): void
    {
        foreach ([HistoricalCanonicalQualificationMapper::class, PostgreSqlHistoricalCanonicalQualifier::class, HistoricalCanonicalQualifier::class] as $component) {
            self::assertTrue($this->app->bound($component), $component);
            self::assertFalse($this->app->resolved($component), $component);
        }

        $mapper = $this->app->make(HistoricalCanonicalQualificationMapper::class);
        $adapter = $this->app->make(PostgreSqlHistoricalCanonicalQualifier::class);
        $qualifier = $this->app->make(HistoricalCanonicalQualifier::class);

        self::assertInstanceOf(HistoricalCanonicalQualificationMapper::class, $mapper);
        self::assertInstanceOf(PostgreSqlHistoricalCanonicalQualifier::class, $qualifier);
        self::assertSame($adapter, $qualifier);
        self::assertSame($mapper, new \ReflectionProperty($adapter, 'mapper')->getValue($adapter));
        self::assertSame($this->app->make(PDO::class), new \ReflectionProperty($adapter, 'connection')->getValue($adapter));
        self::assertStringNotContainsStringIgnoringCase('fake', $adapter::class);
    }

    public function test_delivery_worker_and_its_complete_production_graph_are_resolved_by_laravel(): void
    {
        $expected = [
            PublicProjectionOutboxReader::class => PostgreSqlPublicProjectionOutboxReader::class,
            PublicProjectionOutboxWriter::class => PostgreSqlPublicProjectionOutboxWriter::class,
            PublicProjectionOutboxClaimManager::class => PostgreSqlPublicProjectionOutboxClaimManager::class,
            PublicProjectionDeliveryClock::class => SystemPublicProjectionDeliveryClock::class,
        ];
        foreach ($expected as $contract => $implementation) {
            self::assertInstanceOf($implementation, $this->app->make($contract));
        }

        foreach ([PublicProjectionOutboxRetryPolicy::class, PublicProjectionDeliveryConsumerRegistry::class, PublicProjectionOutboxConsumerId::class, PublicProjectionDeliveryWorkerId::class] as $contract) {
            self::assertInstanceOf($contract, $this->app->make($contract));
        }

        $worker = $this->app->make(PublicProjectionDeliveryWorker::class);
        self::assertInstanceOf(PublicProjectionDeliveryWorker::class, $worker);
        self::assertSame($worker, $this->app->make(PublicProjectionDeliveryWorker::class));
        self::assertNotSame('', $this->app->make(PublicProjectionDeliveryClock::class)->now()->format(DATE_ATOM));

        $registrations = new \ReflectionProperty(PublicProjectionDeliveryConsumerRegistry::class, 'registrations')->getValue($this->app->make(PublicProjectionDeliveryConsumerRegistry::class));
        self::assertIsArray($registrations);
        self::assertCount(54, $registrations);
        self::assertCount(54, array_unique(array_map(static fn ($registration): string => $registration->eventType->value, $registrations)));
    }

    public function test_each_runtime_contract_has_one_explicit_container_binding(): void
    {
        foreach ([PublicProjectionSourceLookup::class, InspectablePublicListingProjectionSource::class, PublicGeographyDecisionReader::class, PublicMediaDecisionReader::class, MediaCollectionPropertyResolver::class, MultiTargetPropagationStrategy::class, PublicProjectionUpdateExecutor::class, PublicListingProjectionWriter::class, PublicProjectionRebuildEnumerator::class, PublicProjectionDeliveryConsumer::class, HistoricalRedirectResolver::class, HistoricalCanonicalQualifier::class] as $contract) {
            self::assertTrue($this->app->bound($contract), $contract);
            self::assertSame($this->app->make($contract)::class, $this->app->make($contract)::class);
        }
    }

    public function test_aggregate_repositories_share_the_runtime_transaction_participant_and_pdo(): void
    {
        $pdo = $this->app->make(PDO::class);
        $participant = $this->app->make(PostgreSqlAggregateOutboxParticipantTransaction::class);
        $transaction = $this->app->make(PostgreSqlAggregateOutboxTransaction::class);

        self::assertSame($pdo, new \ReflectionProperty($participant, 'connection')->getValue($participant));
        self::assertSame($pdo, new \ReflectionProperty($transaction, 'connection')->getValue($transaction));

        $repositories = [
            ListingRegistry::class => PostgreSqlListingRepository::class,
            PropertyRegistry::class => PostgreSqlPropertyRepository::class,
            MediaCollectionRegistry::class => PostgreSqlMediaCollectionRepository::class,
        ];
        foreach ($repositories as $contract => $implementation) {
            $repository = $this->app->make($contract);
            self::assertInstanceOf($implementation, $repository);
            self::assertSame($pdo, new \ReflectionProperty($repository, 'connection')->getValue($repository));
            self::assertSame($participant, new \ReflectionProperty($repository, 'transaction')->getValue($repository));
        }
    }
}
