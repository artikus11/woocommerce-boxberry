<?php

namespace Boxberry\Models;

class DeliveryCalculation extends AbstractModel
{
    const PICKUP_DELIVERY_TYPE_ID = 1;
    const COURIER_DELIVERY_TYPE_ID = 2;

    protected $totalPricePickup = 0;
    protected $totalPriceCourier = 0;
    protected $priceBasePickup = 0;
    protected $priceBaseCourier = 0;
    protected $deliveryPeriodPickup = 0;
    protected $deliveryPeriodCourier = 0;

    /**
     * DeliveryCalculation constructor.
     * @param array $data
     * @throws \Exception
     */
    public function __construct($data)
    {

        if (empty($data) || isset($data['err']) || (isset($data['error']) && $data['error'] === true) ||
            (!isset($data['result']['DeliveryCosts']) && !is_array($data['result']['DeliveryCosts']))) {
            throw new \Exception('Доставка до выбранного места невозможна');
        }

        foreach ($data['result']['DeliveryCosts'] as $deliveryCost) {
            if ($deliveryCost['DeliveryTypeId'] === self::PICKUP_DELIVERY_TYPE_ID) {
                $this->totalPricePickup = $deliveryCost['TotalPrice'];
                $this->priceBasePickup = $deliveryCost['PriceBase'];
                $this->deliveryPeriodPickup = $deliveryCost['DeliveryPeriod'];
            }

            if ($deliveryCost['DeliveryTypeId'] === self::COURIER_DELIVERY_TYPE_ID) {
                $this->totalPriceCourier = $deliveryCost['TotalPrice'];
                $this->priceBaseCourier = $deliveryCost['PriceBase'];
                $this->deliveryPeriodCourier = $deliveryCost['DeliveryPeriod'];
            }
        }

        parent::__construct();
    }

    /**
     * @return int
     */
    public function getTotalPricePickup()
    {
        return $this->totalPricePickup;
    }

    /**
     * @param int $totalPricePickup
     */
    public function setTotalPricePickup($totalPricePickup)
    {
        $this->totalPricePickup = $totalPricePickup;
    }

    /**
     * @return int
     */
    public function getTotalPriceCourier()
    {
        return $this->totalPriceCourier;
    }

    /**
     * @param int $totalPriceCourier
     */
    public function setTotalPriceCourier($totalPriceCourier)
    {
        $this->totalPriceCourier = $totalPriceCourier;
    }

    /**
     * @return int
     */
    public function getPriceBasePickup()
    {
        return $this->priceBasePickup;
    }

    /**
     * @param int $priceBasePickup
     */
    public function setPriceBasePickup($priceBasePickup)
    {
        $this->priceBasePickup = $priceBasePickup;
    }

    /**
     * @return int
     */
    public function getPriceBaseCourier()
    {
        return $this->priceBaseCourier;
    }

    /**
     * @param int $priceBaseCourier
     */
    public function setPriceBaseCourier($priceBaseCourier)
    {
        $this->priceBaseCourier = $priceBaseCourier;
    }

    /**
     * @return int
     */
    public function getDeliveryPeriodPickup()
    {
        return $this->deliveryPeriodPickup;
    }

    /**
     * @param int $deliveryPeriodPickup
     */
    public function setDeliveryPeriodPickup($deliveryPeriodPickup)
    {
        $this->deliveryPeriodPickup = $deliveryPeriodPickup;
    }

    /**
     * @return int
     */
    public function getDeliveryPeriodCourier()
    {
        return $this->deliveryPeriodCourier;
    }

    /**
     * @param int $deliveryPeriodCourier
     */
    public function setDeliveryPeriodCourier($deliveryPeriodCourier)
    {
        $this->deliveryPeriodCourier = $deliveryPeriodCourier;
    }

}