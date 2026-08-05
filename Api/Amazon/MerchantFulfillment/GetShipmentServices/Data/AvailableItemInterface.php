<?php

declare(strict_types=1);

namespace Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices\Data;

interface AvailableItemInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getShipDate(): string;

    /**
     * @return string
     */
    public function getEstimatedDeliveryDate(): string;

    /**
     * @return float
     */
    public function getRateAmount(): float;

    /**
     * @return string
     */
    public function getRateCurrency(): string;
}
