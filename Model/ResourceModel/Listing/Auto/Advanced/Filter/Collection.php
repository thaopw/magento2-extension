<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter;

/**
 * @method \Ess\M2ePro\Model\Listing\Auto\Advanced\Filter getFirstItem()
 * @method \Ess\M2ePro\Model\Listing\Auto\Advanced\Filter[] getItems()
 */
class Collection extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\Component\Parent\AbstractModel
{
    public function _construct()
    {
        $this->_init(
            \Ess\M2ePro\Model\Listing\Auto\Advanced\Filter::class,
            \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::class
        );
    }
}
