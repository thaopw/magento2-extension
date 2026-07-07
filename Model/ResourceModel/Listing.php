<?php

namespace Ess\M2ePro\Model\ResourceModel;

class Listing extends ActiveRecord\Component\Parent\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_MARKETPLACE_ID = 'marketplace_id';
    public const COLUMN_AUTO_ADVANCED_FILTER_ADDING_MODE = 'auto_advanced_filter_adding_mode';
    public const COLUMN_AUTO_ADVANCED_FILTER_ADDING_ADD_NOT_VISIBLE = 'auto_advanced_filter_adding_add_not_visible';
    public const COLUMN_AUTO_ADVANCED_FILTER_DELETING_MODE = 'auto_advanced_filter_deleting_mode';
    public const COLUMN_AUTO_ADVANCED_FILTER_CONDITION = 'auto_advanced_filter_condition';

    public function _construct()
    {
        $this->_init(
            \Ess\M2ePro\Helper\Module\Database\Tables::TABLE_LISTING,
            self::COLUMN_ID
        );
    }
}
