<?php

namespace Tests\Unit\ProfessionalMandateResolution;

use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\ProfessionalMandateResolutionStatusV1;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\ProfessionalMandateResolutionV1;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ProfessionalMandateResolverV1ContractTest extends TestCase
{
    #[Test]
    public function result_catalogue_is_closed_and_fail_closed(): void
    {
        self::assertSame(
            ['resolved', 'not_mandated', 'ambiguous', 'corrupted', 'dependency_unavailable'],
            array_column(ProfessionalMandateResolutionStatusV1::cases(), 'value'),
        );

        foreach ([
            ProfessionalMandateResolutionV1::notMandated(),
            ProfessionalMandateResolutionV1::ambiguous(),
            ProfessionalMandateResolutionV1::corrupted(),
            ProfessionalMandateResolutionV1::dependencyUnavailable(),
        ] as $result) {
            self::assertNull($result->professionalId);
        }
    }

    #[Test]
    public function only_resolved_may_expose_a_professional_id(): void
    {
        $professionalId = ProfessionalId::fromString('62000000-0000-4000-8000-000000000001');
        $resolved = ProfessionalMandateResolutionV1::resolved($professionalId);

        self::assertSame(ProfessionalMandateResolutionStatusV1::Resolved, $resolved->status);
        self::assertSame($professionalId, $resolved->professionalId);

        $reflection = new ReflectionClass(ProfessionalMandateResolutionV1::class);
        $constructor = $reflection->getConstructor();
        self::assertNotNull($constructor);

        $this->expectException(LogicException::class);
        $constructor->invoke(
            $reflection->newInstanceWithoutConstructor(),
            ProfessionalMandateResolutionStatusV1::NotMandated,
            $professionalId,
        );
    }
}
