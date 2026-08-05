<?php

declare(strict_types=1);

namespace Ess\M2ePro\Api\Amazon;

interface MerchantFulfillmentInterface
{
    /**
     * @param string $amazonOrderId
     * @param \Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices\RequestInterface $request
     *
     * @return \Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices\ResponseInterface
     */
    public function getShipmentServices(
        string $amazonOrderId,
        \Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices\RequestInterface $request
    ): \Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices\ResponseInterface;

    /**
     * @param string $amazonOrderId
     * @param \Ess\M2ePro\Api\Amazon\MerchantFulfillment\CreateShipmentOffer\RequestInterface $request
     *
     * @return \Ess\M2ePro\Api\Amazon\MerchantFulfillment\CreateShipmentOffer\ResponseInterface
     */
    public function createShipmentOffer(
        string $amazonOrderId,
        \Ess\M2ePro\Api\Amazon\MerchantFulfillment\CreateShipmentOffer\RequestInterface $request
    ): \Ess\M2ePro\Api\Amazon\MerchantFulfillment\CreateShipmentOffer\ResponseInterface;

    /**
     * @param string $amazonOrderId
     *
     * @return bool
     */
    public function cancelShipmentOffer(string $amazonOrderId): bool;
}
