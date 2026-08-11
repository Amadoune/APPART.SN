<?php

namespace App\Http\Controllers;

use App\Application\PublicSearchResults\Contract\PublicSearchResultsReaderV1;
use App\Http\Requests\PublicSearchResultsRequest;
use Illuminate\Contracts\View\View;

final class PublicSearchExperienceController extends Controller
{
    public function __construct(private readonly PublicSearchResultsReaderV1 $reader) {}

    public function __invoke(PublicSearchResultsRequest $request): View
    {
        $query = $request->publicQuery();
        $result = $this->reader->read($query);

        return view('public-search-results', [
            'query' => $query,
            'result' => $result,
        ]);
    }
}
