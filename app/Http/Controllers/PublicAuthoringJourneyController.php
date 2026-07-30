<?php

namespace App\Http\Controllers;

use App\Application\PublicAuthoringIntegration\Contract\PublicAuthoringJourney;
use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyRequest;
use App\Application\PublicAuthoringIntegration\PublicAuthoringJourneyStatus;
use App\Http\Requests\PublicAuthoringJourneyHttpRequest;
use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Throwable;

final class PublicAuthoringJourneyController extends Controller
{
    public function __construct(private readonly PublicAuthoringJourney $journey) {}

    public function __invoke(PublicAuthoringJourneyHttpRequest $request): JsonResponse
    {
        try {
            $account = $request->attributes->get('iam_account_id');
            if (! $account instanceof AccountId) {
                return $this->response(PublicAuthoringJourneyStatus::NotFound);
            }
            /** @var array<string, mixed> $input */
            $input = $request->safe()->except([
                '_intentId', 'propertyId', 'listingId', 'expectedVersion', 'requestedAt',
            ]);
            $result = $this->journey->execute(new PublicAuthoringJourneyRequest(
                $request->operation(),
                (string) $request->validated('_intentId'),
                $account->value,
                $request->validated('propertyId'),
                $request->validated('listingId'),
                (int) $request->validated('expectedVersion'),
                $input,
                new DateTimeImmutable((string) $request->validated('requestedAt')),
            ));

            return $this->response($result->status, $result->data);
        } catch (Throwable) {
            return $this->response(PublicAuthoringJourneyStatus::Unavailable);
        }
    }

    /** @param array<string, mixed> $data */
    private function response(PublicAuthoringJourneyStatus $status, array $data = []): JsonResponse
    {
        return new JsonResponse(['status' => $status->value] + $data, match ($status) {
            PublicAuthoringJourneyStatus::Succeeded,
            PublicAuthoringJourneyStatus::AcceptedReplay => 200,
            PublicAuthoringJourneyStatus::NotFound => 404,
            PublicAuthoringJourneyStatus::Invalid => 422,
            PublicAuthoringJourneyStatus::Incomplete,
            PublicAuthoringJourneyStatus::Conflict => 409,
            PublicAuthoringJourneyStatus::Unavailable => 503,
        }, [
            'Cache-Control' => 'no-store',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
