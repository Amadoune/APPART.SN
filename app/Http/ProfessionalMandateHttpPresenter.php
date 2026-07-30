<?php

namespace App\Http;

use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\ProfessionalMandateResolutionStatusV1;
use Appart\Modules\Professionals\Application\ProfessionalMandateResolution\ProfessionalMandateResolutionV1;

final class ProfessionalMandateHttpPresenter
{
    public function map(ProfessionalMandateResolutionV1 $result): ProfessionalHttpResponse
    {
        return match ($result->status) {
            ProfessionalMandateResolutionStatusV1::Resolved => new ProfessionalHttpResponse([
                'status' => $result->status->value,
                'professionalId' => $result->professionalId->value,
            ], 200),
            ProfessionalMandateResolutionStatusV1::NotMandated => $this->status($result, 404),
            ProfessionalMandateResolutionStatusV1::Ambiguous => $this->status($result, 409),
            ProfessionalMandateResolutionStatusV1::Corrupted => $this->status($result, 500),
            ProfessionalMandateResolutionStatusV1::DependencyUnavailable => $this->status($result, 503),
        };
    }

    private function status(ProfessionalMandateResolutionV1 $result, int $status): ProfessionalHttpResponse
    {
        return new ProfessionalHttpResponse(['status' => $result->status->value], $status);
    }
}
