<?php

namespace boxberry\Models;

class DadataSuggestions extends AbstractModel
{
    protected $area = '';
    protected $street = '';
    protected $house = '';
    protected $flat = '';

    /**
     * @throws \Exception
     */
    public function __construct($data)
    {
        if (!isset($data['suggestions'][0]['data'])){
            throw new \Exception('Данные не найдены');
        }

        $data = $data['suggestions'][0]['data'];

        $this->area = $data['region'];
        $this->street = $data['street'];
        $this->house = $data['house'];
        $this->flat = isset($data['flat']) ? $data['flat'] : '';

        parent::__construct($data);
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


}