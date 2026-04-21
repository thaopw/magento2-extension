<?php

declare(strict_types=1);

namespace Ess\M2ePro\Setup\Update\y26_m04;

use Ess\M2ePro\Helper\Module\Database\Tables;

class FixAmazonTemplateShippingTableModeDefaultValue extends \Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->getTableModifier(Tables::TABLE_AMAZON_TEMPLATE_SHIPPING);

        $modifier->changeColumn(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping::COLUMN_MODE,
            'SMALLINT UNSIGNED NOT NULL',
            \Ess\M2ePro\Model\Amazon\Template\Shipping::MODE_AMAZON_TEMPLATE,
            \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping::COLUMN_MARKETPLACE_ID,
            false,
        );

        $modifier->commit();
    }
}
