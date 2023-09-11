<?php
namespace App\Services;

use App\Constants\LocationTypes;
use Illuminate\Support\Facades\Log;

class LocationFormatterService
{
    public function formatLocations(string $locationType, array $locations): array
    {
        $formattedLocations = [];

        foreach ($locations as $location) {
            if ($locationType === LocationTypes::PARK_AND_RIDE) {
                $formattedLocations[] = $this->formatParkAndRide($location);
            } elseif ($locationType === LocationTypes::PARKING_SPACE) {
                $formattedLocations[] = $this->formatParkingSpace($location);
            }
        }

        return $formattedLocations;
    }

    protected function formatParkAndRide(array $location): array
    {
        return [
            'description' => "Park and Ride to {$location['attraction_name']}. (approx {$location['minutes_to_destination']} minutes to destination)",
            'location_name' => $location['location_description'],
        ];
    }

    protected function formatParkingSpace(array $location): array
    {
        return [
            'description' => "Parking space with {$location['no_of_spaces']} bays: {$location['space_details']}",
            'location_name' => "{$location['street_name']}, {$location['city']}",
        ];
    }
}
