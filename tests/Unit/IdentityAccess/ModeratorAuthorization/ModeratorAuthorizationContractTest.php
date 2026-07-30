<?php

namespace Tests\Unit\IdentityAccess\ModeratorAuthorization;

use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\Contract\ModeratorAuthorizationReaderV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModerationCapabilityV1;
use Appart\Modules\IdentityAccess\Application\ModeratorAuthorization\ModeratorAuthorizationDecisionV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ModeratorAuthorizationContractTest extends TestCase
{
    #[Test]
    public function capability_and_decision_catalogs_are_closed(): void
    {
        self::assertSame([
            'report',
            'validate',
            'investigate',
            'decide',
            'audit',
        ], array_column(ModerationCapabilityV1::cases(), 'value'));
        self::assertSame([
            'allowed',
            'denied',
            'corrupted',
            'dependency_unavailable',
        ], array_column(ModeratorAuthorizationDecisionV1::cases(), 'value'));
    }

    #[Test]
    public function reader_is_read_only_and_returns_only_a_closed_decision(): void
    {
        $reader = new class implements ModeratorAuthorizationReaderV1
        {
            public function authorize(
                AccountId $accountId,
                ModerationCapabilityV1 $capability,
                DateTimeImmutable $observedAt,
            ): ModeratorAuthorizationDecisionV1 {
                return ModeratorAuthorizationDecisionV1::Denied;
            }
        };

        self::assertSame(
            ModeratorAuthorizationDecisionV1::Denied,
            $reader->authorize(
                AccountId::fromString('53c10000-0000-4000-8000-000000000001'),
                ModerationCapabilityV1::Decide,
                new DateTimeImmutable('2026-07-30T12:00:00+00:00'),
            ),
        );
    }
}
