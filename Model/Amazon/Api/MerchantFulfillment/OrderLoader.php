<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Api\MerchantFulfillment;

class OrderLoader
{
    private \Ess\M2ePro\Model\Amazon\Order\Repository $amazonOrderRepository;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Order\Repository $amazonOrderRepository
    ) {
        $this->amazonOrderRepository = $amazonOrderRepository;
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function load(string $amazonOrderId): \Ess\M2ePro\Model\Order
    {
        $order = $this->amazonOrderRepository->findByAmazonOrderId($amazonOrderId);
        if ($order === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic(
                sprintf('Amazon Order with ID "%s" not found.', $amazonOrderId)
            );
        }

        return $order;
    }
}
