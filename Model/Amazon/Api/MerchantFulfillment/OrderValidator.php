<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Api\MerchantFulfillment;

class OrderValidator
{
    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function validate(\Ess\M2ePro\Model\Order $order): void
    {
        $this->validateMarketplace($order);
        $this->validateFulfilment($order);
        $this->validateStatus($order);
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    private function validateFulfilment(\Ess\M2ePro\Model\Order $order): void
    {
        /** @var \Ess\M2ePro\Model\Amazon\Order $amazonOrder */
        $amazonOrder = $order->getChildObject();

        if ($amazonOrder->isFulfilledByAmazon()) {
            throw new \Ess\M2ePro\Model\Exception\Logic(
                'Amazon\'s Shipping Services can not be applied to FBA Orders. ' .
                'The delivery of purchased FBA Items is managed by Amazon Fulfillment Center'
            );
        }
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    private function validateMarketplace(\Ess\M2ePro\Model\Order $order): void
    {
        /** @var \Ess\M2ePro\Model\Amazon\Marketplace $amazonMarketplace */
        $amazonMarketplace = $order->getMarketplace()->getChildObject();

        if (!$amazonMarketplace->isMerchantFulfillmentAvailable()) {
            throw new \Ess\M2ePro\Model\Exception\Logic(
                'This Order was Created on Amazon Marketplace where Amazon\'s Shipping Services ' .
                'are not available. Currently, you can use this Tool for Orders purchased on Amazon UK, US and DE'
            );
        }
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    private function validateStatus(\Ess\M2ePro\Model\Order $order): void
    {
        /** @var \Ess\M2ePro\Model\Amazon\Order $amazonOrder */
        $amazonOrder = $order->getChildObject();

        if (
            $amazonOrder->isCanceled()
            || $amazonOrder->isPending()
            || $amazonOrder->isShipped()
        ) {
            throw new \Ess\M2ePro\Model\Exception\Logic(
                'Amazon\'s Shipping Service can not be used for the Orders with Status Pending, ' .
                'Shipped and Canceled. It can be applied only to Amazon Orders with Unshipped Status'
            );
        }
    }
}
