<?php

namespace Tests\Unit\ProfessionalProfileHttp;

use App\Http\ProfessionalMandateHttpPresenter;
use App\Http\ProfessionalStatusHttpPresenter;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\ProfessionalMandateResolutionV1;
use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\ProfessionalPublicStatusDecisionV1;
use Appart\Modules\Professionals\Domain\ValueObject\ProfessionalId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProfessionalHttpResponseMapperTest extends TestCase
{
    #[Test]
    public function mandate_mapper_is_exhaustive_and_never_exposes_an_id_outside_resolved(): void
    {
        $id = ProfessionalId::fromString('63000000-0000-4000-8000-000000000301');
        $cases = [
            [ProfessionalMandateResolutionV1::resolved($id), 200],
            [ProfessionalMandateResolutionV1::notMandated(), 404],
            [ProfessionalMandateResolutionV1::ambiguous(), 409],
            [ProfessionalMandateResolutionV1::corrupted(), 500],
            [ProfessionalMandateResolutionV1::dependencyUnavailable(), 503],
        ];
        $mapper = new ProfessionalMandateHttpPresenter;

        foreach ($cases as [$result, $expectedStatus]) {
            $response = $mapper->map($result);
            self::assertSame($expectedStatus, $response->status);
            self::assertSame($result->status->value, $response->body['status']);
            self::assertSame(
                $result->professionalId?->value,
                $response->body['professionalId'] ?? null,
            );
        }
    }

    #[Test]
    public function status_mapper_is_exhaustive_and_deterministic(): void
    {
        $cases = [
            ProfessionalPublicStatusDecisionV1::Available->value => 200,
            ProfessionalPublicStatusDecisionV1::Unavailable->value => 409,
            ProfessionalPublicStatusDecisionV1::Missing->value => 404,
            ProfessionalPublicStatusDecisionV1::Corrupted->value => 500,
            ProfessionalPublicStatusDecisionV1::DependencyUnavailable->value => 503,
        ];
        $mapper = new ProfessionalStatusHttpPresenter;

        foreach (ProfessionalPublicStatusDecisionV1::cases() as $decision) {
            $response = $mapper->map($decision);
            self::assertSame($cases[$decision->value], $response->status);
            self::assertSame(['status' => $decision->value], $response->body);
        }
    }
}
