<?php

namespace App\Gateways;

use App\ThirdParty\ParkAndRide\ParkAndRideSDK;
use App\ThirdParty\ParkAndRide\RankingRequest;
use App\ThirdParty\TimeoutException;
use Illuminate\Support\Facades\Log;

class ParkAndRideRankerGateway implements RankerGateway
{
    private $parkAndRide;

    public function __construct(ParkAndRideSDK $parkAndRide)
    {
        $this->parkAndRide = $parkAndRide;
    }

    public function rank(array $items)
    {
        $keyedItems = [];
        foreach ($items as $item) {
            $keyedItems[$item['id']] = $item;
        }

        $retryCount = 0;
        $rankedResponse = [];

        while ($retryCount < self::MAX_RETRY) {
            try {
                // Use a timeout for this call
                $rankedResponse = $this->sendRankRequest($keyedItems);
                break;
            } catch (TimeoutException $e) {
                // Request timed out, increment retry count
                $retryCount++;
            }
        }

        $arr = array_column($rankedResponse, 'rank');
        array_multisort($arr, SORT_ASC, $rankedResponse);
        $ranking = array_column($rankedResponse, 'park_and_ride_id');

        Log::info('Got ParkAndRideRankerGateway ranking: ' . json_encode($ranking));

        $rankedItems = [];
        foreach ($ranking as $rank) {
            $rankedItems[] = $keyedItems[$rank];
        }

        return $rankedItems;
    }


    /**
     * @throws TimeoutException
     */
    public function sendRankRequest ($keyedItems)
    {
        return $this->parkAndRide->getRankingResponse(new RankingRequest(array_keys($keyedItems)), self::TIMEOUT)->getResult();
    }
}
