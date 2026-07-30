<?php

namespace App\Http\Controllers;

use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use App\Application\IdentityAccessHttp\IdentityAccessHttpCommand;
use App\Application\IdentityAccessHttp\IdentityAccessHttpOperation;
use App\Application\IdentityAccessHttp\IdentityAccessHttpResult;
use App\Application\IdentityAccessHttp\IdentityAccessHttpStatus;
use App\Http\Requests\IdentityAccessHttpRequest;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Throwable;

final class IdentityAccessHttpController extends Controller
{
    public function __construct(private readonly IdentityAccessHttpRuntime $runtime) {}

    public function __invoke(IdentityAccessHttpRequest $request): JsonResponse
    {
        $operation = $request->operation();
        try {
            /** @var array<string, bool|int|string|null> $input */
            $input = $request->safe()->except(['_intentId']);
            $account = $request->attributes->get('iam_account_id');
            $result = $this->runtime->execute(new IdentityAccessHttpCommand(
                $operation,
                $request->isRead()
                    ? '00000000-0000-4000-8000-000000000000'
                    : (string) $request->validated('_intentId'),
                $input,
                $account instanceof AccountId ? $account : null,
                isset($input['requestedAt']) && is_string($input['requestedAt'])
                    ? new DateTimeImmutable($input['requestedAt'])
                    : new DateTimeImmutable,
            ));
        } catch (Throwable) {
            $result = new IdentityAccessHttpResult(IdentityAccessHttpStatus::Unavailable);
        }

        return $this->respond($operation, $result);
    }

    private function respond(
        IdentityAccessHttpOperation $operation,
        IdentityAccessHttpResult $result,
    ): JsonResponse {
        if ($operation === IdentityAccessHttpOperation::RequestRecovery
            && $result->status !== IdentityAccessHttpStatus::Unavailable) {
            return $this->response(['status' => 'accepted'], 202);
        }

        $response = match ($result->status) {
            IdentityAccessHttpStatus::Succeeded => $this->response(
                ['status' => 'succeeded'] + $result->publicData,
                200,
            ),
            IdentityAccessHttpStatus::Accepted => $this->response(['status' => 'accepted'], 202),
            IdentityAccessHttpStatus::GenericFailure => $this->response(
                ['status' => $operation === IdentityAccessHttpOperation::Login ? 'authentication_failed' : 'request_failed'],
                $operation === IdentityAccessHttpOperation::Login ? 401 : 400,
            ),
            IdentityAccessHttpStatus::Forbidden => $this->response(['status' => 'forbidden'], 403),
            IdentityAccessHttpStatus::Conflict => $this->response(['status' => 'conflict'], 409),
            IdentityAccessHttpStatus::Unavailable => $this->response(['status' => 'unavailable'], 503),
        };

        if ($result->status === IdentityAccessHttpStatus::Succeeded
            && $result->sessionSecret !== null
            && $result->sessionExpiresInSeconds !== null) {
            $response->headers->setCookie($this->sessionCookie(
                $result->sessionSecret,
                $result->sessionExpiresInSeconds,
            ));
        }
        if ($operation === IdentityAccessHttpOperation::Logout
            && $result->status === IdentityAccessHttpStatus::Succeeded) {
            $response->headers->clearCookie(
                (string) config('identity_access_http.cookie.name'),
                (string) config('identity_access_http.cookie.path'),
                config('identity_access_http.cookie.domain'),
                true,
                true,
                (string) config('identity_access_http.cookie.same_site'),
            );
        }

        return $response;
    }

    /** @param array<string, bool|int|string|null> $body */
    private function response(array $body, int $status): JsonResponse
    {
        return new JsonResponse($body, $status, [
            'Cache-Control' => 'no-store',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function sessionCookie(string $secret, int $expiresIn): Cookie
    {
        return Cookie::create(
            (string) config('identity_access_http.cookie.name'),
            $secret,
            new DateTimeImmutable("+{$expiresIn} seconds"),
            (string) config('identity_access_http.cookie.path'),
            config('identity_access_http.cookie.domain'),
            true,
            true,
            false,
            (string) config('identity_access_http.cookie.same_site'),
        );
    }
}
