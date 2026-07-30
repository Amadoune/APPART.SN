<?php

use Appart\Modules\ContactsLeads\Application\LeadEligibilitySourceData\LeadEligibilityMaterialization;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserEligibility;
use Appart\Modules\ContactsLeads\Domain\ValueObject\AdvertiserId;
use Appart\Modules\ContactsLeads\Domain\ValueObject\EligibilityRevision;
use Appart\Modules\ContactsLeads\Domain\ValueObject\LeadTimestamp;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingContactability;
use Appart\Modules\ContactsLeads\Domain\ValueObject\ListingId;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\LeadEligibilitySourceDataMapper;
use Appart\Modules\ContactsLeads\Infrastructure\Persistence\PostgreSql\PostgreSqlLeadEligibilityDecisionStore;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;

require dirname(__DIR__, 3).'/vendor/autoload.php';

[$script, $barrier, $worker, $mode] = $argv;
touch($barrier.'.ready.'.$worker);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}

$version = $mode === 'successive' ? (int) $worker + 1 : 1;
if ($mode === 'successive' && $worker === '2') {
    usleep(150000);
}
$relationNumber = $mode === 'divergent' ? (int) $worker : 1;
$listing = ListingId::fromString('b4400000-0000-4000-8000-000000000001');
$advertiser = AdvertiserId::fromString('b4500000-0000-4000-8000-'.sprintf('%012d', $relationNumber));
$evaluated = AdvertiserId::fromString('b4500000-0000-4000-8000-000000000001');
$revision = new EligibilityRevision(
    'b4600000-0000-4000-8000-'.sprintf('%012d', $version),
    $version,
    LeadTimestamp::at(new DateTimeImmutable(sprintf('2026-07-22T%02d:00:00+00:00', 9 + $version))),
);
$lot = new LeadEligibilityMaterialization($listing, $advertiser, ListingContactability::Contactable, $revision, $evaluated, AdvertiserEligibility::EligibleRecipient, $revision);
$store = new PostgreSqlLeadEligibilityDecisionStore(PostgreSqlTestEnvironment::connection(), new LeadEligibilitySourceDataMapper);
echo $store->materialize($lot)->value;
