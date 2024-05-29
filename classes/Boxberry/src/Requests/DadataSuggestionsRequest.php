<?php

namespace boxberry\Requests;

class DadataSuggestionsRequest extends Request
{
    protected $resultClass = 'Boxberry\\Models\\DadataSuggestions';
    protected $propNameMap = [
        'query' => 'query',
        'locations'  => 'locations',
    ];

    public $method = 'POST';

    protected $query = '';
    protected $locations = [];


    /**
     * @inheritDoc
     */
    function checkRequiredFields()
    {
        if (empty(trim($this->query))){
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }

    /**
     * @return array
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @return void
     */
    public function setLocations()
    {
        $this->locations = [
            'country' => '*',
        ];
    }

    /**
     * @return void
     */
    public function fixCityName()
    {
        $this->query = str_replace('АЛМА-АТА', 'АЛМАТЫ', mb_strtoupper($this->query));
    }

}