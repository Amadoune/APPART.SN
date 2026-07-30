<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class RequireAccountStatusLifecycleAuthority
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('account_status_http.bearer_token');
        $presentedToken = $request->bearerToken();
        if (! is_string($configuredToken)
            || strlen($configuredToken) < 32
            || ! is_string($presentedToken)
            || ! hash_equals($configuredToken, $presentedToken)) {
            return new JsonResponse([
                'code' => 'account_status.authentication_required',
            ], 401);
        }

        $requiredScope = (string) config('account_status_http.required_scope');
        if ($request->header('X-Account-Status-Scope') !== $requiredScope) {
            return new JsonResponse([
                'code' => 'account_status.authorization_denied',
            ], 403);
        }

        $request->attributes->set('account_status_authorized', true);

        return $next($request);
    }
}
