<?php

declare(strict_types=1);

namespace Ess\M2ePro\Setup\Update\y26_m07;

use Ess\M2ePro\Helper\Module\Database\Tables;

class AddAmazonAutoSearchAsinCron extends \Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->getTableModifier(Tables::TABLE_AMAZON_LISTING_PRODUCT);

        $modifier->addColumn(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product::COLUMN_AUTO_SEARCH_ASIN_LAST_ATTEMPT_DATE,
            'DATETIME',
            null,
            null,
            false,
            false
        );

        $modifier->addColumn(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product::COLUMN_AUTO_SEARCH_ASIN_ATTEMPT,
            'SMALLINT UNSIGNED NOT NULL',
            0,
            null,
            false,
            false
        );

        $modifier->commit();
    }
}
