<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Listing\Auto;

class CategoryFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Category
    {
        return $this->objectManager->create(Category::class);
    }
}
