<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\ResourceModel\Amazon\Template;

class Shipping extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_TITLE = 'title';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_MARKETPLACE_ID = 'marketplace_id';
    public const COLUMN_MODE = 'mode';
    public const COLUMN_TEMPLATE_ID = 'template_id';
    public const COLUMN_CUSTOM_ATTRIBUTE = 'custom_attribute';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    public function _construct()
    {
        $this->_init(
            \Ess\M2ePro\Helper\Module\Database\Tables::TABLE_AMAZON_TEMPLATE_SHIPPING,
            self::COLUMN_ID
        );
    }
}
