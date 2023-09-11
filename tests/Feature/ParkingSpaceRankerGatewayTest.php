<?php

namespace Tests\Feature;

use App\Gateways\ParkingSpaceRankerGateway;
use App\Models\ParkingSpace;
use App\ThirdParty\ParkingSpaceHttpService;
use App\ThirdParty\TimeoutException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkingSpaceRankerGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function testParkingSpaceRanker()
    {
        // TODO Part 2)
        $parkingSpace1 = ParkingSpace::factory()->create([
            'name' => 'consequatur',
            'lat' =>42.598212,
            'lng' =>177.831564,
            'user_id' => 1,
            'space_details' => 'Atque inventore omnis et animi adipisci ea quis.',
            'city' => 'vitae',
            'street_name' => 'qui',
            'no_of_spaces' =>60002884,
            'id' => 1,
            'updated_at' => '2023-09-10T14:47:30.000000Z',
            'created_at' => '2023-09-10T14:47:30.000000Z'
        ]);

        $parkingSpace2 = ParkingSpace::factory()->create([
            'name' => 'consequatur',
            'lat' =>42.598212,
            'lng' =>177.831564,
            'user_id' => 2,
            'space_details' => 'Atque inventore omnis et animi adipisci ea quis.',
            'city' => 'vitae',
            'street_name' => 'qui',
            'no_of_spaces' =>60002884,
            'id' => 2,
            'updated_at' => '2023-09-10T14:47:30.000000Z',
            'created_at' => '2023-09-10T14:47:30.000000Z'
        ]);

        $parkingSpace3 = ParkingSpace::factory()->create([
            'name' => 'consequatur',
            'lat' =>42.598212,
            'lng' =>177.831564,
            'user_id' => 3,
            'space_details' => 'Atque inventore omnis et animi adipisci ea quis.',
            'city' => 'vitae',
            'street_name' => 'qui',
            'no_of_spaces' =>60002884,
            'id' => 3,
            'updated_at' => '2023-09-10T14:47:30.000000Z',
            'created_at' => '2023-09-10T14:47:30.000000Z'
        ]);

        /** @var ParkingSpaceRankerGateway $gateway */
        $gateway = app(ParkingSpaceRankerGateway::class);

        $result = $gateway->rank([$parkingSpace3, $parkingSpace1, $parkingSpace2]);
        $this->assertEquals([$parkingSpace1['id'] => $parkingSpace1->toArray(), $parkingSpace2['id'] => $parkingSpace2->toArray(), $parkingSpace3['id'] => $parkingSpace3->toArray()], $result);
    }

    public function testSlowService()
    {
        $parkingSpace7 = ParkingSpace::factory()->create(['id' => 7]);
        $parkingSpace8 = ParkingSpace::factory()->create(['id' => 8]);
        $parkingSpace9 = ParkingSpace::factory()->create(['id' => 9]);

        // Create a mock for ParkingSpaceHttpService
        $parkingSpaceHttpServiceMock = $this->getMockBuilder(ParkingSpaceHttpService::class)
            ->setMethods(['getRanking'])
            ->disableOriginalConstructor()
            ->getMock();

        // Configure the mock to throw a TimeoutException (simulating a timeout)
        $parkingSpaceHttpServiceMock->method('getRanking')->willThrowException(new TimeoutException());

        $parkingSpaceRankerGateway = new ParkingSpaceRankerGateway($parkingSpaceHttpServiceMock);

        // Call rank
        $result = $parkingSpaceRankerGateway->rank([$parkingSpace7, $parkingSpace8, $parkingSpace9]);

        // Assert that the result is empty (request failed due to timeout)
        $this->assertEmpty($result);
    }
}
