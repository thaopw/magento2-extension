<?php

namespace Ess\M2ePro\Model\Ebay\Magento\Product;

class ChangeProcessor extends \Ess\M2ePro\Model\Magento\Product\ChangeProcessor\AbstractModel
{
    public const INSTRUCTION_TYPE_TITLE_DATA_CHANGED = 'magento_product_title_data_changed';
    public const INSTRUCTION_TYPE_SUBTITLE_DATA_CHANGED = 'magento_product_subtitle_data_changed';
    public const INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED = 'magento_product_description_data_changed';
    public const INSTRUCTION_TYPE_IMAGES_DATA_CHANGED = 'magento_product_images_data_changed';
    public const INSTRUCTION_TYPE_PRODUCT_IDENTIFIERS_DATA_CHANGED = 'magento_product_identifiers_data_changed';
    public const INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED = 'magento_product_categories_data_changed';
    public const INSTRUCTION_TYPE_PARTS_DATA_CHANGED = 'magento_product_parts_data_changed';
    public const INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED = 'magento_product_shipping_data_changed';
    public const INSTRUCTION_TYPE_OTHER_DATA_CHANGED = 'magento_product_other_data_changed';

    /** @var \Ess\M2ePro\Helper\Component\Ebay\Configuration */
    private $ebayConfiguration;

    public function __construct(
        \Ess\M2ePro\Helper\Component\Ebay\Configuration $ebayConfiguration,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        array $data = []
    ) {
        parent::__construct($helperFactory, $modelFactory, $activeRecordFactory, $data);

        $this->ebayConfiguration = $ebayConfiguration;
    }

    public function getTrackingAttributes()
    {
        return array_unique(
            array_merge(
                $this->getTitleTrackingAttributes(),
                $this->getSubtitleTrackingAttributes(),
                $this->getDescriptionTrackingAttributes(),
                $this->getProductIdentifiersTrackingAttributes(),
                $this->getImagesTrackingAttributes(),
                $this->getConditionDescriptorAttributes(),
                $this->getCategoriesTrackingAttributes(),
                $this->getShippingTrackingAttributes(),
                $this->getOtherTrackingAttributes()
            )
        );
    }

