<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Api\MerchantFulfillment;

class GetShipmentServicesService
{
    private OrderLoader $orderLoader;
    private OrderValidator $orderValidator;
    private \Ess\M2ePro\Model\Amazon\Connector\Shipment\Get\Offers\Processor $getOffersProcessor;

    public function __construct(
        OrderLoader $orderLoader,
        OrderValidator $orderValidator,
        \Ess\M2ePro\Model\Amazon\Connector\Shipment\Get\Offers\Processor $getOffersProcessor
    ) {
        $this->orderLoader = $orderLoader;
        $this->orderValidator = $orderValidator;
        $this->getOffersProcessor = $getOffersProcessor;
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function execute(
        string $amazonOrderId,
        \Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices\RequestInterface $request
    ): \Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices\ResponseInterface {
        $order = $this->orderLoader->load($amazonOrderId);

        $this->orderValidator->validate($order);

        $requestData = $this->createRequestDataForGetOffers($order, $request);
        $response = $this->getOffersProcessor->process($order->getAccount(), $requestData);

        return new \Ess\M2ePro\Model\Amazon\Api\MerchantFulfillment\GetShipmentServices\Response($response);
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    private function createRequestDataForGetOffers(
        \Ess\M2ePro\Model\Order $order,
        \Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices\RequestInterface $request
    ): array {
        /** @var \Ess\M2ePro\Model\Amazon\Order $amazonOrder */
        $amazonOrder = $order->getChildObject();

        $requestData = [
            'order_id' => $order->getChildObject()->getAmazonOrderId(),
            'order_items' => array_map(function (\Ess\M2ePro\Model\Order\Item $item) {
                /** @var \Ess\M2ePro\Model\Amazon\Order\Item $amazonOrderItem */
                $amazonOrderItem = $item->getChildObject();

                return [
                    'id' => $amazonOrderItem->getAmazonOrderItemId(),
                    'qty' => $amazonOrderItem->getQtyPurchased(),
                ];
            }, $order->getItems()),
            'package' => [
                'dimensions' => [
                    'length' => $request->getPackageDimensionLength(),
                    'width' => $request->getPackageDimensionWidth(),
                    'height' => $request->getPackageDimensionHeight(),
                    'unit_of_measure' => $request->getPackageDimensionMeasure(),
                ],
                'weight' => [
                    'value' => $request->getPackageWeight(),
                    'unit_of_measure' => $request->getPackageWeightMeasure(),
                ],
            ],
            'shipment_location' => [
                'info' => [
                    'name' => $request->getShipFromAddressName(),
                    'email' => $request->getShipFromAddressEmail(),
                    'phone' => $request->getShipFromAddressPhone(),
                ],
                'physical' => [
                    'country' => $request->getShipFromAddressCountry(),
                    'city' => $request->getShipFromAddressCity(),
                    'address_1' => $request->getShipFromAddressAddressLine1(),
                    'postal_code' => $request->getShipFromAddressPostalCode(),
                ],
            ],
            'delivery_confirmation_level' => $request->getDeliveryConfirmationLevel(),
            'carrier_pickup' => $request->getCarrierWillPickup(),
            'arrive_by_date' => $request->getArriveByDate(),
            'declared_value' => [
                'amount' => $amazonOrder->getSubtotalPrice(),
                'currency_code' => $amazonOrder->getCurrency(),
            ],
        ];

        if (!empty($request->getShipFromAddressRegionState())) {
            $requestData['shipment_location']['physical']['region_state'] = $request->getShipFromAddressRegionState();
        }

        if (!empty($request->getShipFromAddressAddressLine2())) {
            $requestData['shipment_location']['physical']['address_2'] = $request->getShipFromAddressAddressLine2();
        }

        return $requestData;
    }
}
