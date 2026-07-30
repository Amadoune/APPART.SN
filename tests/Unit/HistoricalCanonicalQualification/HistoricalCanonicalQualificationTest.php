<?php

namespace Tests\Unit\HistoricalCanonicalQualification;

use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualification;
use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualificationDiagnostic;
use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualificationDiagnosticCode;
use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualificationStatus;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class HistoricalCanonicalQualificationTest extends TestCase
{
    public function test_current_never_exposes_a_historical_canonical(): void
    {
        $canonical = $this->canonical();
        $qualification = HistoricalCanonicalQualification::current($canonical);

        self::assertSame(HistoricalCanonicalQualificationStatus::Current, $qualification->status);
        self::assertSame($canonical, $qualification->canonical);
        self::assertNull($qualification->historicalCanonical);
        self::assertNull($qualification->diagnostic);
    }

    public function test_historical_is_the_only_result_exposing_a_historical_canonical(): void
    {
        $canonical = $this->canonical();
        $qualification = HistoricalCanonicalQualification::historical($canonical);

        self::assertSame(HistoricalCanonicalQualificationStatus::Historical, $qualification->status);
        self::assertSame($canonical, $qualification->historicalCanonical?->canonical);
        self::assertNull($qualification->diagnostic);
    }

    /** @return iterable<string, array{HistoricalCanonicalQualificationStatus, HistoricalCanonicalQualificationDiagnosticCode, string}> */
    public static function unresolvedQualifications(): iterable
    {
        yield 'unknown' => [HistoricalCanonicalQualificationStatus::Unknown, HistoricalCanonicalQualificationDiagnosticCode::CanonicalUnknown, 'unknown'];
        yield 'ambiguous' => [HistoricalCanonicalQualificationStatus::Ambiguous, HistoricalCanonicalQualificationDiagnosticCode::ConflictingQualifications, 'ambiguous'];
        yield 'corrupted' => [HistoricalCanonicalQualificationStatus::Corrupted, HistoricalCanonicalQualificationDiagnosticCode::QualificationCorrupted, 'corrupted'];
    }

    #[DataProvider('unresolvedQualifications')]
    public function test_unresolved_qualification_is_typed_and_never_exposes_historical_identity(
        HistoricalCanonicalQualificationStatus $status,
        HistoricalCanonicalQualificationDiagnosticCode $diagnostic,
        string $factory,
    ): void {
        $qualification = HistoricalCanonicalQualification::{$factory}($this->canonical());

        self::assertSame($status, $qualification->status);
        self::assertSame($diagnostic, $qualification->diagnostic?->code);
        self::assertNull($qualification->historicalCanonical);
    }

    public function test_status_set_is_exhaustive_and_stable(): void
    {
        self::assertSame(
            ['current', 'historical', 'unknown', 'ambiguous', 'corrupted'],
            array_column(HistoricalCanonicalQualificationStatus::cases(), 'value'),
        );
    }

    /** @param class-string $class */
    #[DataProvider('immutableModels')]
    public function test_models_are_final_and_readonly(string $class): void
    {
        $reflection = new ReflectionClass($class);

        self::assertTrue($reflection->isFinal());
        self::assertTrue($reflection->isReadOnly());
    }

    /** @return iterable<array{class-string}> */
    public static function immutableModels(): iterable
    {
        yield [HistoricalCanonicalQualification::class];
        yield [HistoricalCanonicalQualificationDiagnostic::class];
    }

    private function canonical(): CanonicalUrl
    {
        return CanonicalUrl::fromString('https://appart.sn/annonces/canonical-publique');
    }
}
