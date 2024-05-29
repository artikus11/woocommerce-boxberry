<?php

namespace Boxberry\Client;

use Boxberry\Collections\ListCitiesCollection;

class LocationFinder
{
    /**
     * @var ListCitiesCollection
     */
    protected $cities;

    /**
     * @var string
     */
    protected $cityCode = '';

    /**
     * @var string
     */
    protected $countryCode = '';

    /**
     * @var string
     */
    protected $userCityName = '';
    /**
     * @var string
     */
    protected $userRegionName = '';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $error = '';

    /**
     * @return ListCitiesCollection
     */
    public function getCities()
    {
        return $this->cities;
    }

    /**
     * @param ListCitiesCollection $cities
     */
    public function setCities(ListCitiesCollection $cities)
    {
        $this->cities = $cities;
    }

    /**
     * @return string
     */
    public function getUserCityName()
    {
        return $this->userCityName;
    }

    /**
     * @param string $userCityName
     */
    public function setUserCityName($userCityName)
    {
        $this->userCityName = $userCityName;
    }

    /**
     * @return string
     */
    public function getUserRegionName()
    {
        return $this->userRegionName;
    }

    /**
     * @param string $userRegionName
     */
    public function setUserRegionName($userRegionName)
    {
        $this->userRegionName = $userRegionName;
    }

    private function compareLocations($a, $b)
    {
        return str_replace(['Ё', 'Г ', 'АЛМАТЫ'], ['Е', '', 'АЛМА-АТА'], mb_strtoupper($a)) === str_replace(['Ё', 'Г ', 'АЛМАТЫ'], ['Е', '', 'АЛМА-АТА'], mb_strtoupper($b));
    }

    /**
     * @return void
     */
    public function findCodeByFullData()
    {
        foreach ($this->cities as $city) {
            if ($this->compareLocations($city->getName(), trim($this->userCityName)) && @mb_stripos(trim($this->userRegionName), $city->getRegion()) !== false) {
                $this->setCityData($city);
                break;
            }
        }
    }

    /**
     * @return void
     */
    public function findCodeByCityName()
    {
        foreach ($this->cities as $city) {
            if ($this->compareLocations($city->getName(), trim($this->userCityName))) {
                $this->setCityData($city);
                break;
            }
        }

    }

    private function setCityData($city)
    {
        $this->cityCode = $city->getCode();
        $this->countryCode = $city->getCountryCode();
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return string
     */
    public function getCityCode()
    {
        return $this->cityCode;
    }

    /**
     * @param string $cityCode
     */
    public function setCityCode($cityCode)
    {
        $this->cityCode = $cityCode;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    public function find($city, $region = '')
    {
        try {
            $listCitiesFull = $this->client->getListCitiesFull();
            $this->cities = $this->client->execute($listCitiesFull);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return;
        }

        if (!empty(trim($region))) {
            $this->userCityName = $city;
            $this->userRegionName = $region;
            $this->findCodeByFullData();
        }

        if (!$this->cityCode) {
            $this->userCityName = $city;
            $this->findCodeByCityName();
        }

        if (!$this->cityCode){
            $this->error = 'Код города не найден';
        }
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }


}