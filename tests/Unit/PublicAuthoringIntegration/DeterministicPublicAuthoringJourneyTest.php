<?php

namespace Tests\Unit\PublicAuthoringIntegration;

use App\Application\PropertyListingAuthoringOperations\AuthoringOperationCommand;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperationResult;
use App\Application\PropertyListingAuthoringOperations\AuthoringOperationStatus;
use App\Application\PropertyListingAuthoringOperations\Contract\PropertyListingAuthoringOperations;
use App\Application\PublicAuthoringIntegration\DeterministicPublicAuthoringJourney;
use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyOperation;
use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyRequest;
use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DeterministicPublicAuthoringJourneyTest extends TestCase
{
    public function test_journey_translates_public_input_to_the_certified_operations_contract(): void
    {
        $operations = $this->createMock(PropertyListingAuthoringOperations::class);
        $operations->expects(self::once())->method('execute')
            ->with(self::callback(static fn (AuthoringOperationCommand $command): bool => $command->operation->value === 'CreateListing'
                && $command->actorAccountId === '77000000-0000-4000-8000-000000000001'
                && $command->propertyId === '77000000-0000-4000-8000-000000000002'
                && $command->listingId === '77000000-0000-4000-8000-000000000003'
                && $command->intentId === '77000000-0000-4000-8000-000000000004'))
            ->willReturn(new AuthoringOperationResult(AuthoringOperationStatus::Applied, ['version' => 1]));
        $journey = new DeterministicPublicAuthoringJourney($operations);

        $result = $journey->execute(new PublicAuthoringJourneyRequest(
            PublicAuthoringJourneyOperation::CreateListing,
            '77000000-0000-4000-8000-000000000004',
            '77000000-0000-4000-8000-000000000001',
            '77000000-0000-4000-8000-000000000002',
            '77000000-0000-4000-8000-000000000003',
            0,
            ['revisionId' => '77000000-0000-4000-8000-000000000005'],
            new DateTimeImmutable('2026-07-27T20:00:00+00:00'),
        ));

        self::assertSame(PublicAuthoringJourneyStatus::Succeeded, $result->status);
        self::assertSame(['version' => 1], $result->data);
    }
}
