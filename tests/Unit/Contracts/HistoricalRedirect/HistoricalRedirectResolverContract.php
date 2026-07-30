<?php

namespace Tests\Unit\Contracts\HistoricalRedirect;

use Appart\Modules\ContentSeo\Application\Contract\HistoricalRedirectResolver;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalCanonical;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectDiagnosticCode;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectStatus;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

abstract class HistoricalRedirectResolverContract extends TestCase
{
    abstract protected function resolver(): HistoricalRedirectResolver;

    abstract protected function persist(
        HistoricalCanonical $source,
        ?CanonicalUrl $destination,
        ?string $qualification,
        int $revision = 1,
    ): void;

    public function test_unknown_canonical_is_not_found(): void
    {
        $result = $this->resolver()->resolve($this->source());

        self::assertSame(HistoricalRedirectStatus::NotFound, $result->status);
        self::assertSame(HistoricalRedirectDiagnosticCode::UnknownHistoricalCanonical, $result->diagnostic?->code);
    }

    /** @return iterable<string, array{?string, ?string, HistoricalRedirectStatus, HistoricalRedirectDiagnosticCode|null}> */
    public static function singleDecisions(): iterable
    {
        yield 'resolved' => ['nouvelle-annonce', 'current', HistoricalRedirectStatus::Resolved, null];
        yield 'destination missing' => [null, null, HistoricalRedirectStatus::DestinationMissing, HistoricalRedirectDiagnosticCode::PublicDestinationMissing];
        yield 'loop' => ['ancienne-annonce', 'current', HistoricalRedirectStatus::LoopDetected, HistoricalRedirectDiagnosticCode::DestinationEqualsSource];
        yield 'chain' => ['autre-ancienne-annonce', 'historical', HistoricalRedirectStatus::ChainDetected, HistoricalRedirectDiagnosticCode::DestinationIsHistorical];
    }

    #[DataProvider('singleDecisions')]
    public function test_single_decision_is_classified_without_interpretation(
        ?string $destinationSlug,
        ?string $qualification,
        HistoricalRedirectStatus $status,
        ?HistoricalRedirectDiagnosticCode $diagnostic,
    ): void {
        $source = $this->source();
        $destination = $destinationSlug === null ? null : $this->canonical($destinationSlug);
        $this->persist($source, $destination, $qualification);

        $result = $this->resolver()->resolve($source);

        self::assertSame($status, $result->status);
        self::assertSame($diagnostic, $result->diagnostic?->code);
        self::assertSame($status === HistoricalRedirectStatus::Resolved ? $destination?->value : null, $result->target?->canonical->value);
    }

    public function test_multiple_destinations_are_ambiguous_and_none_is_selected(): void
    {
        $source = $this->source();
        $this->persist($source, $this->canonical('destination-a'), 'current');
        $this->persist($source, $this->canonical('destination-b'), 'current', 2);

        $result = $this->resolver()->resolve($source);

        self::assertSame(HistoricalRedirectStatus::Ambiguous, $result->status);
        self::assertSame(HistoricalRedirectDiagnosticCode::MultiplePublicDestinations, $result->diagnostic?->code);
        self::assertNull($result->target);
    }

    protected function source(): HistoricalCanonical
    {
        return HistoricalCanonical::declared($this->canonical('ancienne-annonce'));
    }

    protected function canonical(string $slug): CanonicalUrl
    {
        return CanonicalUrl::fromString("https://appart.sn/annonces/{$slug}");
    }
}
