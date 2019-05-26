<?php

namespace AppBundle\Service;

use AppBundle\Entity\AppLocation;
use AppBundle\Repository\LocationRepository;
use Doctrine\ORM\NonUniqueResultException;

class LocationService
{
    /**
     * @var LocationRepository
     */
    private $locationRepository;

    /**
     * LocationService constructor.
     * @param LocationRepository $locationRepository
     */
    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    /**
     * @param float $lat
     * @param float $long
     * @return AppLocation
     * @throws NonUniqueResultException
     */
    public function getLocation(float $lat, float $long) : AppLocation
    {
        return $this->locationRepository->getOrCreateLocation($lat, $long);
    }
}
