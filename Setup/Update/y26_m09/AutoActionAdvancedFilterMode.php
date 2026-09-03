<?php

declare(strict_types=1);

namespace Ess\M2ePro\Setup\Update\y26_m09;

use Ess\M2ePro\Helper\Module\Database\Tables;
use Magento\Framework\DB\Ddl\Table;

class AutoActionAdvancedFilterMode extends \Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->createTableListingAutoAdvancedFilterGroup();
        $this->createTableEbayListingAutoAdvancedFilterGroup();

        $this->migrateData();

        $this->dropColumnsFromListingTable();
        $this->dropColumnsFromEbayListingTable();
    }

    public function createTableListingAutoAdvancedFilterGroup(): void
    {
        $tableName = $this->getFullTableName(Tables::TABLE_LISTING_AUTO_ADVANCED_FILTER);
        if ($this->getConnection()->isTableExists($tableName)) {
            return;
        }

        $table = $this
            ->getConnection()
            ->newTable($tableName)
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_LISTING_ID,
                TABLE::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_TITLE,
                TABLE::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_ADDING_MODE,
                TABLE::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => \Ess\M2ePro\Model\Listing::ADDING_MODE_NONE,
                ]
            )
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_ADDING_ADD_NOT_VISIBLE,
                TABLE::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => \Ess\M2ePro\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
                ]
            )
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_DELETING_MODE,
                TABLE::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'default' => \Ess\M2ePro\Model\Listing::DELETING_MODE_NONE,
                ]
            )
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_CONDITION,
                TABLE::TYPE_TEXT,
                \Ess\M2ePro\Model\Setup\Installer::LONG_COLUMN_SIZE,
                ['nullable' => true]
            )
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_COMPONENT_MODE,
                TABLE::TYPE_TEXT,
                10,
                ['nullable' => false]
            )
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_UPDATE_DATE,
                TABLE::TYPE_DATETIME,
                null,
                ['nullable' => true]
            )
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_CREATE_DATE,
                TABLE::TYPE_DATETIME,
                null,
                ['nullable' => true]
            )
            ->addIndex(
                'listing_id',
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_LISTING_ID
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci');

        $this->getConnection()->createTable($table);
    }

    public function createTableEbayListingAutoAdvancedFilterGroup()
    {
        $ebayListingAutoAdvancedFilterTableName = $this->getFullTableName(
            Tables::TABLE_EBAY_LISTING_AUTO_ADVANCED_FILTER
        );
        if ($this->getConnection()->isTableExists($ebayListingAutoAdvancedFilterTableName)) {
            return;
        }

        $ebayListingAutoAdvancedFilterTable = $this
            ->getConnection()
            ->newTable($ebayListingAutoAdvancedFilterTableName)
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Auto\Advanced\Filter::COLUMN_LISTING_AUTO_ADVANCED_FILTER_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Auto\Advanced\Filter::COLUMN_ADDING_TEMPLATE_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Auto\Advanced\Filter::COLUMN_ADDING_TEMPLATE_CATEGORY_SECONDARY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Auto\Advanced\Filter::COLUMN_ADDING_TEMPLATE_STORE_CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addColumn(
                \Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Auto\Advanced\Filter::COLUMN_ADDING_TEMPLATE_STORE_CATEGORY_SECONDARY_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )
            ->addIndex(
                'listing_auto_advanced_filter_id',
                \Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Auto\Advanced\Filter::COLUMN_LISTING_AUTO_ADVANCED_FILTER_ID
            )
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci');

        $this->getConnection()->createTable($ebayListingAutoAdvancedFilterTable);
    }

    public function migrateData(): void
    {
        $this
            ->getConnection()
            ->truncateTable($this->getFullTableName(Tables::TABLE_LISTING_AUTO_ADVANCED_FILTER))
            ->truncateTable($this->getFullTableName(Tables::TABLE_EBAY_LISTING_AUTO_ADVANCED_FILTER));

        $select = $this->getConnection()->select();
        $select->from(
            ['listing' => $this->getFullTableName(Tables::TABLE_LISTING)],
            [
                'id',
                'component_mode',
                'auto_advanced_filter_adding_mode',
                'auto_advanced_filter_adding_add_not_visible',
                'auto_advanced_filter_condition',
                'auto_advanced_filter_deleting_mode',
            ]
        );
        $select->joinInner(
            ['ebay_listing' => $this->getFullTableName(Tables::TABLE_EBAY_LISTING)],
            'ebay_listing.listing_id = listing.id',
            [
                'auto_advanced_filter_adding_template_category_id',
                'auto_advanced_filter_adding_template_category_secondary_id',
                'auto_advanced_filter_adding_template_store_category_id',
                'auto_advanced_filter_adding_template_store_category_secondary_id',
            ]
        );
        $select->where('listing.auto_mode = ?', 4);

        $query = $this->getConnection()->query($select);

        $this->getConnection()->beginTransaction();
        $idIndex = 1;
        while ($row = $query->fetch()) {
            $currentDate = \Ess\M2ePro\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');
            $dataForInsertToListingAdvancedFilterTable = [
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_ID => $idIndex,
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_LISTING_ID => $row['id'],
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_TITLE => 'Filter #' . $idIndex,
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_ADDING_MODE => (int)$row['auto_advanced_filter_adding_mode'],
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_ADDING_ADD_NOT_VISIBLE => $row['auto_advanced_filter_adding_add_not_visible'],
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_DELETING_MODE => $row['auto_advanced_filter_deleting_mode'],
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_CONDITION => $row['auto_advanced_filter_condition'],
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_COMPONENT_MODE => $row['component_mode'],
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_UPDATE_DATE => $currentDate,
                \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_CREATE_DATE => $currentDate,
            ];

            $dataForInsertToEbayListingAdvancedFilterTable = [
                \Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Auto\Advanced\Filter::COLUMN_LISTING_AUTO_ADVANCED_FILTER_ID => $idIndex,
                \Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Auto\Advanced\Filter::COLUMN_ADDING_TEMPLATE_CATEGORY_ID => $row['auto_advanced_filter_adding_template_category_id'],
                \Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Auto\Advanced\Filter::COLUMN_ADDING_TEMPLATE_CATEGORY_SECONDARY_ID => $row['auto_advanced_filter_adding_template_category_secondary_id'],
                \Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Auto\Advanced\Filter::COLUMN_ADDING_TEMPLATE_STORE_CATEGORY_ID => $row['auto_advanced_filter_adding_template_store_category_id'],
                \Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Auto\Advanced\Filter::COLUMN_ADDING_TEMPLATE_STORE_CATEGORY_SECONDARY_ID => $row['auto_advanced_filter_adding_template_store_category_secondary_id'],
            ];

            $this->getConnection()->insert(
                $this->getFullTableName(Tables::TABLE_LISTING_AUTO_ADVANCED_FILTER),
                $dataForInsertToListingAdvancedFilterTable
            );

            $this->getConnection()->insert(
                $this->getFullTableName(Tables::TABLE_EBAY_LISTING_AUTO_ADVANCED_FILTER),
                $dataForInsertToEbayListingAdvancedFilterTable
            );

            $idIndex++;
        }
        $this->getConnection()->commit();
    }

    public function dropColumnsFromListingTable(): void
    {
        $modifier = $this->getTableModifier(Tables::TABLE_LISTING);
        $modifier->dropColumn(
            'auto_advanced_filter_adding_mode',
            false,
            false
        );
        $modifier->dropColumn(
            'auto_advanced_filter_adding_add_not_visible',
            false,
            false
        );
        $modifier->dropColumn(
            'auto_advanced_filter_deleting_mode',
            false,
            false
        );
        $modifier->dropColumn(
            'auto_advanced_filter_condition',
            false,
            false
        );
        $modifier->commit();
    }

    public function dropColumnsFromEbayListingTable()
    {
        $modifier = $this->getTableModifier(Tables::TABLE_EBAY_LISTING);
        $modifier->dropColumn(
            'auto_advanced_filter_adding_template_category_id',
            true,
            false
        );
        $modifier->dropColumn(
            'auto_advanced_filter_adding_template_category_secondary_id',
            true,
            false
        );
        $modifier->dropColumn(
            'auto_advanced_filter_adding_template_store_category_id',
            true,
            false
        );
        $modifier->dropColumn(
            'auto_advanced_filter_adding_template_store_category_secondary_id',
            true,
            false
        );
        $modifier->commit();
    }
}
