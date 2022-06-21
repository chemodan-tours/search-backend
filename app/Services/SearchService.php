<?php

namespace App\Services;

use App\Search\Alean\AleanParser;
use App\Search\SearchDTO;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SearchService
{
    /**
     * @param array $params
     * @return array
     */
    public function search(array $params): array
    {
        $search_id = Str::uuid();

        $dto = new SearchDTO(
            $params['origin'],
            $params['destination'],
            Carbon::parse($params['departure_date']),
            Carbon::parse($params['return_date']),
            $params['tourists']['adults'],
            $params['tourists']['children'],
            $params['tourists']['babies'],
        );

        $search_started_at = microtime(true);
        $search_result = (new AleanParser($dto))->parse();
        $search_ended_at = microtime(true);

        $search_time = $search_ended_at = $search_started_at;

        return [
            'search_id' => $search_id,
            'params' => $params,
            'time' => $search_time,
            'result' => $search_result,
        ];
    }
}
