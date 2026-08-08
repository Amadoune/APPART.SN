<?php

namespace App\Http\ContentSeo;

use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentResultV1;
use Appart\Modules\ContentSeo\Application\PublicRead\EditorialContentStatusV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoResultV1;
use Appart\Modules\ContentSeo\Application\PublicRead\OperationalSeoStatusV1;
use Illuminate\Http\JsonResponse;

final readonly class ContentSeoResponseFactory
{
    public function editorial(EditorialContentResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            EditorialContentStatusV1::Published,
            EditorialContentStatusV1::Unpublished => 200,
            EditorialContentStatusV1::Missing => 404,
            EditorialContentStatusV1::Corrupted,
            EditorialContentStatusV1::DependencyUnavailable => 503,
        };

        return $this->response($result->status->value, $status);
    }

    public function operational(OperationalSeoResultV1 $result): JsonResponse
    {
        $status = match ($result->status) {
            OperationalSeoStatusV1::Indexable,
            OperationalSeoStatusV1::NoIndex => 200,
            OperationalSeoStatusV1::Missing => 404,
            OperationalSeoStatusV1::Corrupted,
            OperationalSeoStatusV1::DependencyUnavailable => 503,
        };

        return $this->response($result->status->value, $status);
    }

    private function response(string $status, int $httpStatus): JsonResponse
    {
        return new JsonResponse(['status' => $status], $httpStatus, ['Cache-Control' => 'no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
