<?php

namespace Tests\Unit\ProfessionalMandateResolution;

use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\Contract\ProfessionalMandateOwnerSource;
use Appart\Modules\Professionals\Application\ProfessionalMandateOwnerSource\ProfessionalMandateOwnerSourceResult;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\OwnerProfessionalMandateResolverV1;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\ProfessionalMandateResolutionStatusV1;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OwnerProfessionalMandateResolverV1Test extends TestCase
{
    #[Test]
    public function it_translates_only_the_five_certified_owner_source_results(): void
    {
        $professionalId = ProfessionalId::fromString('63000000-0000-4000-8000-000000000101');
        $cases = [
            [ProfessionalMandateOwnerSourceResult::resolved($professionalId), ProfessionalMandateResolutionStatusV1::Resolved],
            [ProfessionalMandateOwnerSourceResult::notMandated(), ProfessionalMandateResolutionStatusV1::NotMandated],
            [ProfessionalMandateOwnerSourceResult::ambiguous(), ProfessionalMandateResolutionStatusV1::Ambiguous],
            [ProfessionalMandateOwnerSourceResult::corrupted(), ProfessionalMandateResolutionStatusV1::Corrupted],
            [ProfessionalMandateOwnerSourceResult::dependencyUnavailable(), ProfessionalMandateResolutionStatusV1::DependencyUnavailable],
        ];

        foreach ($cases as [$sourceResult, $expectedStatus]) {
            $source = $this->createMock(ProfessionalMandateOwnerSource::class);
            $source->expects(self::once())->method('resolve')->with('account-1')->willReturn($sourceResult);

            $result = (new OwnerProfessionalMandateResolverV1($source))->resolve('account-1');

            self::assertSame($expectedStatus, $result->status);
            self::assertSame(
                $expectedStatus === ProfessionalMandateResolutionStatusV1::Resolved ? $professionalId : null,
                $result->professionalId,
            );
        }
    }
}
