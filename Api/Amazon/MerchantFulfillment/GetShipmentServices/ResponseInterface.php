<?php

declare(strict_types=1);

namespace Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices;

interface ResponseInterface
{
    /**
     * @return \Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices\Data\AvailableItemInterface[]
     */
    public function getAvailableItems(): array;

    /**
     * @return mixed
     */
    public function getUnavailable(): array;

    /**
     * @return mixed
     */
    public function getNotAcceptedItems(): array;
}
