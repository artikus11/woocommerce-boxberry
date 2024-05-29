<?php

namespace Boxberry\Models;

class CourierDeliveryExport extends AbstractModel
{
    const EAEU_COURIER_DEFAULT_INDEX = '151';
    const TRANSPORTER_GUID = 'fd85a8b6-4688-404f-9993-30b9e55d2950';

    protected $propNameMap = array(
        'index' => 'index',
        'countryCode' => 'countryCode',
        'cityCode' => 'cityCode',
        'area' => 'area',
        'street' => 'street',
        'house' => 'house',
        'flat ' => 'flat',
        'transporterGuid' => 'transporterGuid',
    );

    protected $index = '';
    protected $countryCode = '';
    protected $cityCode = '';
    protected $area = '';
    protected $street = '';
    protected $house = '';
    protected $flat = '';
    protected $transporterGuid = '';

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param string $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
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
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param string $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * @param string $house
     */
    public function setHouse($house)
    {
        $this->house = $house;
    }

    /**
     * @return string
     */
    public function getFlat()
    {
        return $this->flat;
    }

    /**
     * @param string $flat
     */
    public function setFlat($flat)
    {
        $this->flat = $flat;
    }

    /**
     * @return string
     */
    public function getTransporterGuid()
    {
        return $this->transporterGuid;
    }

    /**
     * @param string $transporterGuid
     */
    public function setTransporterGuid($transporterGuid)
    {
        $this->transporterGuid = $transporterGuid;
    }


}