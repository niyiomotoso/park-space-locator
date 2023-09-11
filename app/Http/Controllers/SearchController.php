<?php

namespace App\Http\Controllers;

use App\Constants\LocationTypes;
use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use App\Rules\Coordinates;
use App\Services\LocationFormatterService;
use App\Services\SearchService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SearchController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function index(
        SearchService $searchService,
        ParkAndRideRankerGateway $parkAndRideGateway,
        ParkingSpaceRankerGateway $parkingSpaceGateway
    ) {
        // @todo Part 3) validate lat long
        $request = request();

        $request->validate([
            'lat' => ['required', new Coordinates],
            'lng' => ['required', new Coordinates],
        ]);

        $boundingBox = $searchService->getBoundingBox(request()->input('lat'), request()->input('lng'), 5);
        $parkingSpaces = $searchService->searchParkingSpaces($boundingBox, ['id', 'name', 'lng', 'lat', 'user_id']);
        // @todo Part 2) rank parking spaces
        $rankedParkingSpaces = $parkingSpaceGateway->rank($parkingSpaces->toArray());

        $parkAndRide = $searchService->searchParkAndRide($boundingBox, ['id', 'name', 'lng', 'lat', 'user_id']);
        $rankedParkAndRide = $parkAndRideGateway->rank($parkAndRide->toArray());

        $resultArray = array_merge($rankedParkAndRide, $rankedParkingSpaces);
        /*@todo Part 2)*/
        // @todo Part 3)  N+1 queries inside the resource transformer
        return response()->json($resultArray);
    }

    public function details(SearchService $searchService, LocationFormatterService $formatterService)
    {
        // @todo Part 3) validate lat long
        $boundingBox = $searchService->getBoundingBox(request()->input('lat'), request()->input('lng'), 5);
        $parkingSpaces = $searchService->searchParkingSpaces($boundingBox);
        $parkAndRide = $searchService->searchParkAndRide($boundingBox);
        $searchResponse = [];

        if (!empty($parkAndRide))
        {
            $searchResponse = $formatterService->formatLocations(LocationTypes::PARK_AND_RIDE, $parkAndRide->toArray());
        }

        if (!empty($parkingSpaces))
        {
            $searchResponse = array_merge($searchResponse, $formatterService->formatLocations(LocationTypes::PARKING_SPACE, $parkingSpaces->toArray()));
        }

        return response()->json($searchResponse); /*@todo Part 1) */
    }
}
