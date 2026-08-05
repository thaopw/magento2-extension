<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Api;

class MerchantFulfillment implements \Ess\M2ePro\Api\Amazon\MerchantFulfillmentInterface
{
    private MerchantFulfillment\GetShipmentServicesService $getShipmentServicesService;
    private MerchantFulfillment\CreateShipmentOfferService $createShipmentOfferService;
    private MerchantFulfillment\CancelShipmentOfferService $cancelShipmentOfferService;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Api\MerchantFulfillment\GetShipmentServicesService $getShipmentServicesService,
        \Ess\M2ePro\Model\Amazon\Api\MerchantFulfillment\CreateShipmentOfferService $createShipmentOfferService,
        \Ess\M2ePro\Model\Amazon\Api\MerchantFulfillment\CancelShipmentOfferService $cancelShipmentOfferService
    ) {
        $this->createShipmentOfferService = $createShipmentOfferService;
        $this->getShipmentServicesService = $getShipmentServicesService;
        $this->cancelShipmentOfferService = $cancelShipmentOfferService;
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getShipmentServices(
        string $amazonOrderId,
        \Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices\RequestInterface $request
    ): \Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices\ResponseInterface {
        return $this->getShipmentServicesService->execute($amazonOrderId, $request);
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function createShipmentOffer(
        string $amazonOrderId,
        \Ess\M2ePro\Api\Amazon\MerchantFulfillment\CreateShipmentOffer\RequestInterface $request
    ): \Ess\M2ePro\Api\Amazon\MerchantFulfillment\CreateShipmentOffer\ResponseInterface {
        return $this->createShipmentOfferService->execute($amazonOrderId, $request);
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function cancelShipmentOffer(string $amazonOrderId): bool
    {
        return $this->cancelShipmentOfferService->execute($amazonOrderId);
    }
}
