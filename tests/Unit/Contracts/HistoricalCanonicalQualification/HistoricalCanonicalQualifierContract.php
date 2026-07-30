<?php

namespace Tests\Unit\Contracts\HistoricalCanonicalQualification;

use Appart\Modules\ContentSeo\Application\Contract\HistoricalCanonicalQualifier;
use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualificationDiagnosticCode;
use Appart\Modules\ContentSeo\Application\HistoricalCanonicalQualification\HistoricalCanonicalQualificationStatus;
use Appart\Modules\ContentSeo\Domain\ValueObject\CanonicalUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

abstract class HistoricalCanonicalQualifierContract extends TestCase
{
    abstract protected function qualifier(): HistoricalCanonicalQualifier;

    abstract protected function persist(CanonicalUrl $canonical, string $qualification, int $revision = 1): void;

    public function test_absent_decision_is_unknown(): void
    {
        $result = $this->qualifier()->qualify($this->canonical());

        self::assertSame(HistoricalCanonicalQualificationStatus::Unknown, $result->status);
        self::assertSame(HistoricalCanonicalQualificationDiagnosticCode::CanonicalUnknown, $result->diagnostic?->code);
        self::assertNull($result->historicalCanonical);
    }

    /** @return iterable<string, array{string, HistoricalCanonicalQualificationStatus}> */
    public static function validQualifications(): iterable
    {
        yield 'current' => ['current', HistoricalCanonicalQualificationStatus::Current];
        yield 'historical' => ['historical', HistoricalCanonicalQualificationStatus::Historical];
    }

    #[DataProvider('validQualifications')]
    public function test_single_decision_is_qualified_exactly(string $stored, HistoricalCanonicalQualificationStatus $status): void
    {
        $canonical = $this->canonical();
        $this->persist($canonical, $stored);

        $result = $this->qualifier()->qualify($canonical);

        self::assertSame($status, $result->status);
        self::assertSame($status === HistoricalCanonicalQualificationStatus::Historical ? $canonical : null, $result->historicalCanonical?->canonical);
        self::assertNull($result->diagnostic);
    }

    public function test_multiple_decisions_are_ambiguous_without_implicit_choice(): void
    {
        $canonical = $this->canonical();
        $this->persist($canonical, 'current');
        $this->persist($canonical, 'historical', 2);

        $result = $this->qualifier()->qualify($canonical);

        self::assertSame(HistoricalCanonicalQualificationStatus::Ambiguous, $result->status);
        self::assertSame(HistoricalCanonicalQualificationDiagnosticCode::ConflictingQualifications, $result->diagnostic?->code);
        self::assertNull($result->historicalCanonical);
    }

    protected function canonical(): CanonicalUrl
    {
        return CanonicalUrl::fromString('https://appart.sn/annonces/canonical-publique');
    }
}
