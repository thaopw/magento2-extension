<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced;

class Filter extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Component\Parent\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_LISTING_ID = 'listing_id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_ADDING_MODE = 'adding_mode';
    public const COLUMN_ADDING_ADD_NOT_VISIBLE = 'adding_add_not_visible';
    public const COLUMN_DELETING_MODE = 'deleting_mode';
    public const COLUMN_CONDITION = 'condition';
    public const COLUMN_COMPONENT_MODE = 'component_mode';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    protected function _construct()
    {
        $this->_init(
            \Ess\M2ePro\Helper\Module\Database\Tables::TABLE_LISTING_AUTO_ADVANCED_FILTER,
            self::COLUMN_ID
        );
    }
}
