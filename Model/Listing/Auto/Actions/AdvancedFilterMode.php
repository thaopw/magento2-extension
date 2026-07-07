<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Listing\Auto\Actions;

class AdvancedFilterMode
{
    private Listing\Factory $autoActionsListingFactory;
    private Mode\DuplicateProducts $duplicateProducts;
    private \Ess\M2ePro\Model\Ebay\Listing\Repository $ebayListingRepository;
    private \Ess\M2ePro\Model\Magento\Product\RuleFactory $ruleFactory;
    private \Ess\M2ePro\Model\Ebay\Listing\Product\Repository $ebayListingProductRepository;
    private \Ess\M2ePro\Model\Magento\ProductFactory $magentoProductFactory;

    public function __construct(
        \Ess\M2ePro\Model\Magento\Product\RuleFactory $ruleFactory,
        \Ess\M2ePro\Model\Ebay\Listing\Repository $ebayListingRepository,
        \Ess\M2ePro\Model\Ebay\Listing\Product\Repository $ebayListingProductRepository,
        \Ess\M2ePro\Model\Listing\Auto\Actions\Listing\Factory $autoActionsListingFactory,
        \Ess\M2ePro\Model\Listing\Auto\Actions\Mode\DuplicateProducts $duplicateProducts,
        \Ess\M2ePro\Model\Magento\ProductFactory $magentoProductFactory
    ) {
        $this->autoActionsListingFactory = $autoActionsListingFactory;
        $this->duplicateProducts = $duplicateProducts;
        $this->ebayListingRepository = $ebayListingRepository;
        $this->ruleFactory = $ruleFactory;
        $this->ebayListingProductRepository = $ebayListingProductRepository;
        $this->magentoProductFactory = $magentoProductFactory;
    }

    public function synchByProductId(int $magentoProductId): void
    {
        $listings = $this->ebayListingRepository->findAutoActionAdvancedFilterListings();
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

            $ruleModel = $this->ruleFactory->create('ebay_auto_action_advanced_filter', $listing->getStoreId());
            $ruleModel->loadFromSerialized($listing->getAutoAdvancedFilterCondition());

            $isProductInListing = $this->ebayListingProductRepository
                ->isExistProductInListing((int)$listing->getId(), (int)$magentoProduct->getId());

            if (
                (!$isProductInListing && $listing->isAutoAdvancedFilterAddingModeNone())
                || ($isProductInListing && $listing->isAutoAdvancedFilterDeletingModeNone())
            ) {
                return;
            }

            $isValidCondition = $ruleModel->validate($magentoProduct);

            if (!$isProductInListing && $isValidCondition) {
                $this->addProductToListing($listing, $magentoProduct);
            }

            if ($isProductInListing && !$isValidCondition) {
                $this->deleteProductFromListing($listing, $magentoProduct);
            }
        }
    }

    private function addProductToListing(
        \Ess\M2ePro\Model\Listing $listing,
        \Magento\Catalog\Model\Product $magentoProduct
    ): void {
        if ($listing->isAutoAdvancedFilterAddingModeNone()) {
            return;
        }

        if (!$listing->isAutoAdvancedFilterAddingAddNotVisibleYes()) {
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
            $listing
        );
    }

    private function deleteProductFromListing(
        \Ess\M2ePro\Model\Listing $listing,
        \Magento\Catalog\Model\Product $magentoProduct
    ) {
        if ($listing->isAutoAdvancedFilterDeletingModeNone()) {
            return;
        }

        $autoActionListing = $this->autoActionsListingFactory->create($listing);
        $autoActionListing
            ->deleteProduct($magentoProduct, $listing->getAutoAdvancedFilterDeletingMode());
    }

    private function createMagentoProduct(int $magentoProductId, int $storeId): \Magento\Catalog\Model\Product
    {
        $product = $this->magentoProductFactory->create();
        $product->setProductId($magentoProductId);
        $product->setStoreId($storeId);

        return $product->getProduct();
    }
}
