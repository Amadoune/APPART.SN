<?php

namespace Tests\Unit\ListingRevisionAuthority;

use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\DeterministicListingRevisionAllocatorV1;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\ListingRevisionIntentId;
use Appart\Modules\ListingLifecycle\Application\RevisionAuthority\ListingRevisionOperation;
use Appart\Modules\ListingLifecycle\Domain\ValueObject\ListingId;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ListingRevisionAuthorityTest extends TestCase
{
    private const string LISTING = 'a1000000-0000-4000-8000-000000000001';

    private const string INTENT = 'a1000000-0000-4000-8000-000000000002';

    #[DataProvider('operations')]
    public function test_replay_converges_to_one_valid_revision(ListingRevisionOperation $operation): void
    {
        $allocator = new DeterministicListingRevisionAllocatorV1;
        $first = $allocator->allocate($this->listing(), $operation, $this->intent());
        $replay = $allocator->allocate($this->listing(), $operation, $this->intent());

        self::assertTrue($first->equals($replay));
        self::assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $first->value);
        self::assertSame('5', $first->value[14]);
    }

    public function test_operation_listing_and_intent_are_identity_boundaries(): void
    {
        $allocator = new DeterministicListingRevisionAllocatorV1;
        $baseline = $allocator->allocate($this->listing(), ListingRevisionOperation::Submit, $this->intent())->value;

        self::assertNotSame($baseline, $allocator->allocate($this->listing(), ListingRevisionOperation::BeginReview, $this->intent())->value);
        self::assertNotSame($baseline, $allocator->allocate(ListingId::fromString('a1000000-0000-4000-8000-000000000003'), ListingRevisionOperation::Submit, $this->intent())->value);
        self::assertNotSame($baseline, $allocator->allocate($this->listing(), ListingRevisionOperation::Submit, ListingRevisionIntentId::fromString('a1000000-0000-4000-8000-000000000004'))->value);
    }

    public function test_non_uuid_intent_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ListingRevisionIntentId::fromString('technical-now-value');
    }

    /** @return iterable<string, array{ListingRevisionOperation}> */
    public static function operations(): iterable
    {
        foreach (ListingRevisionOperation::cases() as $operation) {
            yield $operation->value => [$operation];
        }
    }

    private function listing(): ListingId
    {
        return ListingId::fromString(self::LISTING);
    }

    private function intent(): ListingRevisionIntentId
    {
        return ListingRevisionIntentId::fromString(self::INTENT);
    }
}
