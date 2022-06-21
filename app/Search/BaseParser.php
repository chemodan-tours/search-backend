<?php

namespace App\Search;

use App\Search\Contracts\Parser;

abstract class BaseParser implements Parser
{
    protected const AUTH_TIMEOUT = 1_800_000;

    protected const AUTH_CACHE_TIMEOUT =
        self::AUTH_TIMEOUT / 1000 / 1.05;

    protected const HOTELS_CACHE_TIMEOUT = 86_400;
}
