<?php

namespace Tests\Unit\ProfessionalMandateOwnerSource;

use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceResult;
use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceState;
use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceStatus;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use Appart\Modules\Professionals\Infrastructure\Persistence\ProfessionalMandateOwnerSourceMapper;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProfessionalMandateOwnerSourceContractTest extends TestCase
{
    #[Test]
    public function results_are_closed_and_only_resolved_exposes_professional_id(): void
    {
        self::assertSame(
            ['resolved', 'not_mandated', 'ambiguous', 'corrupted', 'dependency_unavailable'],
            array_column(ProfessionalMandateOwnerSourceStatus::cases(), 'value'),
        );
        $id = ProfessionalId::fromString('63000000-0000-4000-8000-000000000001');
        self::assertSame($id, ProfessionalMandateOwnerSourceResult::resolved($id)->professionalId);
        foreach ([
            ProfessionalMandateOwnerSourceResult::notMandated(),
            ProfessionalMandateOwnerSourceResult::ambiguous(),
            ProfessionalMandateOwnerSourceResult::corrupted(),
            ProfessionalMandateOwnerSourceResult::dependencyUnavailable(),
        ] as $result) {
            self::assertNull($result->professionalId);
        }
    }

    #[Test]
    public function mapper_accepts_only_canonical_sorted_unique_ids(): void
    {
        $mapper = new ProfessionalMandateOwnerSourceMapper;
        $state = new ProfessionalMandateOwnerSourceState(
            '63000000-0000-4000-8000-000000000010',
            [
                '63000000-0000-4000-8000-000000000001',
                '63000000-0000-4000-8000-000000000002',
            ],
            1,
            '63000000-0000-4000-8000-000000000011',
            str_repeat('a', 64),
            new DateTimeImmutable('2026-07-28T12:00:00+00:00'),
        );
        $parameters = $mapper->parameters($state);

        self::assertSame(
            '["63000000-0000-4000-8000-000000000001","63000000-0000-4000-8000-000000000002"]',
            $parameters['professional_ids'],
        );

        $this->expectException(InvalidArgumentException::class);
        $mapper->parameters(new ProfessionalMandateOwnerSourceState(
            $state->accountId,
            array_reverse($state->professionalIds),
            1,
            $state->intentId,
            $state->intentChecksum,
            $state->recordedAt,
        ));
    }
}
