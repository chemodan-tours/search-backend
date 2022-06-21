<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\SearchResource;
use App\Services\SearchService;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $service,
    ) {}

    /**
     * Handle the incoming request.
     *
     * @param \App\Http\Requests\SearchRequest $request
     * @return \App\Http\Resources\SearchResource
     */
    public function __invoke(SearchRequest $request): SearchResource
    {
        return new SearchResource(
            $this->service->search($request->validated())
        );
    }
}
