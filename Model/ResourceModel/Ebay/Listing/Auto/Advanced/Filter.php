<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Auto\Advanced;

class Filter extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Component\Child\AbstractModel
{
    public const COLUMN_LISTING_AUTO_ADVANCED_FILTER_ID = 'listing_auto_advanced_filter_id';
    public const COLUMN_ADDING_TEMPLATE_CATEGORY_ID = 'adding_template_category_id';
    public const COLUMN_ADDING_TEMPLATE_CATEGORY_SECONDARY_ID = 'adding_template_category_secondary_id';
    public const COLUMN_ADDING_TEMPLATE_STORE_CATEGORY_ID = 'adding_template_store_category_id';
    public const COLUMN_ADDING_TEMPLATE_STORE_CATEGORY_SECONDARY_ID = 'adding_template_store_category_secondary_id';

    protected function _construct()
    {
        $this->_init(
            \Ess\M2ePro\Helper\Module\Database\Tables::TABLE_EBAY_LISTING_AUTO_ADVANCED_FILTER,
            self::COLUMN_LISTING_AUTO_ADVANCED_FILTER_ID
        );
        $this->_isPkAutoIncrement = false;
    }
}
