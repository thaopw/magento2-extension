<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\ResourceModel\Amazon\Dictionary\TemplateShipping;

/**
 * @method \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping[] getItems()
 * @method \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping getFirstItem()
 */
class Collection extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping::class,
            \Ess\M2ePro\Model\ResourceModel\Amazon\Dictionary\TemplateShipping::class
        );
    }

    /**
     * @param int $accountId
     *
     * @return $this
     */
    public function appendFilterAccountId(int $accountId): self
    {
        $this->getSelect()->where('main_table.account_id = ?', $accountId);

        return $this;
    }
}
