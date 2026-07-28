<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\ResourceModel\Listing\Other;

/**
 * @method \Ess\M2ePro\Model\Listing\Other[] getItems()
 * @method \Ess\M2ePro\Model\Listing\Other getFirstItem()
 */
class Collection extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\Component\Parent\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \Ess\M2ePro\Model\Listing\Other::class,
            \Ess\M2ePro\Model\ResourceModel\Listing\Other::class
        );
    }

    /**
     * @param int $productId
     * @param int $accountId
     * @param int $marketplaceId
     * @param string $componentMode
     *
     * @return bool
     */
    public function isExistsProduct(
        int $productId,
        int $accountId,
        int $marketplaceId,
        string $componentMode
    ): bool {
        $this
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('account_id', $accountId)
            ->addFieldToFilter('marketplace_id', $marketplaceId)
            ->addFieldToFilter('component_mode', $componentMode);

        return $this->getSize() > 0;
    }
}
