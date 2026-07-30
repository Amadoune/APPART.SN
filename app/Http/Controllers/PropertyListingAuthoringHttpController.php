<?php

namespace App\Http\Controllers;

use App\Application\PropertyListingAuthoringHttp\Contract\PropertyListingAuthoringHttpRuntime;
use App\Application\PropertyListingAuthoringHttp\PropertyListingAuthoringHttpStatus;
use App\Http\Requests\PropertyListingAuthoringHttpRequest;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Illuminate\Http\JsonResponse;
use Throwable;

final class PropertyListingAuthoringHttpController extends Controller
{
    public function __construct(private readonly PropertyListingAuthoringHttpRuntime $runtime) {}

    public function __invoke(PropertyListingAuthoringHttpRequest $request): JsonResponse
    {
        try {
            $account = $request->attributes->get('iam_account_id');
            if (! $account instanceof AccountId) {
                return $this->respond(PropertyListingAuthoringHttpStatus::NotFoundOrForbidden);
            }
            /** @var array<string, mixed> $input */
            $input = $request->safe()->except(['_intentId']);
            $resourceId = $request->route('propertyId') ?? $request->route('listingId');
            $result = $this->runtime->execute(
                $request->operation(),
                $account->value,
                is_string($resourceId) ? $resourceId : null,
                $request->operation()->isRead() ? null : (string) $request->validated('_intentId'),
                $input,
            );

            return $this->respond($result->status, $result->data);
        } catch (Throwable) {
            return $this->respond(PropertyListingAuthoringHttpStatus::Unavailable);
        }
    }

    /** @param array<string, mixed> $data */
    private function respond(PropertyListingAuthoringHttpStatus $status, array $data = []): JsonResponse
    {
        $code = match ($status) {
            PropertyListingAuthoringHttpStatus::Succeeded => 200,
            PropertyListingAuthoringHttpStatus::Created => 201,
            PropertyListingAuthoringHttpStatus::NotFoundOrForbidden => 404,
            PropertyListingAuthoringHttpStatus::Invalid => 422,
            PropertyListingAuthoringHttpStatus::Conflict => 409,
            PropertyListingAuthoringHttpStatus::Unavailable => 503,
        };

        return new JsonResponse(['status' => $status->value] + $data, $code, [
            'Cache-Control' => 'no-store',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
