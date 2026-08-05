<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Api\MerchantFulfillment\GetShipmentServices\Data;

class AvailableItem implements \Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices\Data\AvailableItemInterface
{
    private string $id;
    private string $name;
    private string $shipDate;
    private string $estimatedDeliveryDate;
    private float $rateAmount;
    private string $rateCurrency;

    public function __construct(
        string $id,
        string $name,
        string $shipDate,
        string $estimatedDeliveryDate,
        float $rateAmount,
        string $rateCurrency
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->shipDate = $shipDate;
        $this->estimatedDeliveryDate = $estimatedDeliveryDate;
        $this->rateAmount = $rateAmount;
        $this->rateCurrency = $rateCurrency;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getShipDate(): string
    {
        return $this->shipDate;
    }

    public function getEstimatedDeliveryDate(): string
    {
        return $this->estimatedDeliveryDate;
    }

    public function getRateAmount(): float
    {
        return $this->rateAmount;
    }

    public function getRateCurrency(): string
    {
        return $this->rateCurrency;
    }
}
