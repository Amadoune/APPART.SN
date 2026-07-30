<?php

namespace Tests\Unit\ProfessionalProfileHttp;

use App\Application\ProfessionalEndpoint\DeterministicProfessionalEndpointRuntimeV1;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\Contract\ProfessionalMandateResolverV1;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\ProfessionalMandateResolutionV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\Contract\ProfessionalPublicStatusReaderV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\ProfessionalPublicStatusDecisionV1;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DeterministicProfessionalProfileHttpRuntimeV1Test extends TestCase
{
    #[Test]
    public function status_uses_the_certified_auto_scope_chain_and_is_fail_closed(): void
    {
        $accountId = AccountId::fromString('63000000-0000-4000-8000-000000000302');
        $professionalId = ProfessionalId::fromString('63000000-0000-4000-8000-000000000303');
        $resolver = $this->createMock(ProfessionalMandateResolverV1::class);
        $reader = $this->createMock(ProfessionalPublicStatusReaderV1::class);
        $resolver->expects(self::once())->method('resolve')->with($accountId->value)
            ->willReturn(ProfessionalMandateResolutionV1::resolved($professionalId));
        $reader->expects(self::once())->method('read')->with($professionalId)
            ->willReturn(ProfessionalPublicStatusDecisionV1::Available);

        $runtime = new DeterministicProfessionalEndpointRuntimeV1($resolver, $reader);

        self::assertSame(ProfessionalPublicStatusDecisionV1::Available, $runtime->status($accountId));
    }

    #[Test]
    public function unresolved_mandates_never_invoke_the_status_reader(): void
    {
        $accountId = AccountId::fromString('63000000-0000-4000-8000-000000000304');
        $cases = [
            [ProfessionalMandateResolutionV1::notMandated(), ProfessionalPublicStatusDecisionV1::Missing],
            [ProfessionalMandateResolutionV1::ambiguous(), ProfessionalPublicStatusDecisionV1::Corrupted],
            [ProfessionalMandateResolutionV1::corrupted(), ProfessionalPublicStatusDecisionV1::Corrupted],
            [ProfessionalMandateResolutionV1::dependencyUnavailable(), ProfessionalPublicStatusDecisionV1::DependencyUnavailable],
        ];

        foreach ($cases as [$resolution, $expected]) {
            $resolver = $this->createMock(ProfessionalMandateResolverV1::class);
            $reader = $this->createMock(ProfessionalPublicStatusReaderV1::class);
            $resolver->method('resolve')->willReturn($resolution);
            $reader->expects(self::never())->method('read');

            self::assertSame(
                $expected,
                (new DeterministicProfessionalEndpointRuntimeV1($resolver, $reader))->status($accountId),
            );
        }
    }
}
