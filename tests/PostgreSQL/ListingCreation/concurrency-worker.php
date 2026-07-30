<?php

use Appart\Modules\ListingLifecycle\Application\Creation\CreateListingDraftCommandV1;
use Appart\Modules\ListingLifecycle\Application\Creation\DeterministicCreateListingDraftV1;
use Appart\Modules\ListingLifecycle\Application\UseCase\CreateDraft;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyId;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingCreationIntentStore;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingRepository;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingTransaction;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Modules\ListingLifecycle\Support\FakePropertyCatalog;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$arguments = $_SERVER['argv'] ?? [];
if (count($arguments) !== 3) {
    exit(2);
}
[$script, $barrier, $worker] = $arguments;
$connection = PostgreSqlTestEnvironment::connection();
$properties = new FakePropertyCatalog;
$properties->set(PropertyId::fromString('55000000-0000-4000-8000-000000000003'), PropertyAvailability::Eligible);
$transaction = new PostgreSqlListingTransaction($connection);
$repository = new PostgreSqlListingRepository($connection, new ListingMapper, $transaction);
$service = new DeterministicCreateListingDraftV1(
    new CreateDraft($repository, $properties, new ListingTransitionPolicy),
    new PostgreSqlListingCreationIntentStore($connection),
    $transaction,
);
$command = new CreateListingDraftCommandV1(
    '55000000-0000-4000-8000-000000000001',
    '55000000-0000-4000-8000-000000000002',
    '55000000-0000-4000-8000-000000000003',
    '55000000-0000-4000-8000-000000000004',
    'account:owner',
    new DateTimeImmutable('2026-07-27T14:00:00+00:00'),
);

touch($barrier.'.ready.'.$worker);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}

echo $service->create($command)->status->value;
