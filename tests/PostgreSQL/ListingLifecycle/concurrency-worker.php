<?php

use Appart\Modules\ListingLifecycle\Domain\Model\TransitionEvidence;
use Appart\Modules\ListingLifecycle\Domain\Policy\ListingTransitionPolicy;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ActorId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingRevisionId;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\PropertyAvailability;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionOrigin;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionReason;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\TransitionTrigger;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\ListingMapper;
use Appart\Modules\ListingLifecycle\Infrastructure\Persistence\PostgreSql\PostgreSqlListingRepository;
use Tests\PostgreSQL\Support\PostgreSqlTestEnvironment;
use Tests\Unit\Contracts\ListingLifecycle\FakeListingRegistryHarness;

require dirname(__DIR__, 3).'/vendor/autoload.php';

[$script, $mode, $barrier, $worker] = $argv;
$fixtures = new FakeListingRegistryHarness;
$repository = new PostgreSqlListingRepository(PostgreSqlTestEnvironment::connection(), new ListingMapper);
$listing = $mode === 'save' ? $repository->find($fixtures->primaryId()) : $fixtures->minimalListing();
if ($listing === null) {
    exit(2);
}
touch($barrier.'.ready.'.$worker);
$deadline = microtime(true) + 10;
while (! is_file($barrier.'.start') && microtime(true) < $deadline) {
    usleep(1000);
}
try {
    if ($mode === 'add') {
        $repository->add($listing);
    } else {
        $listing->submit(
            ListingRevisionId::fromString(sprintf('32000000-0000-4000-8000-%012d', 200 + (int) $worker)),
            new TransitionEvidence(ActorId::fromString('actor:worker-'.$worker), TransitionTrigger::SubmissionConfirmed, TransitionReason::fromString('Concurrent Listing mutation '.$worker.'.'), TransitionOrigin::Advertiser, new DateTimeImmutable('2026-07-17T10:01:00+00:00')),
            new ListingTransitionPolicy,
            PropertyAvailability::Eligible,
        );
        $repository->save($listing, 0);
    }
    echo 'ok';
} catch (Throwable $error) {
    echo $error::class;
}
