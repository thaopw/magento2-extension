<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\ResourceModel\Amazon\Dictionary;

class TemplateShipping extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_TEMPLATE_ID = 'template_id';
    public const COLUMN_TITLE = 'title';

    protected function _construct()
    {
        $this->_init(
            \Ess\M2ePro\Helper\Module\Database\Tables::TABLE_AMAZON_DICTIONARY_TEMPLATE_SHIPPING,
            self::COLUMN_ID
        );
    }
}
