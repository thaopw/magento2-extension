<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Listing\Product;

class Repository
{
    private \Ess\M2ePro\Model\ResourceModel\Listing\Product\CollectionFactory $listingProductCollectionFactory;

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Listing\Product\CollectionFactory $listingProductCollectionFactory
    ) {
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
    }

    public function get(int $id): \Ess\M2ePro\Model\Listing\Product
    {
        $collection = $this->listingProductCollectionFactory->createWithAmazonChildMode();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Listing\Product::COLUMN_ID,
            ['eq' => $id]
        );

        $item = $collection->getFirstItem();
        if ($item->isObjectNew()) {
            throw new \Ess\M2ePro\Model\Exception\Logic(
                sprintf('Listing Product with id %s not found', $id)
            );
        }

        return $item;
    }

    public function save(\Ess\M2ePro\Model\Listing\Product $listingProduct): void
    {
        $listingProduct->save();
    }

    /**
     * @return \Ess\M2ePro\Model\Listing\Product[]
     */
    public function getProductsForSearchAsin(int $limit, int $maxSearchAsinAttempt): array
    {
        $collection = $this->listingProductCollectionFactory->createWithAmazonChildMode();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Listing\Product::STATUS_FIELD,
            ['eq' => \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED]
        );
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product::COLUMN_IS_GENERAL_ID_OWNER,
            ['eq' => \Ess\M2ePro\Model\Amazon\Listing\Product::IS_GENERAL_ID_OWNER_NO]
        );
        $collection->addFieldToFilter(
            [
                'search_settings_status',
                'search_settings_status',
            ],
            [
                ['neq' => \Ess\M2ePro\Model\Amazon\Listing\Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS],
                ['null' => true],
            ]
        );
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product::COLUMN_GENERAL_ID,
            ['null' => true]
        );
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product::COLUMN_AUTO_SEARCH_ASIN_ATTEMPT,
            ['lteq' => $maxSearchAsinAttempt]
        );

        $weekAgoDate = \Ess\M2ePro\Helper\Date::createCurrentGmt()->modify('-7 days')->format('Y-m-d H:i:s');
        $collection->addFieldToFilter(
            [
                \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product::COLUMN_AUTO_SEARCH_ASIN_LAST_ATTEMPT_DATE,
                \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product::COLUMN_AUTO_SEARCH_ASIN_LAST_ATTEMPT_DATE,
            ],
            [
                ['null' => true],
                ['lt' => $weekAgoDate],
            ]
        );

        $collection->getSelect()->limit($limit);

        return array_values($collection->getItems());
    }
}
