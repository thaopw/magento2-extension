<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Template\Shipping;

class IsLocked
{
    private \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\CollectionFactory $listingCollectionFactory;
    private \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\CollectionFactory $listingCollectionFactory,
        \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product\CollectionFactory $listingProductCollectionFactory
    ) {
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function execute(int $templateShippingId): bool
    {
        $listingCollection = $this->listingCollectionFactory->create();
        $listingCollection->addFieldToFilter(
            'template_shipping_id',
            ['eq' => $templateShippingId]
        );

        if ($listingCollection->getSize() > 0) {
            return true;
        }

        $listingProductCollection = $this->listingProductCollectionFactory->create();
        $listingProductCollection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product::COLUMN_TEMPLATE_SHIPPING_ID,
            ['eq' => $templateShippingId]
        );

        if ($listingProductCollection->getSize() > 0) {
            return true;
        }

        return false;
    }
}
