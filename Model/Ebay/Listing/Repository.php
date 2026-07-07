<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Ebay\Listing;

class Repository
{
    private \Ess\M2ePro\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory
    ) {
        $this->listingCollectionFactory = $listingCollectionFactory;
    }

    /**
     * @return \Ess\M2ePro\Model\Listing[]
     */
    public function getAll(): array
    {
        $listingsCollection = $this->listingCollectionFactory->createWithEbayChildMode();

        return array_values($listingsCollection->getItems());
    }

    /**
     * @return \Ess\M2ePro\Model\Listing[]
     */
    public function findAutoActionAdvancedFilterListings(): array
    {
        $collection = $this->listingCollectionFactory->createWithEbayChildMode();

        $collection->addFieldToFilter(
            'auto_mode',
            ['eq' => \Ess\M2ePro\Model\Listing::AUTO_MODE_ADVANCED_FILTER]
        );
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Listing::COLUMN_AUTO_ADVANCED_FILTER_ADDING_MODE,
            ['neq' => \Ess\M2ePro\Model\Listing::ADDING_MODE_NONE]
        );

        return array_values($collection->getItems());
    }
}
