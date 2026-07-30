<?php

namespace Tests\Unit\ProfessionalProfileRuntime;

use App\Application\ProfessionalProfileRuntime\DeterministicProfessionalProfileRuntimeAvailabilityPolicy;
use App\Application\ProfessionalProfileRuntime\ProfessionalProfileRuntimeStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProfessionalProfileRuntimeAvailabilityPolicyTest extends TestCase
{
    #[Test]
    public function it_is_ready_only_when_all_local_owner_bindings_are_available(): void
    {
        $ready = new DeterministicProfessionalProfileRuntimeAvailabilityPolicy([
            'professional_public_profile' => true,
            'professional_verification' => true,
            'professional_public_portfolio' => true,
        ]);
        self::assertSame(ProfessionalProfileRuntimeStatus::Ready, $ready->inspect()->status);
        self::assertNull($ready->inspect()->componentCode);
        self::assertSame('professional-profile-runtime-v1', $ready->inspect()->policyVersion);

        $missing = new DeterministicProfessionalProfileRuntimeAvailabilityPolicy([
            'professional_public_profile' => true,
            'professional_verification' => false,
            'professional_public_portfolio' => true,
        ]);
        self::assertSame(ProfessionalProfileRuntimeStatus::MissingBinding, $missing->inspect()->status);
        self::assertSame('professional_verification', $missing->inspect()->componentCode);
    }

    #[Test]
    public function diagnostic_outcomes_are_closed_and_do_not_expose_context(): void
    {
        self::assertSame(
            ['Ready', 'MissingBinding', 'DependencyUnavailable', 'IncompatibleVersion'],
            array_column(ProfessionalProfileRuntimeStatus::cases(), 'value'),
        );
    }
}
