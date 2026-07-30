<?php

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationActionV1;
use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\OwnerListingModerationCommandGatewayV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\DeterministicListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingPublicationWorkflowMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingModerationIntentStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingModerationIntentTransaction;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationWorkflowRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$barrier = $argv[1];
$worker = $argv[2];
$connection = PostgreSqlTestEnvironment::connection();
$workflows = new PostgreSqlListingPublicationWorkflowRepository(
    $connection,
    new ListingPublicationWorkflowMapper,
);
$intents = new PostgreSqlListingModerationIntentStore($connection);
$gateway = new OwnerListingModerationCommandGatewayV1(
    $intents,
    new PostgreSqlListingModerationIntentTransaction($connection),
    new DeterministicListingPublicationOrchestrator(new ListingPublicationWorkflow, $workflows),
    $workflows,
);

touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}

echo $gateway->apply(
    ListingId::fromString('53c30000-0000-4000-8000-000000000010'),
    ListingModerationActionV1::Suspend,
    '53c30000-0000-4000-8000-000000000011',
    str_repeat('e', 64),
    new DateTimeImmutable('2026-07-30T12:00:00+00:00'),
)->value;
