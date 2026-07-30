<?php

namespace Tests\Unit\MultiTargetDelivery;

use App\Application\MultiTargetDelivery\MultiTargetPropagationRequest;
use App\Application\MultiTargetDelivery\MultiTargetPropagationSource;
use App\Application\MultiTargetDelivery\MultiTargetPropagationStatus;
use App\Application\MultiTargetDelivery\PagedMultiTargetPropagationStrategy;
use App\Application\PropertyListingResolution\Contract\PropertyListingsResolver;
use App\Application\PropertyListingResolution\PropertyListingsDiagnostic;
use App\Application\PropertyListingResolution\PropertyListingsPage;
use App\Application\PropertyListingResolution\PropertyListingsPageStatus;
use PHPUnit\Framework\TestCase;

final class PagedMultiTargetPropagationStrategyTest extends TestCase
{
    private const string PROPERTY = '9b000000-0000-4000-8000-000000000001';

    public function test_zero_one_and_many_targets_are_preserved_without_deduction(): void
    {
        $empty = $this->strategy([new PropertyListingsPage(PropertyListingsPageStatus::Empty, [], null, true)])
            ->plan($this->property(), null, 2);
        self::assertSame(MultiTargetPropagationStatus::NoTargets, $empty->status);

        $one = $this->strategy([new PropertyListingsPage(PropertyListingsPageStatus::Completed, ['listing-1'], null, true)])
            ->plan($this->property(), null, 2);
        self::assertSame(['listing-1'], $one->listingIds);

        $many = $this->strategy([new PropertyListingsPage(PropertyListingsPageStatus::Found, ['listing-1', 'listing-2'], 'next', false)])
            ->plan($this->property(), null, 2);
        self::assertSame(['listing-1', 'listing-2'], $many->listingIds);
        self::assertSame('next', $many->nextCheckpoint);
    }

    public function test_media_and_property_use_the_same_explicit_normalized_property_route(): void
    {
        $page = new PropertyListingsPage(PropertyListingsPageStatus::Completed, ['listing-1'], null, true);
        $strategy = $this->strategy([$page, $page]);
        $property = $strategy->plan($this->property(), null, 10);
        $media = $strategy->plan(new MultiTargetPropagationRequest(MultiTargetPropagationSource::Media, 'media-1', self::PROPERTY), null, 10);

        self::assertSame($property->listingIds, $media->listingIds);
        self::assertSame(MultiTargetPropagationSource::Media, $media->request->source);
    }

    public function test_interruption_replays_the_same_page_then_resumes_without_loss(): void
    {
        $first = new PropertyListingsPage(PropertyListingsPageStatus::Found, ['listing-1', 'listing-2'], 'next', false);
        $last = new PropertyListingsPage(PropertyListingsPageStatus::Completed, ['listing-3'], null, true);
        $strategy = $this->strategy([$first, $first, $last]);

        $attempt = $strategy->plan($this->property(), null, 2);
        $redelivery = $strategy->plan($this->property(), null, 2);
        $resume = $strategy->plan($this->property(), $redelivery->nextCheckpoint, 2);

        self::assertEquals($attempt, $redelivery);
        self::assertSame(['listing-1', 'listing-2', 'listing-3'], array_values(array_unique([...$redelivery->listingIds, ...$resume->listingIds])));
        self::assertTrue($resume->completed);
    }

    public function test_diagnostics_are_forwarded_exhaustively(): void
    {
        $invalid = new PropertyListingsPage(PropertyListingsPageStatus::InvalidIdentity, [], null, true, PropertyListingsDiagnostic::InvalidPropertyIdentity);
        $corrupted = new PropertyListingsPage(PropertyListingsPageStatus::Corrupted, [], null, true, PropertyListingsDiagnostic::InvalidCheckpoint);
        $strategy = $this->strategy([$invalid, $corrupted]);

        self::assertSame(MultiTargetPropagationStatus::InvalidIdentity, $strategy->plan($this->property(), null, 1)->status);
        self::assertSame(MultiTargetPropagationStatus::Corrupted, $strategy->plan($this->property(), null, 1)->status);
    }

    /** @param list<PropertyListingsPage> $pages */
    private function strategy(array $pages): PagedMultiTargetPropagationStrategy
    {
        return new PagedMultiTargetPropagationStrategy(new class($pages) implements PropertyListingsResolver
        {
            /** @param list<PropertyListingsPage> $pages */
            public function __construct(private array $pages) {}

            public function readPage(string $propertyId, ?string $checkpoint, int $limit): PropertyListingsPage
            {
                return array_shift($this->pages) ?? throw new \LogicException('Missing page.');
            }
        });
    }

    private function property(): MultiTargetPropagationRequest
    {
        return new MultiTargetPropagationRequest(MultiTargetPropagationSource::Property, self::PROPERTY, self::PROPERTY);
    }
}
