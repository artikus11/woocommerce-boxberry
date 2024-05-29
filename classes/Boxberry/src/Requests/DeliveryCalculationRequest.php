<?php

namespace Boxberry\Requests;

class DeliveryCalculationRequest extends Request
{
    protected $resultClass = 'Boxberry\\Models\\DeliveryCalculation';
    protected $propNameMap = [
        'SenderCityId' => 'senderCityId',
        'RecipientCityId'  => 'recipientCityId',
        'DeliveryType' => 'deliveryType',
        'OrderSum' => 'orderSum',
        'PaySum' => 'paySum',
        'BoxSizes' => 'boxSizes',
        'UseShopSettings' => 'useShopSettings',
        'CmsName' => 'cmsName',
        'Url' => 'url',
        'Version' => 'version'
    ];

    public $method = 'POST';

    protected $senderCityId = '';
    protected $recipientCityId = '';
    protected $deliveryType = '';
    protected $orderSum = 0;
    protected $paySum = 0;
    protected $boxSizes = [];
    protected $width = 0;
    protected $height = 0;
    protected $depth = 0;
    protected $weight = 0;
    protected $useShopSettings = '';
    protected $cmsName = '';
    protected $url = '';
    protected $version = '';

    /**
     * @return string
     */
    public function getSenderCityId()
    {
        return $this->senderCityId;
    }

    /**
     * @param string $senderCityId
     */
    public function setSenderCityId($senderCityId)
    {
        $this->senderCityId = $senderCityId;
    }

    /**
     * @return string
     */
    public function getRecipientCityId()
    {
        return $this->recipientCityId;
    }

    /**
     * @param string $recipientCityId
     */
    public function setRecipientCityId($recipientCityId)
    {
        $this->recipientCityId = $recipientCityId;
    }

    /**
     * @return string
     */
    public function getDeliveryType()
    {
        return $this->deliveryType;
    }

    /**
     * @param string $deliveryType
     */
    public function setDeliveryType($deliveryType)
    {
        $this->deliveryType = $deliveryType;
    }

    /**
     * @return int
     */
    public function getOrderSum()
    {
        return $this->orderSum;
    }

    /**
     * @param int $orderSum
     */
    public function setOrderSum($orderSum)
    {
        $this->orderSum = $orderSum;
    }

    /**
     * @return int
     */
    public function getPaySum()
    {
        return $this->paySum;
    }

    /**
     * @param int $paySum
     */
    public function setPaySum($paySum)
    {
        $this->paySum = $paySum;
    }

    /**
     * @return array
     */
    public function getBoxSizes()
    {
        return $this->boxSizes;
    }

    /**
     */
    public function setBoxSizes()
    {
        $this->boxSizes = [
            [
                'Width' => $this->width,
                'Height' => $this->height,
                'Depth' => $this->depth,
                'Weight' => $this->weight
            ]

        ];
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return string
     */
    public function getUseShopSettings()
    {
        return $this->useShopSettings;
    }

    /**
     * @param string $useShopSettings
     */
    public function setUseShopSettings($useShopSettings)
    {
        $this->useShopSettings = $useShopSettings;
    }

    /**
     * @return string
     */
    public function getCmsName()
    {
        return $this->cmsName;
    }

    /**
     * @param string $cmsName
     */
    public function setCmsName($cmsName)
    {
        $this->cmsName = $cmsName;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @inheritDoc
     */
    function checkRequiredFields()
    {
        if (!$this->recipientCityId){
            return false;
        }

        if (!$this->boxSizes){
            return false;
        }

        if (!$this->weight){
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }
}