<?php

namespace Tests\Unit\HistoricalRedirect;

use Appart\Modules\ContentSeo\Application\HistoricalRedirect\Exception\InvalidHistoricalRedirectResolution;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalCanonical;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectDiagnostic;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectDiagnosticCode;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectResolution;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectStatus;
use Appart\Modules\ContentSeo\Application\HistoricalRedirect\HistoricalRedirectTarget;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class HistoricalRedirectResolutionTest extends TestCase
{
    public function test_valid_resolution_exposes_only_a_public_canonical_target(): void
    {
        $source = $this->historical('ancienne-annonce');
        $target = HistoricalRedirectTarget::publicCanonical($this->canonical('annonce-actuelle'));

        $resolution = HistoricalRedirectResolution::resolved($source, $target);

        self::assertSame(HistoricalRedirectStatus::Resolved, $resolution->status);
        self::assertSame($target, $resolution->target);
        self::assertNull($resolution->diagnostic);
    }

    /** @return iterable<string, array{HistoricalRedirectStatus, HistoricalRedirectDiagnosticCode, string}> */
    public static function failures(): iterable
    {
        yield 'canonical inconnue' => [HistoricalRedirectStatus::NotFound, HistoricalRedirectDiagnosticCode::UnknownHistoricalCanonical, 'notFound'];
        yield 'destination absente' => [HistoricalRedirectStatus::DestinationMissing, HistoricalRedirectDiagnosticCode::PublicDestinationMissing, 'destinationMissing'];
        yield 'boucle' => [HistoricalRedirectStatus::LoopDetected, HistoricalRedirectDiagnosticCode::DestinationEqualsSource, 'loopDetected'];
        yield 'chaine' => [HistoricalRedirectStatus::ChainDetected, HistoricalRedirectDiagnosticCode::DestinationIsHistorical, 'chainDetected'];
        yield 'ambiguite' => [HistoricalRedirectStatus::Ambiguous, HistoricalRedirectDiagnosticCode::MultiplePublicDestinations, 'ambiguous'];
        yield 'corruption' => [HistoricalRedirectStatus::Corrupted, HistoricalRedirectDiagnosticCode::StoredDecisionCorrupted, 'corrupted'];
    }

    #[DataProvider('failures')]
    public function test_each_failure_is_closed_typed_and_has_no_target(
        HistoricalRedirectStatus $status,
        HistoricalRedirectDiagnosticCode $code,
        string $factory,
    ): void {
        $resolution = HistoricalRedirectResolution::{$factory}($this->historical('ancienne-annonce'));

        self::assertSame($status, $resolution->status);
        self::assertNull($resolution->target);
        self::assertSame($code, $resolution->diagnostic?->code);
    }

    public function test_resolved_factory_rejects_a_loop(): void
    {
        $canonical = $this->canonical('meme-annonce');

        $this->expectException(InvalidHistoricalRedirectResolution::class);
        HistoricalRedirectResolution::resolved(
            HistoricalCanonical::declared($canonical),
            HistoricalRedirectTarget::publicCanonical($canonical),
        );
    }

    public function test_status_set_is_exhaustive(): void
    {
        self::assertSame(
            ['resolved', 'not_found', 'destination_missing', 'loop_detected', 'chain_detected', 'ambiguous', 'corrupted'],
            array_column(HistoricalRedirectStatus::cases(), 'value'),
        );
    }

    /** @param class-string $class */
    #[DataProvider('immutableModels')]
    public function test_contract_models_are_final_and_readonly(string $class): void
    {
        $reflection = new ReflectionClass($class);

        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
    }

    /** @return iterable<array{class-string}> */
    public static function immutableModels(): iterable
    {
        yield [HistoricalCanonical::class];
        yield [HistoricalRedirectTarget::class];
        yield [HistoricalRedirectResolution::class];
        yield [HistoricalRedirectDiagnostic::class];
    }

    private function historical(string $slug): HistoricalCanonical
    {
        return HistoricalCanonical::declared($this->canonical($slug));
    }

    private function canonical(string $slug): CanonicalUrl
    {
        return CanonicalUrl::fromString("https://appart.sn/annonces/{$slug}");
    }
}
