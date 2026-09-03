<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Listing;

class Repository
{
    private \Ess\M2ePro\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory
    ) {
        $this->listingCollectionFactory = $listingCollectionFactory;
    }

    public function get(int $id): \Ess\M2ePro\Model\Listing
    {
        $result = $this->find($id);
        if ($result === null) {
            throw new \Ess\M2ePro\Model\Exception\EntityNotFound("Listing with id '$id' does not exist.");
        }

        return $result;
    }

    public function find(int $id): ?\Ess\M2ePro\Model\Listing
    {
        $collection = $this->listingCollectionFactory->createWithAmazonChildMode();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Listing::COLUMN_ID,
            $id
        );

        $result = $collection->getFirstItem();
        if ($result->isObjectNew()) {
            return null;
        }

        return $result;
    }

    /**
     * @return \Ess\M2ePro\Model\Listing[]
     */
    public function getAll(): array
    {
        $listingsCollection = $this->listingCollectionFactory->createWithAmazonChildMode();

        return array_values($listingsCollection->getItems());
    }

    public function isSellingPolicyUseOnlyForUsMarketplaces(int $sellingPolicyId): bool
    {
        $collection = $this->listingCollectionFactory->createWithAmazonChildMode();
        $collection->addFieldToFilter(
            'template_selling_format_id',
            ['eq' => $sellingPolicyId]
        );
        $collection->addFieldToFilter(
            'marketplace_id',
            ['neq' => \Ess\M2ePro\Helper\Component\Amazon::MARKETPLACE_US]
        );

        return $collection->getSize() === 0;
    }
}
