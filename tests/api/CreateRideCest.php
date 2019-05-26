<?php

namespace Tests\api;
use ApiTester;
use Tests\AppBundle\Location\LocationRepositoryTest;

class CreateRideCest
{
    public function seeNewRideCreated(ApiTester $I)
    {
        $createdPassengerId = $I->getNewPassenger();
        $createdDriverId = $I->getNewDriver();

        $createdRide = $I->sendPostApiRequest(
            '/ride',
            [
                'passengerId' => $createdPassengerId,
                'departureLat' => LocationRepositoryTest::HOME_LOCATION_LAT,
                'departureLong' => LocationRepositoryTest::HOME_LOCATION_LONG
            ]
        );

        $I->seeResponseContainsJson(
            [
                'passenger_id' => $createdPassengerId,
                'departure_lat' => LocationRepositoryTest::HOME_LOCATION_LAT,
                'departure_long' => LocationRepositoryTest::HOME_LOCATION_LONG
            ]
        );
        $createdRideId = $createdRide['id'];

        $I->sendPatchApiRequest(
            '/ride/'.$createdRideId,
            [
                'driverId' => $createdDriverId
            ]
        );

        $I->seeResponseContainsJson(
            [
                'passenger_id' => $createdPassengerId,
                'departure_lat' => LocationRepositoryTest::HOME_LOCATION_LAT,
                'departure_long' => LocationRepositoryTest::HOME_LOCATION_LONG,
                'driver_id' => $createdDriverId
            ]
        );

        $I->sendPatchApiRequest(
            '/ride/'.$createdRideId,
            [
                'destinationLat' => LocationRepositoryTest::WORK_LOCATION_LAT,
                'destinationLong' => LocationRepositoryTest::WORK_LOCATION_LONG
            ]
        );

        $I->seeResponseContainsJson(
            [
                'id' => $createdRideId,
                'passenger_id' => $createdPassengerId,
                'departure_lat' => LocationRepositoryTest::HOME_LOCATION_LAT,
                'departure_long' => LocationRepositoryTest::HOME_LOCATION_LONG,
                'driver_id' => $createdDriverId,
                'destination_lat' => LocationRepositoryTest::WORK_LOCATION_LAT,
                'destination_long' => LocationRepositoryTest::WORK_LOCATION_LONG
            ]
        );
    }
}
