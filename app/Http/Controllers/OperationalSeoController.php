<?php

namespace App\Http\Controllers;

use App\Http\ContentSeo\ContentSeoHttpRuntimeV1;
use App\Http\ContentSeo\ContentSeoResponseFactory;
use App\Http\Requests\OperationalSeoRequest;
use Appart\Modules\ContentSeo\Application\PublicRead\Contract\OperationalSeoReaderV1;
use Illuminate\Http\JsonResponse;

final class OperationalSeoController extends Controller implements ContentSeoHttpRuntimeV1
{
    public function __construct(private readonly OperationalSeoReaderV1 $reader, private readonly ContentSeoResponseFactory $responses) {}

    public function __invoke(OperationalSeoRequest $request): JsonResponse
    {
        return $this->responses->operational($this->reader->read($request->resourceKey(), $request->observedAt()));
    }
}
