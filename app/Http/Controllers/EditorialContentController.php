<?php

namespace App\Http\Controllers;

use App\Http\ContentSeo\ContentSeoHttpRuntimeV1;
use App\Http\ContentSeo\ContentSeoResponseFactory;
use App\Http\Requests\EditorialContentRequest;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\EditorialContentReaderV1;
use Illuminate\Http\JsonResponse;

final class EditorialContentController extends Controller implements ContentSeoHttpRuntimeV1
{
    public function __construct(private readonly EditorialContentReaderV1 $reader, private readonly ContentSeoResponseFactory $responses) {}

    public function __invoke(EditorialContentRequest $request): JsonResponse
    {
        return $this->responses->editorial($this->reader->read($request->resourceKey(), $request->observedAt()));
    }
}
