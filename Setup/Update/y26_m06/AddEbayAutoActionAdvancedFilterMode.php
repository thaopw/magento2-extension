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
            'auto_advanced_filter_adding_mode',
            'SMALLINT UNSIGNED NOT NULL',
            0,
            null,
            false,
            false
        );

        $modifier->addColumn(
            'auto_advanced_filter_adding_add_not_visible',
            'SMALLINT UNSIGNED NOT NULL',
            1,
            null,
            false,
            false
        );

        $modifier->addColumn(
            'auto_advanced_filter_deleting_mode',
            'SMALLINT UNSIGNED NOT NULL',
            0,
            null,
            false,
            false
        );

        $modifier->addColumn(
            'auto_advanced_filter_condition',
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
            'auto_advanced_filter_adding_template_category_id',
            'INT UNSIGNED NULL',
            null,
            null,
            true,
            false
        );

        $modifier->addColumn(
            'auto_advanced_filter_adding_template_category_secondary_id',
            'INT UNSIGNED NULL',
            null,
            null,
            true,
            false
        );

        $modifier->addColumn(
            'auto_advanced_filter_adding_template_store_category_id',
            'INT UNSIGNED NULL',
            null,
            null,
            true,
            false
        );

        $modifier->addColumn(
            'auto_advanced_filter_adding_template_store_category_secondary_id',
            'INT UNSIGNED NULL',
            null,
            null,
            true,
            false
        );

        $modifier->commit();
    }
}
