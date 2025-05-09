<?php

namespace Ess\M2ePro\Model\Walmart\Listing\Product\Instruction\SynchronizationTemplate\Checker;

class NotListed extends AbstractModel
{
    public function isAllowed()
    {
        if (!parent::isAllowed()) {
            return false;
        }

        $listingProduct = $this->input->getListingProduct();

        if (!$listingProduct->isListable() || !$listingProduct->isNotListed()) {
            return false;
        }

        /** @var \Ess\M2ePro\Model\Walmart\Listing\Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();

        if (
            $walmartListingProduct->getWalmartMarketplace()
                                  ->isSupportedProductType()
            && !$walmartListingProduct->isExistsProductType()
            && !$walmartListingProduct->isAvailableMappingToExistingChannelItem()
        ) {
            return false;
        }

        $variationManager = $walmartListingProduct->getVariationManager();

        if ($variationManager->isVariationProduct()) {
            if (
                $variationManager->isPhysicalUnit() &&
                !$variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                return false;
            }

            if ($variationManager->isRelationParentType()) {
                return false;
            }
        }

        return true;
    }

    public function process(array $params = [])
    {
        if (!$this->isMeetListRequirements()) {
            if ($this->input->getScheduledAction() && !$this->input->getScheduledAction()->isForce()) {
                $this->getScheduledActionManager()->deleteAction($this->input->getScheduledAction());
            }

            return;
        }

        if ($this->input->getScheduledAction() && $this->input->getScheduledAction()->isActionTypeList()) {
            return;
        }

        $scheduledAction = $this->input->getScheduledAction();
        if ($scheduledAction === null) {
            $scheduledAction = $this->activeRecordFactory->getObject('Listing_Product_ScheduledAction');
        }

        $scheduledAction->addData(
            [
                'listing_product_id' => $this->input->getListingProduct()->getId(),
                'component' => \Ess\M2ePro\Helper\Component\Walmart::NICK,
                'action_type' => \Ess\M2ePro\Model\Listing\Product::ACTION_LIST,
                'additional_data' => \Ess\M2ePro\Helper\Json::encode(['params' => $params]),
            ]
        );

        if ($scheduledAction->getId()) {
            $this->getScheduledActionManager()->updateAction($scheduledAction);
        } else {
            $this->getScheduledActionManager()->addAction($scheduledAction);
        }
    }

    public function isMeetListRequirements()
    {
        $listingProduct = $this->input->getListingProduct();

        /** @var \Ess\M2ePro\Model\Walmart\Listing\Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();

        $walmartSynchronizationTemplate = $walmartListingProduct->getWalmartSynchronizationTemplate();

        if (!$walmartSynchronizationTemplate->isListMode()) {
            return false;
        }

        /** @var \Ess\M2ePro\Model\ResourceModel\Listing\Product\Variation $variationResource */
        $variationResource = $this->activeRecordFactory->getObject('Listing_Product_Variation')->getResource();

        $additionalData = $listingProduct->getAdditionalData();

        if ($walmartSynchronizationTemplate->isListStatusEnabled()) {
            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                $note = $this->getHelper('Module\Log')->encodeDescription(
                    'Product was not Listed as it has Disabled Status in Magento. The Product Status condition
                     in the List Rules was not met.'
                );
                $additionalData['synch_template_list_rules_note'] = $note;

                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } elseif (
                $variationManager->isPhysicalUnit() &&
                $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    $note = $this->getHelper('Module\Log')->encodeDescription(
                        'Product was not Listed as this Product Variation has Disabled Status in Magento.
                         The Product Status condition in the List Rules was not met.'
                    );
                    $additionalData['synch_template_list_rules_note'] = $note;

                    $listingProduct->setSettings('additional_data', $additionalData)->save();

                    return false;
                }
            }
        }

        if ($walmartSynchronizationTemplate->isListIsInStock()) {
            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                $note = $this->getHelper('Module\Log')->encodeDescription(
                    'Product was not Listed as it is Out of Stock in Magento. The Stock Availability condition in
                     the List Rules was not met.'
                );
                $additionalData['synch_template_list_rules_note'] = $note;

                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            } elseif (
                $variationManager->isPhysicalUnit() &&
                $variationManager->getTypeModel()->isVariationProductMatched()
            ) {
                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    $note = $this->getHelper('Module\Log')->encodeDescription(
                        'Product was not Listed as this Product Variation is Out of Stock in Magento. The Stock
                         Availability condition in the List Rules was not met.'
                    );
                    $additionalData['synch_template_list_rules_note'] = $note;

                    $listingProduct->setSettings('additional_data', $additionalData)->save();

                    return false;
                }
            }
        }

        if (
            $walmartSynchronizationTemplate->isListWhenQtyCalculatedHasValue() &&
            !$variationManager->isRelationParentType()
        ) {
            $result = false;
            $productQty = (int)$walmartListingProduct->getQty(false);
            $minQty = (int)$walmartSynchronizationTemplate->getListWhenQtyCalculatedHasValue();

            $note = '';

            if ($productQty >= $minQty) {
                $result = true;
            } else {
                $note = $this->getHelper('Module\Log')->encodeDescription(
                    'Product was not Listed as its Quantity is %product_qty% in Magento. The Calculated
                     Quantity condition in the List Rules was not met.',
                    ['!product_qty' => $productQty]
                );
            }

            if (!$result) {
                if (!empty($note)) {
                    $additionalData['synch_template_list_rules_note'] = $note;
                    $listingProduct->setSettings('additional_data', $additionalData)->save();
                }

                return false;
            }
        }

        if ($walmartSynchronizationTemplate->isListAdvancedRulesEnabled()) {
            $ruleModel = $this->activeRecordFactory->getObject('Magento_Product_Rule')->setData(
                [
                    'store_id' => $listingProduct->getListing()->getStoreId(),
                    'prefix' => \Ess\M2ePro\Model\Walmart\Template\Synchronization::LIST_ADVANCED_RULES_PREFIX,
                ]
            );
            $ruleModel->loadFromSerialized($walmartSynchronizationTemplate->getListAdvancedRulesFilters());

            if (!$ruleModel->validate($listingProduct->getMagentoProduct()->getProduct())) {
                $note = $this->getHelper('Module\Log')->encodeDescription(
                    'Product was not Listed. Advanced Conditions in the List Rules were not met.'
                );

                $additionalData['synch_template_list_rules_note'] = $note;
                $listingProduct->setSettings('additional_data', $additionalData)->save();

                return false;
            }
        }

        return true;
    }
}
