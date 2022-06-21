<?php

namespace App\Search\Contracts;

use App\Search\SearchDTO;

interface Parser
{
    public function parse();
    public function cacheKey(string $key): string;
    public function transform(SearchDTO $dto);
}
