<?php

namespace App\Http\Controllers;

use App\Application\ModerationHttp\Contract\ModerationHttpRuntimeV1;
use App\Application\ModerationHttp\ModerationHttpResult;
use App\Application\ModerationHttp\ModerationHttpStatus;
use App\Http\ModerationHttpResponseMapper;
use App\Http\Requests\ModerationHttpRequest;
use Illuminate\Http\JsonResponse;
use Throwable;

final class ModerationHttpController extends Controller
{
    public function __construct(
        private readonly ModerationHttpRuntimeV1 $runtime,
        private readonly ModerationHttpResponseMapper $responses,
    ) {}

    public function __invoke(ModerationHttpRequest $request): JsonResponse
    {
        try {
            /** @var array<string, mixed> $input */
            $input = $request->safe()->except(['_intentId']);
            $resource = $request->route('reportId')
                ?? $request->route('caseId')
                ?? $request->route('queueItemId');
            $result = $this->runtime->execute(
                $request->operation(),
                $request->accountId()->value,
                is_string($resource) ? $resource : null,
                $request->operation()->isMutation() ? (string) $request->validated('_intentId') : null,
                $input,
            );
        } catch (Throwable) {
            $result = new ModerationHttpResult(
                ModerationHttpStatus::Unavailable,
            );
        }

        $response = $this->responses->map($result);

        return new JsonResponse($response['body'], $response['status'], [
            'Cache-Control' => 'no-store, private',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
