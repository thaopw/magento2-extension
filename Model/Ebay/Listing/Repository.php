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

    public function find(int $id): ?\Ess\M2ePro\Model\Listing
    {
        $collection = $this->listingCollectionFactory->createWithEbayChildMode();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Listing::COLUMN_ID,
            ['eq' => $id]
        );

        $listing = $collection->getFirstItem();
        if ($listing->isObjectNew()) {
            return null;
        }

        return $listing;
    }

    public function get(int $id): \Ess\M2ePro\Model\Listing
    {
        $listing = $this->find($id);
        if ($listing === null) {
            throw new \Ess\M2ePro\Model\Exception\EntityNotFound("Listing '$id' not found");
        }

        return $listing;
    }

    /**
     * @return \Ess\M2ePro\Model\Listing[]
     */
    public function getAll(): array
    {
        $listingsCollection = $this->listingCollectionFactory->createWithEbayChildMode();

        return array_values($listingsCollection->getItems());
    }
}
