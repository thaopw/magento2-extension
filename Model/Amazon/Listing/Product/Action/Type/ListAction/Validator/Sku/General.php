<?php

namespace Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\ListAction\Validator\Sku;

class General extends \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Validator
{
    public const SKU_MAX_LENGTH = 40;

    /**
     * @return bool
     */
    public function validate()
    {
        $sku = $this->getSku();

        if (empty($sku)) {
            $this->addMessage('SKU is not provided. Please, check Listing Settings.', self::ERROR_SKU_MISSING);

            return false;
        }

        if (mb_strlen($sku) > self::SKU_MAX_LENGTH) {
            $this->addMessage('The length of SKU must be less than 40 characters.', self::ERROR_SKU_LENGTH_EXCEEDED);

            return false;
        }

        $this->setData('sku', $sku);

        return true;
    }

    //########################################

    private function getSku()
    {
        if (isset($this->getData()['sku'])) {
            return $this->getData('sku');
        }

        $sku = $this->getAmazonListingProduct()->getSku();
        if (!empty($sku)) {
            return $sku;
        }

        if (
            $this->getVariationManager()->isPhysicalUnit() &&
            $this->getVariationManager()->getTypeModel()->isVariationProductMatched()
        ) {
            $variations = $this->getListingProduct()->getVariations(true);
            if (empty($variations)) {
                throw new \Ess\M2ePro\Model\Exception\Logic(
                    'There are no variations for a variation product.',
                    [
                        'listing_product_id' => $this->getListingProduct()->getId(),
                    ]
                );
            }
            /** @var \Ess\M2ePro\Model\Listing\Product\Variation $variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getSku();
        }

        return $this->getAmazonListingProduct()->getListingSource()->getSku();
    }
}
