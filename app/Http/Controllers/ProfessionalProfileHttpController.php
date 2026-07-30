<?php

namespace App\Http\Controllers;

use App\Application\ProfessionalEndpoint\Contract\ProfessionalEndpointRuntimeV1;
use App\Http\ProfessionalHttpResponse;
use App\Http\ProfessionalMandateHttpPresenter;
use App\Http\ProfessionalStatusHttpPresenter;
use App\Http\Requests\ProfessionalProfileHttpRequest;
use Illuminate\Http\JsonResponse;

final class ProfessionalProfileHttpController extends Controller
{
    public function __construct(
        private readonly ProfessionalEndpointRuntimeV1 $runtime,
        private readonly ProfessionalMandateHttpPresenter $mandates,
        private readonly ProfessionalStatusHttpPresenter $statuses,
    ) {}

    public function mandate(ProfessionalProfileHttpRequest $request): JsonResponse
    {
        return $this->response($this->mandates->map($this->runtime->mandate($request->accountId())));
    }

    public function status(ProfessionalProfileHttpRequest $request): JsonResponse
    {
        return $this->response($this->statuses->map($this->runtime->status($request->accountId())));
    }

    private function response(ProfessionalHttpResponse $response): JsonResponse
    {
        return new JsonResponse($response->body, $response->status, [
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
