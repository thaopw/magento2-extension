<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping;

/**
 * @method \Ess\M2ePro\Model\Amazon\Template\Shipping[] getItems()
 * @method \Ess\M2ePro\Model\Amazon\Template\Shipping getFirstItem()
 */
class Collection extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \Ess\M2ePro\Model\Amazon\Template\Shipping::class,
            \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping::class
        );
    }
}
