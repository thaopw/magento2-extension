<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Api\MerchantFulfillment;

class CancelShipmentOfferService
{
    private OrderLoader $orderLoader;
    private OrderValidator $orderValidator;
    private \Ess\M2ePro\Model\Amazon\Connector\Shipment\Cancel\Entity\Processor $cancelOfferProcessor;

    public function __construct(
        OrderLoader $orderLoader,
        OrderValidator $orderValidator,
        \Ess\M2ePro\Model\Amazon\Connector\Shipment\Cancel\Entity\Processor $cancelOfferProcessor
    ) {
        $this->orderLoader = $orderLoader;
        $this->orderValidator = $orderValidator;
        $this->cancelOfferProcessor = $cancelOfferProcessor;
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function execute(string $amazonOrderId): bool
    {
        $order = $this->orderLoader->load($amazonOrderId);
        $this->orderValidator->validate($order);
        $shipmentId = $this->getShipmentIdFromOrder($order);

        $this->cancelOfferProcessor->process($order->getAccount(), $shipmentId);
        $this->resetMerchantFulfillmentOrderData($order);

        return true;
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    private function getShipmentIdFromOrder(\Ess\M2ePro\Model\Order $order): string
    {
        /** @var \Ess\M2ePro\Model\Amazon\Order $amazonOrder */
        $amazonOrder = $order->getChildObject();
        $orderFulfillmentData = $amazonOrder->getMerchantFulfillmentData();

        if (empty($orderFulfillmentData)) {
            throw new \Ess\M2ePro\Model\Exception\Logic('You should create shipment first');
        }

        $statusRefundPurchased = \Ess\M2ePro\Helper\Component\Amazon\MerchantFulfillment::STATUS_PURCHASED;
        if ($orderFulfillmentData['status'] != $statusRefundPurchased) {
            throw new \Ess\M2ePro\Model\Exception\Logic('Shipment status should be Purchased');
        }

        return (string)$orderFulfillmentData['shipment_id'];
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    private function resetMerchantFulfillmentOrderData(\Ess\M2ePro\Model\Order $order): void
    {
        $dataToSave = [
            'merchant_fulfillment_data' => null,
            'merchant_fulfillment_label' => null,
        ];

        /** @var \Ess\M2ePro\Model\Amazon\Order $amazonOrder */
        $amazonOrder = $order->getChildObject();
        $amazonOrder->addData($dataToSave)->save();
    }
}
