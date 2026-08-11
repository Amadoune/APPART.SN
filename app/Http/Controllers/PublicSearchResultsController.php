<?php

namespace App\Http\Controllers;

use App\Application\PublicSearchResults\Contract\PublicSearchResultsReaderV1;
use App\Http\PublicSearchResults\PublicSearchResultsResponseFactory;
use App\Http\Requests\PublicSearchResultsRequest;
use Illuminate\Http\JsonResponse;

final class PublicSearchResultsController extends Controller
{
    public function __construct(
        private readonly PublicSearchResultsReaderV1 $reader,
        private readonly PublicSearchResultsResponseFactory $responses,
    ) {}

    public function __invoke(PublicSearchResultsRequest $request): JsonResponse
    {
        return $this->responses->make($this->reader->read($request->publicQuery()));
    }
}
