<?php

namespace App\Http;

use Appart\Modules\Professionals\Application\ProfessionalStatusPublicRead\ProfessionalPublicStatusDecisionV1;

final class ProfessionalStatusHttpPresenter
{
    public function map(ProfessionalPublicStatusDecisionV1 $decision): ProfessionalHttpResponse
    {
        return new ProfessionalHttpResponse(
            ['status' => $decision->value],
            match ($decision) {
                ProfessionalPublicStatusDecisionV1::Available => 200,
                ProfessionalPublicStatusDecisionV1::Unavailable => 409,
                ProfessionalPublicStatusDecisionV1::Missing => 404,
                ProfessionalPublicStatusDecisionV1::Corrupted => 500,
                ProfessionalPublicStatusDecisionV1::DependencyUnavailable => 503,
            },
        );
    }
}
