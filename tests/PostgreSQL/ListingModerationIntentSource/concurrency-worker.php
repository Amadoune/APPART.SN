<?php

require dirname(__DIR__, 3).'/vendor/autoload.php';

use Appart\Modules\ListingLifecycle\Application\ModerationBoundary\ListingModerationCommandResultV1;
use Appart\Modules\ListingLifecycle\Application\ModerationIntent\ListingModerationIntentReservationV1;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\DeterministicListingPublicationOrchestrator;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationAction;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationOrchestrationRequest;
use Appart\Modules\ListingLifecycle\Application\PublicationWorkflow\ListingPublicationWorkflow;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingPublicationWorkflowMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingModerationIntentStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingModerationIntentTransaction;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingPublicationWorkflowRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

$barrier = $argv[1];
$worker = $argv[2];
touch($barrier.'.ready.'.$worker);
while (! is_file($barrier.'.start')) {
    usleep(1000);
}
$connection = PostgreSqlTestEnvironment::connection();
$store = new PostgreSqlListingModerationIntentStore($connection);
$transaction = new PostgreSqlListingModerationIntentTransaction($connection);
$repository = new PostgreSqlListingPublicationWorkflowRepository($connection, new ListingPublicationWorkflowMapper);
$orchestrator = new DeterministicListingPublicationOrchestrator(new ListingPublicationWorkflow, $repository);
$commandId = '53c30000-0000-4000-8000-000000000001';
$checksum = str_repeat('a', 64);
$listingId = ListingId::fromString('53c30000-0000-4000-8000-000000000002');
$now = new DateTimeImmutable('2026-07-30T12:00:00+00:00');

$result = $transaction->run(function () use ($store, $orchestrator, $commandId, $checksum, $listingId, $now): string {
    $reservation = $store->reserve($commandId, $checksum, $now);
    if ($reservation === ListingModerationIntentReservationV1::AlreadyApplied) {
        return 'already_applied';
    }
    $transition = $orchestrator->transition(new ListingPublicationOrchestrationRequest(
        $listingId,
        ListingPublicationAction::Suspend,
        1,
    ));
    if ($transition->status->value !== 'applied') {
        return 'version_conflict';
    }
    $store->complete($commandId, $checksum, ListingModerationCommandResultV1::Applied, $now);

    return 'applied';
});
echo $result;
