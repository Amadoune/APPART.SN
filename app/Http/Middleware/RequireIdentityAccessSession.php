<?php

namespace App\Http\Middleware;

use App\Application\IdentityAccessHttp\Contract\IdentityAccessHttpRuntime;
use Closure;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final readonly class RequireIdentityAccessSession
{
    public function __construct(private IdentityAccessHttpRuntime $runtime) {}

    public function handle(Request $request, Closure $next): Response
    {
        $secret = $request->cookie((string) config('identity_access_http.cookie.name'));
        if (! is_string($secret) || $secret === '') {
            return $this->unauthorized();
        }

        try {
            $inspection = $this->runtime->inspectSession($secret, new DateTimeImmutable);
            if (! $inspection->valid || $inspection->accountId === null || $inspection->context === null) {
                return $this->unauthorized();
            }
            $request->attributes->set('iam_session_context', $inspection->context);
            $request->attributes->set('iam_account_id', $inspection->accountId);

            return $next($request);
        } catch (Throwable) {
            return $this->unauthorized();
        }
    }

    private function unauthorized(): JsonResponse
    {
        return new JsonResponse(
            ['status' => 'authentication_required'],
            401,
            ['Cache-Control' => 'no-store', 'Pragma' => 'no-cache'],
        );
    }
}
