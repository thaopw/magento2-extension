<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Walmart\Magento\Product;

class UnmanagedRuleFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(string $prefix, ?int $storeId = null): UnmanagedRule
    {
        return $this->objectManager->create(UnmanagedRule::class)->setData(
            [
                'prefix' => $prefix,
                'store_id' => $storeId,
            ]
        );
    }
}
