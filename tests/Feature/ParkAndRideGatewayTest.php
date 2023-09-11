<?php

namespace Tests\Feature;

use App\Gateways\ParkAndRideRankerGateway;
use App\Models\ParkAndRide;
use App\ThirdParty\ParkAndRide\ParkAndRideSDK;
use App\ThirdParty\ParkAndRide\RankingRequest;
use App\ThirdParty\ParkAndRide\RankingResponse;
use App\ThirdParty\TimeoutException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkAndRideGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function testParkAndRideRanker()
    {
        // TODO Part 4)
        $parkAndRide1 = ParkAndRide::factory()->create([
            'name' => 'cons',
            'lat' =>42.598212,
            'lng' =>177.831564,
            'id' => 1,
            'user_id' => 1,
            'attraction_name' => 'quis',
            'location_description' => 'Ut qui consequatur ut est id expedita.',
            'minutes_to_destination' => 50282398,
            'updated_at' => '2023-09-10 16:24:29',
            'created_at' => '2023-09-10 16:24:29'
        ]);

        $parkAndRide2 = ParkAndRide::factory()->create([
            'name' => 'conseq',
            'lat' =>42.598212,
            'lng' =>177.831564,
            'id' => 2,
            'user_id' => 2,
            'attraction_name' => 'quis',
            'location_description' => 'Ut qui consequatur ut est id expedita.',
            'minutes_to_destination' => 50282398,
            'updated_at' => '2023-09-10 16:24:29',
            'created_at' => '2023-09-10 16:24:29'
        ]);

        $parkAndRide3 = ParkAndRide::factory()->create([
            'name' => 'consequatur',
            'lat' =>42.598212,
            'lng' =>177.831564,
            'id' => 3,
            'user_id' => 3,
            'attraction_name' => 'quis',
            'location_description' => 'Ut qui consequatur ut est id expedita.',
            'minutes_to_destination' => 50282398,
            'updated_at' => '2023-09-10 16:24:29',
            'created_at' => '2023-09-10 16:24:29'
        ]);

        // Create a mock for ParkAndRideSDK
        $parkAndRideSdkMock = $this->getMockBuilder(ParkAndRideSDK::class)
            ->setMethods(['getRankingResponse'])
            ->disableOriginalConstructor()
            ->getMock();

        // Configure the mock to throw a TimeoutException (simulating a timeout)
        $parkAndRideSdkMock->method('getRankingResponse')->willReturn(new RankingResponse(new RankingRequest([1,2,3]), null));

        /** @var ParkAndRideRankerGateway $gateway */
        $gateway = new ParkAndRideRankerGateway($parkAndRideSdkMock);
        $result = $gateway->rank([$parkAndRide3, $parkAndRide1, $parkAndRide2]);

        $this->assertEquals([$parkAndRide1, $parkAndRide2, $parkAndRide3], $result);
    }

    public function testSlowService()
    {
        $parkAndRide7 = ParkAndRide::factory()->create(['id' => 7]);
        $parkAndRide8 = ParkAndRide::factory()->create(['id' => 8]);
        $parkAndRide9 = ParkAndRide::factory()->create(['id' => 9]);

        // Create a mock for ParkAndRideSDK
        $parkAndRideSDKMock = $this->getMockBuilder(ParkAndRideSDK::class)
            ->setMethods(['getRankingResponse'])
            ->disableOriginalConstructor()
            ->getMock();

        // Configure the mock to throw a TimeoutException (simulating a timeout)
        $parkAndRideSDKMock->method('getRankingResponse')->willThrowException(new TimeoutException());

        $parkAndRideRankerGateway = new ParkAndRideRankerGateway($parkAndRideSDKMock);

        // Call rank
        $result = $parkAndRideRankerGateway->rank([$parkAndRide7, $parkAndRide8, $parkAndRide9]);

        // Assert that the result is empty (request failed due to timeout)
        $this->assertEmpty($result);
    }
}
