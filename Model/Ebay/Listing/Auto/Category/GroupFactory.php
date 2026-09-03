<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Ebay\Listing\Auto\Category;

class GroupFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Group
    {
        return $this->objectManager->create(Group::class);
    }
}
