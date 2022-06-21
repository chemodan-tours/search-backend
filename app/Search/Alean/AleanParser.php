<?php

namespace App\Search\Alean;

use App\Search\BaseParser;
use App\Search\SearchDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use SoapClient;

class AleanParser extends BaseParser
{
    private const AUTH_INTERFACE =
        'http://extgate.alean.ru:8082/webservice/ewebsvc.dll/wsdl/IewsServer';

    private const  SEARCH_INTERFACE =
        'http://extgate.alean.ru:8082/webservice/ewebsvc.dll/wsdl/ItwsReservationService';

    private string $session = '';

    private array $params;

    private SoapClient $auth_client;
    private SoapClient $search_client;

    public function __construct(SearchDTO $params)
    {
        ini_set('default_socket_timeout', 180);

        $this->auth_client = new SoapClient(self::AUTH_INTERFACE);
        $this->search_client = new SoapClient(self::SEARCH_INTERFACE);

        $this->session = Cache::remember(
            $this->cacheKey('session'),
            self::AUTH_CACHE_TIMEOUT,
            fn () => $this->authorize()
        );

        $this->params = $this->transform($params);
    }

    /**
     * @throws \Throwable
     */
    private function authorize(): string | null
    {
        $response = $this->auth_client->__soapCall('Login', [
            'ConnectionID' => '',
            'UserAlias' => 'TestWeb',
            'Password' => 'webTest',
            'Language' => 'RUS',
            'ProfileID' => '',
            'ContextXML' => '',
            'Timeout' => self::AUTH_TIMEOUT,
        ]);

        $status = AleanResponseEnum::tryFrom($response['return']);

        throw_if($status !== AleanResponseEnum::SUCCESS);

        return $response['SessionID'];
    }

    private function load_packages()
    {
        $tours = $this->search_client->__soapCall('GetAbodeReservationTable', $this->params);

        return json_decode(json_encode(simplexml_load_string($tours)));
    }

    private function load_hotel_descriptions($hotel)
    {
        $hotel_key = Str::of($hotel)->slug('_');

        $hotel_description = Cache::remember(
            $this->cacheKey("hotel_{$hotel_key}"),
            self::HOTELS_CACHE_TIMEOUT,
            function () use ($hotel) {
                return $this->search_client->__soapCall('GetHotelDescription', [
                    'SessionID' => $this->session,
                    'HotelShortName' => $hotel,
                ]);
            }
        );

        return json_decode(json_encode(simplexml_load_string($hotel_description)));
    }

    public function parse()
    {
        $packages = $this->load_packages();

        $hotels = $packages->HotelList->Hotel;
        $offers = $packages->OfferList->Offer;
        $result = [];

        for ($i = 0; $i < count($offers); $i++)
        {
            $key = $offers[$i]->{'@attributes'}->PacketID;

            $result[$key]['Offer'] = $offers[$i];
            $result[$key]['Hotel'] = $this->load_hotel_descriptions(
                $hotels[$i]->{'@attributes'}->ShortName
            );
        }

        return $result;
    }

    public function cacheKey(string $key): string
    {
        return "alean_{$key}";
    }


    public function transform(SearchDTO $dto)
    {
        $days = $dto->return_date->diffInDays($dto->departure_date);
        $tourists = array_merge(
            array_fill(0, $dto->adults, -1),
            array_fill(0, $dto->children, 10),
            array_fill(0, $dto->babies, 1),
        );

        return [
            'SessionID' => $this->session,
            'TourShortNameArray' => null,
            'HotelTypeShortNameArray' => null,
            'HotelGroupShortNameArray' => [ $dto->destination ],
            'HotelShortNameArray' => null,
            'RoomTypeShortNameArray' => null,
            'BaseSeatQuantity' => -1,
            'ExtSeatQuantity' => -1,
            'TouristAgeArray' => $tourists,
            'MinPrice' => -1,
            'MaxPrice' => -1,
            'BeginDateFrom' => $dto->departure_date->toISOString(),
            'BeginDateTill' => $dto->return_date->toISOString(),
            'DurationFrom' => $days,
            'DurationTill' => $days,
            'MaxVisitCount' => 20,
            'MaxOfferCount' => -1,
        ];
    }
}
