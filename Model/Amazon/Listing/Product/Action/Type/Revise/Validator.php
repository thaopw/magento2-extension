<?php

namespace Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Revise;

use Ess\M2ePro\Model\Amazon\Listing\Product\Action\DataBuilder\Qty as QtyBuilder;

class Validator extends \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Validator
{
    /**
     * @return bool
     */
    public function validate()
    {
        if (!$this->validateBlocked()) {
            return false;
        }

        if ($this->getVariationManager()->isRelationParentType() && !$this->validateParentListingProduct()) {
            return false;
        }

        $params = $this->getParams();

        if (!empty($params['switch_to']) && !$this->getConfigurator()->isQtyAllowed()) {
            $this->addMessage(
                'Fulfillment mode can not be switched if QTY feed is not allowed.',
                self::ERROR_CANNOT_SWITCH_FULFILLMENT_NO_QTY_FEED
            );

            return false;
        }

        if ($this->getConfigurator()->isQtyAllowed()) {
            if ($this->getAmazonListingProduct()->isAfnChannel()) {
                if (empty($params['switch_to'])) {
                    $this->getConfigurator()->disallowQty();

                    $this->addMessage(
                        'Product Quantity, Production Time and Restock Date were not revised
                        because this information of AFN Items is managed by Amazon',
                        null,
                        \Ess\M2ePro\Model\Connector\Connection\Response\Message::TYPE_WARNING
                    );
                } else {
                    if ($params['switch_to'] === QtyBuilder::FULFILLMENT_MODE_AFN) {
                        $this->addMessage(
                            'You cannot switch Fulfillment because it is applied now.',
                            self::FULFILLMENT_ALREADY_APPLIED
                        );

                        return false;
                    }
                }
            } else {
                if (!empty($params['switch_to']) && $params['switch_to'] === QtyBuilder::FULFILLMENT_MODE_MFN) {
                    $this->addMessage(
                        'You cannot switch Fulfillment because it is applied now.',
                        self::FULFILLMENT_ALREADY_APPLIED
                    );

                    return false;
                }
            }
        }

        if (
            $this->getAmazonListingProduct()->isAfnChannel() &&
            $this->getAmazonListingProduct()->isExistShippingTemplate()
        ) {
            $this->addMessage(
                'The Shipping Settings will not be sent for this Product because it is an FBA Item.
                Amazon will handle the delivery of the Order.',
                null,
                \Ess\M2ePro\Model\Connector\Connection\Response\Message::TYPE_WARNING
            );
        }

        if ($this->getVariationManager()->isPhysicalUnit() && !$this->validatePhysicalUnitMatching()) {
            return false;
        }

        if (!$this->validateSku()) {
            return false;
        }

        if (
            !$this->getAmazonListingProduct()->isAfnChannel()
            && ($this->isChangerUser() && !$this->getListingProduct()->isBlocked())
            && (!$this->getListingProduct()->isListed() || !$this->getListingProduct()->isRevisable())
        ) {
            $this->addMessage(
                'Item is not Listed or not available',
                \Ess\M2ePro\Model\Tag\ValidatorIssues::NOT_USER_ERROR
            );

            return false;
        }

        if (!$this->validateQuantity()) {
            return false;
        }

        if (!$this->validateRegularPrice() || !$this->validateBusinessPrice()) {
            return false;
        }

        return true;
    }

    private function validateQuantity(): bool
    {
        if ($this->getListingProduct()->isBlocked()) {
            return $this->forceValidateQty();
        }

        return $this->validateQty();
    }

    protected function validateParentListingProduct()
    {
        if (
            !$this->getConfigurator()->isDetailsAllowed()
            || !$this->getAmazonListingProduct()->isExistsProductTypeTemplate()
        ) {
            $this->addMessage(
                'There was no need for this action. It was skipped.',
                \Ess\M2ePro\Model\Tag\ValidatorIssues::NOT_USER_ERROR
            );

            return false;
        }

        return true;
    }
}
