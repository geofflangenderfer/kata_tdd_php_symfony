<?php


namespace AppBundle;

use AppBundle\Entity\AppLocation;
use AppBundle\Entity\AppRole;
use AppBundle\Entity\AppUser;
use AppBundle\Entity\Ride;
use AppBundle\Entity\RideEvent;
use AppBundle\Entity\RideEventType;

class AppService
{
    /**
     * @var AppDao
     */
    private $dao;

    /**
     * @param AppDao $dao
     */
    public function __construct(AppDao $dao)
    {
        $this->dao = $dao;
    }

    public function newUser($firstName, $lastName)
    {
        $this->dao->newUser($firstName, $lastName);
    }

    /**
     * @param $userId
     * @return AppUser
     */
    public function getUserById($userId)
    {
        return $this->dao->getUserById($userId);
    }

    public function assignRoleToUser(AppUser $user, AppRole $role)
    {
        if (!$this->dao->isUserInRole($user, $role)) {
            $this->dao->assignRoleToUser($user, $role);
        } else {
            throw new RoleLifeCycleException(
                'User: '
                .$user->getFullName()
                .' is already of Role: '
                .$role->getName()
            );
        }
    }

    /**
     * @param AppUser $user
     * @return bool
     */
    public function isUserPassenger(AppUser $user)
    {
        return $this->dao->isUserInRole($user, AppRole::asPassenger());
    }

    public function isUserDriver(AppUser $user)
    {
        return $this->dao->isUserInRole($user, AppRole::asDriver());
    }

    /**
     * @param float $lat
     * @param float $long
     * @return AppLocation
     */
    public function getLocation($lat, $long)
    {
        return $this->dao->getOrCreateLocation(
            $lat,
            $long
        );
    }

    public function createRide(AppUser $passenger, AppLocation $departure)
    {
        $this->dao->createRide($passenger, $departure);
    }

    /**
     * @param AppUser $passenger
     * @return Ride[]
     */
    public function getRidesForPassenger(AppUser $passenger)
    {
        return $this->dao->getRidesForPassenger($passenger);
    }

    public function passengerMarkRideAs(Ride $ride, RideEventType $type)
    {
        $actor = $ride->getPassenger();
        $this->markRideAsForActor($ride, $type, $actor);
    }

    public function driverMarkRideAs(Ride $ride, RideEventType $type)
    {
        $actor = $ride->getDriver();
        $this->markRideAsForActor($ride, $type, $actor);
    }

    /**
     * @param Ride $ride
     * @return RideEvent
     */
    public function getRideStatus(Ride $ride)
    {
        return $this->dao->getLastEventForRide($ride);
    }

    /**
     * @param Ride $ride
     * @param RideEventType $eventType
     * @return bool
     */
    public function isRide(Ride $ride, RideEventType $eventType)
    {
        return $this->dao->isRideStatus($ride, $eventType);
    }

    /**
     * @param Ride $ride
     * @param AppUser $driver
     */
    public function assignDriverToRide(Ride $ride, AppUser $driver)
    {
        $this->dao->assignDriverToRide($ride, $driver);
    }

    /**
     * @param Ride $ride
     * @param RideEventType $type
     * @param $actor
     * @throws RideEventLifeCycleException
     */
    private function markRideAsForActor(Ride $ride, RideEventType $type, $actor)
    {
        $this->validateRequestedLifecycle($ride, $type);
        $event = new RideEvent(
            $this->dao->getEventType($type),
            $ride,
            $actor
        );
        $this->dao->saveRideEvent($event);
    }

    /**
     * @param Ride $ride
     * @param RideEventType $type
     * @throws RideEventLifeCycleException
     */
    private function validateRequestedLifecycle(Ride $ride, RideEventType $type)
    {
        if (
            $type->equals(RideEventType::asRequested())
            &&
            $this->isRide($ride, RideEventType::asRequested())
        ) {
            throw new RideEventLifeCycleException('Ride is already requested.');
        }
    }
}
