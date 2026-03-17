<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Template\Shipping;

use Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product as ListingProductResource;

class AffectedListingsProducts extends \Ess\M2ePro\Model\Template\AffectedListingsProductsAbstract
{
    private \Ess\M2ePro\Model\ResourceModel\Listing\Product\CollectionFactory $listingProductCollectionFactory;
    private \Ess\M2ePro\Model\ResourceModel\Amazon\Listing $amazonListingResource;

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Listing\Product\CollectionFactory $listingProductCollectionFactory,
        \Ess\M2ePro\Model\ResourceModel\Amazon\Listing $amazonListingResource,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        array $data = []
    ) {
        parent::__construct($activeRecordFactory, $helperFactory, $modelFactory, $data);
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->amazonListingResource = $amazonListingResource;
    }

    /**
     * @param array $filters
     *
     * @return \Ess\M2ePro\Model\ResourceModel\Listing\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadCollection(array $filters = []): \Ess\M2ePro\Model\ResourceModel\Listing\Product\Collection
    {
        $collection = $this->listingProductCollectionFactory->createWithAmazonChildMode();

        $collection->join(
            ['amazon_listing' => $this->amazonListingResource->getMainTable()],
            'amazon_listing.listing_id = main_table.listing_id',
            []
        );

        $whereCondition = sprintf(
            'IF(second_table.%1$s, second_table.%1$s, IF(amazon_listing.%2$s, amazon_listing.%2$s, NULL)) = ?',
            ListingProductResource::COLUMN_TEMPLATE_SHIPPING_ID,
            'template_shipping_id'
        );
        $collection
            ->getSelect()
            ->where(new \Zend_Db_Expr($whereCondition), (int)$this->model->getId());

        if (!empty($filters['only_physical_units'])) {
            $collection->addFieldToFilter('is_variation_parent', 0);
        }

        return $collection;
    }
}
