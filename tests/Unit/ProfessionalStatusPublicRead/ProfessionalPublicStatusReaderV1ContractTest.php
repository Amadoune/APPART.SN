<?php

namespace Tests\Unit\ProfessionalStatusPublicRead;

use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\Contract\ProfessionalPublicStatusReaderV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\ProfessionalPublicStatusDecisionV1;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProfessionalPublicStatusReaderV1ContractTest extends TestCase
{
    #[Test]
    public function result_catalogue_is_closed_versioned_and_fail_closed(): void
    {
        self::assertSame(
            ['available', 'unavailable', 'missing', 'corrupted', 'dependency_unavailable'],
            array_column(ProfessionalPublicStatusDecisionV1::cases(), 'value'),
        );

        $reader = new class implements ProfessionalPublicStatusReaderV1
        {
            public function read(ProfessionalId $professionalId): ProfessionalPublicStatusDecisionV1
            {
                return ProfessionalPublicStatusDecisionV1::DependencyUnavailable;
            }
        };

        self::assertSame(
            ProfessionalPublicStatusDecisionV1::DependencyUnavailable,
            $reader->read(ProfessionalId::fromString('62000000-0000-4000-8000-000000000001')),
        );
    }
}
