<?php

namespace Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\ListAction\Validator\Sku;

class Search extends \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Validator
{
    /** @var null  */
    private $skusInProcessing = null;

    // ----------------------------------------

    /**
     * @return bool
     * @throws \Ess\M2ePro\Model\Exception
     */
    public function validate(): bool
    {
        $sku = $this->getSku();

        $generateSkuMode = $this->getAmazonListingProduct()->getAmazonListing()->isGenerateSkuModeYes();

        if (!$this->isExistInM2ePro($sku, !$generateSkuMode)) {
            return true;
        }

        if (!$generateSkuMode) {
            return false;
        }

        $unifiedSku = $this->getUnifiedSku($sku);
        if ($this->checkSkuRequirements($unifiedSku)) {
            $this->setData('sku', $unifiedSku);

            return true;
        }

        if ($this->getVariationManager()->isIndividualType() || $this->getVariationManager()->isRelationChildType()) {
            $baseSku = $this->getAmazonListing()->getSource($this->getMagentoProduct())->getSku();

            $unifiedBaseSku = $this->getUnifiedSku($baseSku);
            if ($this->checkSkuRequirements($unifiedBaseSku)) {
                $this->setData('sku', $unifiedBaseSku);

                return true;
            }
        }

        $unifiedSku = $this->getUnifiedSku();
        if ($this->checkSkuRequirements($unifiedSku)) {
            $this->setData('sku', $unifiedSku);

            return true;
        }

        $randomSku = $this->getRandomSku();
        if ($this->checkSkuRequirements($randomSku)) {
            $this->setData('sku', $randomSku);

            return true;
        }

        $this->addMessage('SKU generating is not successful.', \Ess\M2ePro\Model\Tag\ValidatorIssues::NOT_USER_ERROR);

        return false;
    }

    // ----------------------------------------

    private function getSku()
    {
        if (empty($this->getData('sku'))) {
            throw new \Ess\M2ePro\Model\Exception('SKU is not defined.');
        }

        return $this->getData('sku');
    }

    private function getUnifiedSku($prefix = 'SKU'): string
    {
        return $prefix . '_' . $this->getListingProduct()->getProductId() . '_' . $this->getListingProduct()->getId();
    }

    private function getRandomSku(): string
    {
        $hash = sha1(rand(0, 10000) . microtime(1));

        return $this->getUnifiedSku() . '_' . substr($hash, 0, 10);
    }

    // ----------------------------------------

    private function checkSkuRequirements(string $sku): bool
    {
        if (
            mb_strlen(
                $sku
            ) > \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\ListAction\Validator\Sku\General::SKU_MAX_LENGTH
        ) {
            return false;
        }

        if ($this->isExistInM2ePro($sku, false)) {
            return false;
        }

        return true;
    }

    // ----------------------------------------

    private function isExistInM2ePro($sku, $addMessages = false): bool
    {
        if ($this->isAlreadyInProcessing($sku)) {
            $addMessages && $this->addMessage(
                'Another Product with the same SKU is being Listed simultaneously
                                with this one. Please change the SKU or enable the Option Generate Merchant SKU.',
                self::ERROR_SKU_ALREADY_PROCESSING
            );

            return true;
        }

        if ($this->isExistInM2eProListings($sku)) {
            $addMessages && $this->addMessage(
                'Product with the same SKU is found in other M2E Pro Listing that is created
                 from the same Merchant ID for the same Marketplace.',
                self::ERROR_DUPLICATE_SKU_LISTING
            );

            return true;
        }

        if ($this->isExistInOtherListings($sku)) {
            $addMessages && $this->addMessage(
                'Product with the same SKU is found in M2E Pro Unmanaged Listing.
                                            Please change the SKU or enable the Option Generate Merchant SKU.',
                self::ERROR_DUPLICATE_SKU_UNMANAGED
            );

            return true;
        }

        return false;
    }

    // ---------------------------------------

    private function isAlreadyInProcessing($sku): bool
    {
        return in_array($sku, $this->getSkusInProcessing());
    }

    private function isExistInM2eProListings($sku): bool
    {
        $listingTable = $this->activeRecordFactory->getObject('Listing')->getResource()->getMainTable();

        /** @var \Ess\M2ePro\Model\ResourceModel\Listing\Product\Collection $collection */
        $collection = $this->amazonFactory->getObject('Listing\Product')->getCollection();
        $collection->getSelect()->join(
            ['l' => $listingTable],
            '`main_table`.`listing_id` = `l`.`id`',
            []
        );

        $collection->addFieldToFilter('sku', $sku);
        $collection->addFieldToFilter('account_id', $this->getListingProduct()->getAccount()->getId());

        return $collection->getSize() > 0;
    }

    private function isExistInOtherListings($sku): bool
    {
        /** @var \Ess\M2ePro\Model\ResourceModel\Listing\Other\Collection $collection */
        $collection = $this->amazonFactory->getObject('Listing\Other')->getCollection();

        $collection->addFieldToFilter('sku', $sku);
        $collection->addFieldToFilter('account_id', $this->getListingProduct()->getAccount()->getId());

        return $collection->getSize() > 0;
    }

    // ----------------------------------------

    private function getSkusInProcessing()
    {
        if ($this->skusInProcessing !== null) {
            return $this->skusInProcessing;
        }

        $processingActionListSkuCollection = $this->activeRecordFactory
            ->getObject('Amazon_Listing_Product_Action_ProcessingListSku')
            ->getCollection();
        $processingActionListSkuCollection->addFieldToFilter('account_id', $this->getListing()->getAccountId());

        return $this->skusInProcessing = $processingActionListSkuCollection->getColumnValues('sku');
    }
}
