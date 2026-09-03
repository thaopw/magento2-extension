<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Listing\Auto\Actions;

class AdvancedFilterMode
{
    private Listing\Factory $autoActionsListingFactory;
    private Mode\DuplicateProducts $duplicateProducts;
    private \Ess\M2ePro\Model\Magento\Product\RuleFactory $ruleFactory;
    private \Ess\M2ePro\Model\Magento\ProductFactory $magentoProductFactory;
    private \Ess\M2ePro\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory;
    private \Ess\M2ePro\Model\ResourceModel\Listing\Product\CollectionFactory $listingProductCollectionFactory;
    private \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter\CollectionFactory $autoAdvancedFilterCollectionFactory;

    public function __construct(
        \Ess\M2ePro\Model\Magento\Product\RuleFactory $ruleFactory,
        \Ess\M2ePro\Model\Listing\Auto\Actions\Listing\Factory $autoActionsListingFactory,
        \Ess\M2ePro\Model\Listing\Auto\Actions\Mode\DuplicateProducts $duplicateProducts,
        \Ess\M2ePro\Model\Magento\ProductFactory $magentoProductFactory,
        \Ess\M2ePro\Model\ResourceModel\Listing\CollectionFactory $listingCollectionFactory,
        \Ess\M2ePro\Model\ResourceModel\Listing\Product\CollectionFactory $listingProductCollectionFactory,
        \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter\CollectionFactory $autoAdvancedFilterCollectionFactory
    ) {
        $this->autoActionsListingFactory = $autoActionsListingFactory;
        $this->duplicateProducts = $duplicateProducts;
        $this->ruleFactory = $ruleFactory;
        $this->magentoProductFactory = $magentoProductFactory;
        $this->listingCollectionFactory = $listingCollectionFactory;
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->autoAdvancedFilterCollectionFactory = $autoAdvancedFilterCollectionFactory;
    }

    public function synchByProductId(int $magentoProductId): void
    {
        $listings = $this->getListings();
        if (empty($listings)) {
            return;
        }

        $magentoProductsByStoreId = [];

        foreach ($listings as $listing) {
            if (!isset($magentoProductsByStoreId[$listing->getStoreId()])) {
                $magentoProductsByStoreId[$listing->getStoreId()] = $this
                    ->createMagentoProduct($magentoProductId, $listing->getStoreId());
            }

            $magentoProduct = $magentoProductsByStoreId[$listing->getStoreId()];

            $advancedFilterRules = $this->getAdvancedFilterRulesByListingId((int)$listing->getId());
            if (empty($advancedFilterRules)) {
                continue;
            }

            $isProductInListing = $this->isExistProductInListing(
                (int)$listing->getId(),
                (int)$magentoProduct->getId()
            );

            foreach ($advancedFilterRules as $advancedFilterRule) {
                $ruleModel = $this->ruleFactory->create(
                    \Ess\M2ePro\Model\Listing\Auto\Advanced\Filter::RULE_MODEL_PREFIX,
                    $listing->getStoreId()
                );
                $ruleModel->loadFromSerialized($advancedFilterRule->getCondition());

                if (
                    (!$isProductInListing && $advancedFilterRule->isAddingModeNone())
                    || ($isProductInListing && $advancedFilterRule->isAutoDeletingModeNone())
                ) {
                    continue;
                }

                $isValidCondition = $ruleModel->validate($magentoProduct);

                if (!$isProductInListing && $isValidCondition) {
                    $this->addProductToListing($listing, $advancedFilterRule, $magentoProduct);
                }

                if ($isProductInListing && !$isValidCondition) {
                    $this->deleteProductFromListing($listing, $advancedFilterRule, $magentoProduct);
                }
            }
        }
    }

    private function addProductToListing(
        \Ess\M2ePro\Model\Listing $listing,
        \Ess\M2ePro\Model\Listing\Auto\Advanced\Filter $autoAdvancedFilter,
        \Magento\Catalog\Model\Product $magentoProduct
    ): void {
        if ($autoAdvancedFilter->isAddingModeNone()) {
            return;
        }

        if (!$autoAdvancedFilter->isAutoAddingAddNotVisibleYes()) {
            if (
                $magentoProduct->getVisibility()
                == \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
            ) {
                return;
            }
        }

        if ($this->duplicateProducts->checkDuplicateListingProduct($listing, $magentoProduct)) {
            return;
        }

        $autoActionListing = $this->autoActionsListingFactory->create($listing);
        $autoActionListing->addProductByAdvancedFilterListing(
            $magentoProduct,
            $autoAdvancedFilter
        );
    }

    private function deleteProductFromListing(
        \Ess\M2ePro\Model\Listing $listing,
        \Ess\M2ePro\Model\Listing\Auto\Advanced\Filter $autoAdvancedFilter,
        \Magento\Catalog\Model\Product $magentoProduct
    ) {
        if ($autoAdvancedFilter->isAutoDeletingModeNone()) {
            return;
        }

        $autoActionListing = $this->autoActionsListingFactory->create($listing);
        $autoActionListing->deleteProduct(
            $magentoProduct,
            $autoAdvancedFilter->getDeletingMode()
        );
    }

    private function createMagentoProduct(int $magentoProductId, int $storeId): \Magento\Catalog\Model\Product
    {
        $product = $this->magentoProductFactory->create();
        $product->setProductId($magentoProductId);
        $product->setStoreId($storeId);

        return $product->getProduct();
    }

    /**
     * @return \Ess\M2ePro\Model\Listing[]
     */
    private function getListings(): array
    {
        $collection = $this->listingCollectionFactory->create();
        $collection->addFieldToFilter(
            'auto_mode',
            ['eq' => \Ess\M2ePro\Model\Listing::AUTO_MODE_ADVANCED_FILTER]
        );

        return array_values($collection->getItems());
    }

    /**
     * @return \Ess\M2ePro\Model\Listing\Auto\Advanced\Filter[]
     */
    private function getAdvancedFilterRulesByListingId(int $listingId): array
    {
        $collection = $this->autoAdvancedFilterCollectionFactory->create();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_LISTING_ID,
            ['eq' => $listingId]
        );
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_ADDING_MODE,
            ['neq' => \Ess\M2ePro\Model\Listing::ADDING_MODE_NONE]
        );

        return array_values($collection->getItems());
    }

    private function isExistProductInListing(int $listingId, int $magentoProductId): bool
    {
        $collection = $this->listingProductCollectionFactory->create();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Listing\Product::LISTING_ID_FIELD,
            ['eq' => $listingId]
        );
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Listing\Product::PRODUCT_ID_FIELD,
            ['eq' => $magentoProductId]
        );

        return $collection->count() > 0;
    }
}
