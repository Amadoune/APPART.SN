<?php

namespace Tests\Unit\Modules\AdministrationAudit;

use Appart\Modules\AdministrationAudit\Domain\Exception\InvalidAuditValue;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActionType;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ActorId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AdministrativeActionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\ApprovalId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\AuditReason;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\DecisionId;
use Appart\Modules\AdministrationAudit\Domain\ValueObject\TargetResourceId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AdministrationAuditValueObjectsTest extends TestCase
{
    public function test_reason_normalizes_spaces(): void
    {
        self::assertSame('Valid administrative reason.', AuditReason::fromString(' Valid   administrative reason. ')->value);
    }

    #[DataProvider('invalidValues')]
    public function test_invalid_values_are_rejected(string $type, string $value): void
    {
        $this->expectException(InvalidAuditValue::class);
        match ($type) {
            'action_id' => AdministrativeActionId::fromString($value),'approval_id' => ApprovalId::fromString($value),'decision_id' => DecisionId::fromString($value),'actor' => ActorId::fromString($value),'target' => TargetResourceId::fromString($value),'type' => ActionType::fromString($value),'reason' => AuditReason::fromString($value)
        };
    }

    public static function invalidValues(): array
    {
        return [['action_id', 'x'], ['approval_id', 'x'], ['decision_id', 'x'], ['actor', '?'], ['target', 'account'], ['type', 'X'], ['reason', 'short']];
    }
}
