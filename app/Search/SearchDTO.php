<?php

namespace App\Search;

use Carbon\Carbon;

class SearchDTO
{
    /**
     * @param string $origin
     * @param string $destination
     * @param \Carbon\Carbon $departure_date
     * @param \Carbon\Carbon $return_date
     * @param int $adults
     * @param int $children
     * @param int $babies
     */
    public function __construct(
        public string $origin,
        public string $destination,
        public Carbon $departure_date,
        public Carbon $return_date,
        public int $adults,
        public int $children,
        public int $babies,
    ) {}
}
