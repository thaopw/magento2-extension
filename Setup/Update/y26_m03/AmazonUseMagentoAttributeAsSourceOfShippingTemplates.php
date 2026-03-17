<?php

declare(strict_types=1);

namespace Ess\M2ePro\Setup\Update\y26_m03;

use Ess\M2ePro\Helper\Module\Database\Tables;

class AmazonUseMagentoAttributeAsSourceOfShippingTemplates extends \Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->getTableModifier(Tables::TABLE_AMAZON_TEMPLATE_SHIPPING);

        $modifier->addColumn(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping::COLUMN_MODE,
            'SMALLINT UNSIGNED NOT NULL',
            \Ess\M2ePro\Model\Amazon\Template\Shipping::MODE_AMAZON_TEMPLATE,
            \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping::COLUMN_MARKETPLACE_ID,
            false,
            false
        );

        $modifier->addColumn(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping::COLUMN_CUSTOM_ATTRIBUTE,
            'VARCHAR(255) NOT NULL',
            null,
            \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping::COLUMN_TEMPLATE_ID,
            false,
            false
        );

        $modifier->commit();
    }
}
