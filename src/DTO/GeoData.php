<?php

namespace App\DTO;

class GeoData
{
    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $country;

    /**
     * @param string $city
     * @param string $country
     */
    public function __construct(string  $city, string $country)
    {
        $this->city = $city;
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }
}
