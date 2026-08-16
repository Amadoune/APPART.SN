<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyAuthoringGeographySelectionRequest;
use Appart\Modules\Geography\Application\GeographySelection\Contract\GeographySelectionReaderV1;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionQuery;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionResult;
use Appart\Modules\Geography\Application\GeographySelection\GeographySelectionStatus;
use Appart\Modules\Geography\Domain\ValueObject\PlaceId;
use Appart\Modules\Geography\Domain\ValueObject\PlaceType;
use Illuminate\Http\JsonResponse;
use Throwable;

final class PropertyAuthoringGeographySelectionController extends Controller
{
    public function __construct(private readonly GeographySelectionReaderV1 $reader) {}

    public function __invoke(PropertyAuthoringGeographySelectionRequest $request): JsonResponse
    {
        try {
            $type = PlaceType::from((string) $request->validated('type'));
            $parentValue = $request->validated('parentPlaceId');
            $result = $this->reader->read(new GeographySelectionQuery(
                $type,
                is_string($parentValue) ? PlaceId::fromString($parentValue) : null,
                is_string($request->validated('cursor')) ? $request->validated('cursor') : null,
                (int) ($request->validated('limit') ?? 50),
            ));

            return $this->respond($result);
        } catch (Throwable) {
            return $this->error(422);
        }
    }

    private function respond(GeographySelectionResult $result): JsonResponse
    {
        return match ($result->status) {
            GeographySelectionStatus::Available => new JsonResponse([
                'items' => array_map(static fn ($item): array => [
                    'placeId' => $item->placeId,
                    'label' => $item->label,
                    'type' => $item->type,
                    'parentPlaceId' => $item->parentPlaceId,
                ], $result->items),
                'nextCursor' => $result->nextCursor,
            ], 200, $this->headers()),
            GeographySelectionStatus::Empty => new JsonResponse(['items' => [], 'nextCursor' => null], 200, $this->headers()),
            GeographySelectionStatus::Missing => $this->error(404),
            GeographySelectionStatus::Corrupted => $this->error(500),
            GeographySelectionStatus::DependencyUnavailable => $this->error(503),
        };
    }

    private function error(int $status): JsonResponse
    {
        return new JsonResponse(['error' => match ($status) {
            404 => 'parent_missing',
            422 => 'invalid_query',
            500 => 'corrupted',
            default => 'dependency_unavailable',
        }], $status, $this->headers());
    }

    /** @return array<string, string> */
    private function headers(): array
    {
        return ['Cache-Control' => 'no-store', 'Pragma' => 'no-cache', 'X-Content-Type-Options' => 'nosniff'];
    }
}
