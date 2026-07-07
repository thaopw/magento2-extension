<?php

declare(strict_types=1);

namespace Ess\M2ePro\Setup\Update\y26_m06;

use Ess\M2ePro\Helper\Module\Database\Tables;

class AddEbayAutoActionAdvancedFilterMode extends \Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->addColumnsToListingTable();
        $this->addColumnsToEbayListingTable();
    }

    private function addColumnsToListingTable(): void
    {
        $modifier = $this->getTableModifier(Tables::TABLE_LISTING);

        $modifier->addColumn(
            \Ess\M2ePro\Model\ResourceModel\Listing::COLUMN_AUTO_ADVANCED_FILTER_ADDING_MODE,
            'SMALLINT UNSIGNED NOT NULL',
            0,
            null,
            false,
            false
        );

        $modifier->addColumn(
            \Ess\M2ePro\Model\ResourceModel\Listing::COLUMN_AUTO_ADVANCED_FILTER_ADDING_ADD_NOT_VISIBLE,
            'SMALLINT UNSIGNED NOT NULL',
            1,
            null,
            false,
            false
        );

        $modifier->addColumn(
            \Ess\M2ePro\Model\ResourceModel\Listing::COLUMN_AUTO_ADVANCED_FILTER_DELETING_MODE,
            'SMALLINT UNSIGNED NOT NULL',
            0,
            null,
            false,
            false
        );

        $modifier->addColumn(
            \Ess\M2ePro\Model\ResourceModel\Listing::COLUMN_AUTO_ADVANCED_FILTER_CONDITION,
            'LONGTEXT NULL',
            null,
            null,
            false,
            false
        );

        $modifier->commit();
    }

    private function addColumnsToEbayListingTable(): void
    {
        $modifier = $this->getTableModifier(Tables::TABLE_EBAY_LISTING);

        $modifier->addColumn(
            \Ess\M2ePro\Model\ResourceModel\Ebay\Listing::COLUMN_AUTO_ADVANCED_FILTER_ADDING_TEMPLATE_CATEGORY_ID,
            'INT UNSIGNED NULL',
            null,
            null,
            true,
            false
        );

        $modifier->addColumn(
            \Ess\M2ePro\Model\ResourceModel\Ebay\Listing::COLUMN_AUTO_ADVANCED_FILTER_ADDING_TEMPLATE_CATEGORY_SECONDARY_ID,
            'INT UNSIGNED NULL',
            null,
            null,
            true,
            false
        );

        $modifier->addColumn(
            \Ess\M2ePro\Model\ResourceModel\Ebay\Listing::COLUMN_AUTO_ADVANCED_FILTER_ADDING_TEMPLATE_STORE_CATEGORY_ID,
            'INT UNSIGNED NULL',
            null,
            null,
            true,
            false
        );

        $modifier->addColumn(
            \Ess\M2ePro\Model\ResourceModel\Ebay\Listing::COLUMN_AUTO_ADVANCED_FILTER_ADDING_TEMPLATE_STORE_CATEGORY_SECONDARY_ID,
            'INT UNSIGNED NULL',
            null,
            null,
            true,
            false
        );

        $modifier->commit();
    }
}
