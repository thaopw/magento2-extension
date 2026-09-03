<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Listing;

class AffectedListingsProducts extends \Ess\M2ePro\Model\Template\AffectedListingsProductsAbstract
{
    protected \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory;
    private \Ess\M2ePro\Model\ResourceModel\Listing\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Listing\Product\CollectionFactory $listingProductCollectionFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        array $data = []
    ) {
        parent::__construct($activeRecordFactory, $helperFactory, $modelFactory, $data);
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    // ----------------------------------------

    public function loadCollection(array $filters = []): \Ess\M2ePro\Model\ResourceModel\Listing\Product\Collection
    {
        $listingProductCollection = $this->listingProductCollectionFactory->createWithAmazonChildMode();
        $listingProductCollection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Listing\Product::LISTING_ID_FIELD,
            ['eq' => (int)$this->model->getId()]
        );

        if (!empty($filters['only_physical_units'])) {
            $listingProductCollection->addFieldToFilter(
                \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product::COLUMN_IS_VARIATION_PARENT,
                ['eq' => 0]
            );
        }

        if (!empty($filters['template_shipping_id'])) {
            $listingProductCollection->addFieldToFilter(
                \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product::COLUMN_TEMPLATE_SHIPPING_ID,
                [
                    ['null' => true],
                    ['eq' => 0],
                ]
            );
        }

        return $listingProductCollection;
    }
}
