<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Search;

class SettingsFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Settings
    {
        return $this->objectManager->create(Settings::class);
    }
}
