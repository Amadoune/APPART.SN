<?php

namespace Tests\Unit\Modules\ModerationReports;

use Appart\Modules\ModerationReports\Domain\Exception\InvalidModerationValue;
use Appart\Modules\ModerationReports\Domain\ValueObject\DecisionId;
use Appart\Modules\ModerationReports\Domain\ValueObject\FindingId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationCaseId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ModerationRationale;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportId;
use Appart\Modules\ModerationReports\Domain\ValueObject\ReportReason;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ModerationValueObjectsTest extends TestCase
{
    public function test_text_values_are_normalized(): void
    {
        self::assertSame('Photo trompeuse', ReportReason::fromString(' Photo   trompeuse ')->value);
        self::assertSame('Contrôle confirmé', ModerationRationale::fromString(' Contrôle   confirmé ')->value);
    }

    #[DataProvider('invalidValues')]
    public function test_invalid_values_are_rejected(string $type, string $value): void
    {
        $this->expectException(InvalidModerationValue::class);
        match ($type) {
            'case' => ModerationCaseId::fromString($value),'report' => ReportId::fromString($value),'finding' => FindingId::fromString($value),'decision' => DecisionId::fromString($value),'reason' => ReportReason::fromString($value),'rationale' => ModerationRationale::fromString($value)
        };
    }

    public static function invalidValues(): array
    {
        return [['case', 'x'], ['report', 'x'], ['finding', 'x'], ['decision', 'x'], ['reason', 'x'], ['rationale', 'x']];
    }
}
