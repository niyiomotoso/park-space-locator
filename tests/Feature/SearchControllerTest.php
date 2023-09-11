<?php

namespace Tests\Feature;

use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use App\Models\ParkAndRide;
use App\Models\ParkingSpace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testSearchEndpointValidation()
    {

        $response = $this->json('GET','/api/search?lat=INVALID&lng=INVALID');

        // endpoint returns 422 response
        $response->assertStatus(422);
    }

    public function testSearchEndpointUsesGatewayRanking()
    {
        $EXPECTED_RESPONSE_COUNT = 3;
        ParkAndRide::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'attraction_name' => 'disneyland',
            'location_description' => 'TCR',
            'minutes_to_destination' => 10,
            'user_id' => 1,
        ]);

        ParkingSpace::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'space_details' => 'Driveway off street',
            'city' => 'London',
            'street_name' => 'Oxford Street',
            'no_of_spaces' => 2,
            'user_id' => 2,
        ]);

        ParkingSpace::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'space_details' => 'Driveway on street',
            'city' => 'London',
            'street_name' => 'Cambridge Street',
            'no_of_spaces' => 4,
            'user_id' => 3,
        ]);

        $response = $this->json('GET','/api/search?lat=0.1&lng=0.1');

        // endpoint returns OK response
        $response->assertStatus(200);
        $responseArray = json_decode($response->getContent(), true);
        // endpoint returns the expected response count
        $this->assertCount($EXPECTED_RESPONSE_COUNT, $responseArray);
        // sample response object contains the right keys
        $sampleResponseObject = $responseArray[0];
        $this->assertArrayHasKey( 'id', $sampleResponseObject);
        $this->assertArrayHasKey( 'lng', $sampleResponseObject);
        $this->assertArrayHasKey( 'lat', $sampleResponseObject);
        $this->assertArrayHasKey( 'name', $sampleResponseObject);
        $this->assertArrayHasKey( 'owner', $sampleResponseObject);
        $this->assertArrayHasKey( 'name', $sampleResponseObject['owner']);
    }

    public function testSearchEndpointIsHealthy()
    {
        app()->singleton(ParkAndRideRankerGateway::class, function () {
            $rankerGateway = $this->getMockBuilder(ParkAndRideRankerGateway::class)->disableOriginalConstructor()->getMock();
            $rankerGateway->method('rank')->willReturnArgument(0);
            return $rankerGateway;
        });

        app()->singleton(ParkingSpaceRankerGateway::class, function () {
            $rankerGateway = $this->getMockBuilder(ParkingSpaceRankerGateway::class)->disableOriginalConstructor()->getMock();
            $rankerGateway->method('rank')->willReturnArgument(0);
            return $rankerGateway;
        });

        $response = $this->get('/api/search?lat=0.1&lng=0.1');

        $response->assertStatus(200);
    }

    public function testDetailsEndpoint()
    {
        ParkAndRide::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'attraction_name' => 'disneyland',
            'location_description' => 'TCR',
            'minutes_to_destination' => 10,
        ]);

        ParkingSpace::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'space_details' => 'Driveway off street',
            'city' => 'London',
            'street_name' => 'Oxford Street',
            'no_of_spaces' => 2,
        ]);

        $response = $this->get('/api/details?lat=0.1&lng=0.1');

        $response->assertStatus(200);
        $this->assertEquals(json_encode([
            [
                "description" => "Park and Ride to disneyland. (approx 10 minutes to destination)",
                "location_name" => "TCR"
            ],
            [
                "description" => "Parking space with 2 bays: Driveway off street",
                "location_name" => "Oxford Street, London"
            ]
        ]), $response->getContent());
    }
}
