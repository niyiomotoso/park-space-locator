<?php

namespace App\Gateways;

use App\ThirdParty\ParkingSpaceHttpService;
use App\ThirdParty\TimeoutException;
use Illuminate\Support\Facades\Log;

class ParkingSpaceRankerGateway implements RankerGateway
{
    private $parkingSpaceHttpService;
    public function __construct(ParkingSpaceHttpService $parkingSpaceHttpService)
    {
        $this->parkingSpaceHttpService = $parkingSpaceHttpService;
    }
    // TODO Part 2) create the parking space ranker gateway using the ParkingSpaceHttpService
    public function rank(array $items)
    {
        $keyedItems = [];
        foreach ($items as $item) {
            $keyedItems[$item['id']] = $item;
        }

        $ranking = [];
        $retryCount = 0;

        while ($retryCount < self::MAX_RETRY) {
            try {
                $ranking = $this->sendRankRequest($keyedItems);
                break;
            } catch (TimeoutException $e) {
                // Request timed out, increment retry count
                $retryCount++;
            }
        }

        Log::info('Got ParkingSpaceRankerGateway ranking: '. json_encode($ranking));
        return $ranking;
    }

    /**
     * @throws TimeoutException
     */
    public function sendRankRequest ($keyedItems)
    {
        // Use a timeout for this call
        return json_decode($this->parkingSpaceHttpService->getRanking(json_encode($keyedItems), self::TIMEOUT)->getBody()->getContents(), true);
    }
}
