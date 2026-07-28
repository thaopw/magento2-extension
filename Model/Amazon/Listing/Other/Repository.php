<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Listing\Other;

class Repository
{
    private \Ess\M2ePro\Model\ResourceModel\Listing\Other\CollectionFactory $collectionFactory;

    public function __construct(\Ess\M2ePro\Model\ResourceModel\Listing\Other\CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param int[] $ids
     *
     * @return \Ess\M2ePro\Model\Listing\Other[]
     */
    public function getByIds(array $ids): array
    {
        $collection = $this->collectionFactory->createWithAmazonChildMode();
        $collection->addFieldToFilter('id', ['in' => $ids]);

        return array_values($collection->getItems());
    }

    public function delete(\Ess\M2ePro\Model\Listing\Other $unmanagedProduct)
    {
        if ($unmanagedProduct->getProductId() !== null) {
            $unmanagedProduct->unmapProduct();
        }

        $unmanagedProduct->delete();
    }
}