    public function getInstructionsDataByAttributes(array $attributes)
    {
        if (empty($attributes)) {
            return [];
        }

        $data = [];

        if (array_intersect($attributes, $this->getTitleTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_TITLE_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        if (array_intersect($attributes, $this->getSubtitleTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_SUBTITLE_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        if (
            array_intersect($attributes, $this->getDescriptionTrackingAttributes())
            || array_intersect($attributes, $this->getConditionDescriptorAttributes())
        ) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        if (array_intersect($attributes, $this->getImagesTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        if (array_intersect($attributes, $this->getProductIdentifiersTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_PRODUCT_IDENTIFIERS_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        if (array_intersect($attributes, $this->getCategoriesTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        if (array_intersect($attributes, $this->getShippingTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        if (array_intersect($attributes, $this->getOtherTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_OTHER_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        return $data;
    }

    public function getTitleTrackingAttributes()
    {
        $ebayDescriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();

        return array_unique($ebayDescriptionTemplate->getTitleAttributes());
    }

    public function getSubtitleTrackingAttributes()
    {
        $ebayDescriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();

        return array_unique($ebayDescriptionTemplate->getSubTitleAttributes());
    }

    public function getDescriptionTrackingAttributes()
    {
        $ebayDescriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();

        return array_unique($ebayDescriptionTemplate->getDescriptionAttributes());
    }

    public function getImagesTrackingAttributes()
    {
        $ebayDescriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();

        $trackingAttributes = array_merge(
            $ebayDescriptionTemplate->getImageMainAttributes(),
            $ebayDescriptionTemplate->getGalleryImagesAttributes(),
            $ebayDescriptionTemplate->getVariationImagesAttributes()
        );

        return array_unique($trackingAttributes);
    }

    public function getConditionDescriptorAttributes(): array
    {
        $attributes = [];
        $template = $this->getEbayListingProduct()
                         ->getEbayDescriptionTemplate();

        if ($template->isConditionProfessionalGraderIdModeAttribute()) {
            $attributes[] = $template->getConditionProfessionalGraderIdAttribute();
        }

        if ($template->isConditionGradeIdModeAttribute()) {
            $attributes[] = $template->getConditionGradeIdAttribute();
        }

        if ($template->isConditionGradeCertificationModeAttribute()) {
            $attributes[] = $template->getConditionGradeCertificationAttribute();
        }

        if ($template->isConditionGradeCardConditionModeAttribute()) {
            $attributes[] = $template->getConditionGradeCardConditionIdAttribute();
        }

        return array_unique($attributes);
    }

    private function getProductIdentifiersTrackingAttributes(): array
    {
        if (
            !$this->getEbayListingProduct()
                  ->getEbaySynchronizationTemplate()
                  ->isReviseProductIdentifiersEnabled()
        ) {
            return [];
        }

        $attributes = [
            $this->ebayConfiguration->getUpcCustomAttribute(),
            $this->ebayConfiguration->getEanCustomAttribute(),
            $this->ebayConfiguration->getIsbnCustomAttribute(),
            $this->ebayConfiguration->getEpidCustomAttribute(),
        ];
        $attributes = array_filter($attributes);

        return array_unique($attributes);
    }

    public function getCategoriesTrackingAttributes()
    {
        if (!$this->getEbayListingProduct()->isSetCategoryTemplate()) {
            return [];
        }

        $categoryTemplate = $this->getEbayListingProduct()->getCategoryTemplate();

        return array_unique($categoryTemplate->getCategoryAttributes());
    }

    public function getShippingTrackingAttributes()
    {
        $shippingTemplate = $this->getEbayListingProduct()->getShippingTemplate();

        $attributes = array_merge(
            $shippingTemplate->getCountryAttributes(),
            $shippingTemplate->getAddressAttributes(),
            $shippingTemplate->getPostalCodeAttributes(),
            $shippingTemplate->getDispatchTimeAttributes()
        );

        $calculatedShippingObject = $shippingTemplate->getCalculatedShipping();
        if ($calculatedShippingObject !== null) {
            $attributes = array_merge(
                $attributes,
                array_merge(
                    $calculatedShippingObject->getPackageSizeAttributes(),
                    $calculatedShippingObject->getDimensionAttributes(),
                    $calculatedShippingObject->getWeightAttributes()
                )
            );
        }

        /** @var \Ess\M2ePro\Model\Ebay\Template\Shipping\Service[] $services */
        $services = $shippingTemplate->getServices(true);
        foreach ($services as $service) {
            // @codingStandardsIgnoreStart
            $attributes = array_merge(
                $attributes,
                array_merge(
                    $service->getCostAttributes(),
                    $service->getCostAdditionalAttributes()
                )
            );
            // @codingStandardsIgnoreEnd
        }

        return array_unique($attributes);
    }

    public function getOtherTrackingAttributes()
    {
        $trackingAttributes = [];

        $ebaySellingFormatTemplate = $this->getEbayListingProduct()->getEbaySellingFormatTemplate();
        $trackingAttributes = array_merge(
            $trackingAttributes,
            $ebaySellingFormatTemplate->getBestOfferAcceptAttributes(),
            $ebaySellingFormatTemplate->getBestOfferRejectAttributes(),
            $ebaySellingFormatTemplate->getTaxCategoryAttributes(),
            $ebaySellingFormatTemplate->getLotSizeAttributes(),
            $ebaySellingFormatTemplate->getPriceDiscountMapAttributes()
        );

        $ebayDescriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();
        $trackingAttributes = array_merge(
            $trackingAttributes,
            $ebayDescriptionTemplate->getConditionAttributes(),
            $ebayDescriptionTemplate->getConditionNoteAttributes()
        );

        return array_unique($trackingAttributes);
    }

    private function getEbayListingProduct(): \Ess\M2ePro\Model\Ebay\Listing\Product
    {
        return $this->getListingProduct()->getChildObject();
    }
}
