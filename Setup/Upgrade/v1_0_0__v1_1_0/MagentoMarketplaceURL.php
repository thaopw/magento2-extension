<?php

namespace Ess\M2ePro\Setup\Upgrade\v1_0_0__v1_1_0;

use Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractFeature;

class MagentoMarketplaceURL extends AbstractFeature
{
    //########################################

    public function execute()
    {
        $marketplaceUrl = 'https://marketplace.magento.com/'
           . 'm2epro-ebay-amazon-rakuten-sears-magento-integration-order-import-and-stock-level-synchronization.html';

        $this->getConfigModifier('module')->getEntity('/support/', 'magento_connect_url')
                                          ->updateKey('magento_marketplace_url');

        $this->getConfigModifier('module')->getEntity('/support/', 'magento_marketplace_url')
                                          ->updateValue($marketplaceUrl);
    }

    //########################################
}
