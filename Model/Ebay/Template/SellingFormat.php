<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method \Ess\M2ePro\Model\Template\SellingFormat getParentObject()
 * @method \Ess\M2ePro\Model\ResourceModel\Ebay\Template\SellingFormat getResource()
 */

namespace Ess\M2ePro\Model\Ebay\Template;

class SellingFormat extends \Ess\M2ePro\Model\ActiveRecord\Component\Child\Ebay\AbstractModel
{
    public const LISTING_TYPE_AUCTION = 1;
    public const LISTING_TYPE_FIXED = 2;
    public const LISTING_TYPE_ATTRIBUTE = 3;

    public const LISTING_IS_PRIVATE_NO = 0;
    public const LISTING_IS_PRIVATE_YES = 1;

    public const DURATION_TYPE_EBAY = 1;
    public const DURATION_TYPE_ATTRIBUTE = 2;

    public const QTY_MODIFICATION_MODE_OFF = 0;
    public const QTY_MODIFICATION_MODE_ON = 1;

    public const VAT_MODE_NO = 0;
    public const VAT_MODE_INCLUDING_IN_PRICE = 1;
    public const VAT_MODE_ON_TOP_OF_PRICE = 2;

    public const QTY_MIN_POSTED_DEFAULT_VALUE = 1;
    public const QTY_MAX_POSTED_DEFAULT_VALUE = 100;

    public const TAX_CATEGORY_MODE_NONE = 0;
    public const TAX_CATEGORY_MODE_VALUE = 1;
    public const TAX_CATEGORY_MODE_ATTRIBUTE = 2;

    public const PRICE_COEFFICIENT_NONE = 0;
    public const PRICE_COEFFICIENT_ABSOLUTE_INCREASE = 1;
    public const PRICE_COEFFICIENT_ABSOLUTE_DECREASE = 2;
    public const PRICE_COEFFICIENT_PERCENTAGE_INCREASE = 3;
    public const PRICE_COEFFICIENT_PERCENTAGE_DECREASE = 4;

    public const PRICE_VARIATION_MODE_PARENT = 1;
    public const PRICE_VARIATION_MODE_CHILDREN = 2;

    public const PRICE_DISCOUNT_STP_TYPE_RRP = 0;
    public const PRICE_DISCOUNT_STP_TYPE_SOLD_ON_EBAY = 1;
    public const PRICE_DISCOUNT_STP_TYPE_SOLD_OFF_EBAY = 2;
    public const PRICE_DISCOUNT_STP_TYPE_SOLD_ON_BOTH = 3;

    public const PRICE_DISCOUNT_MAP_EXPOSURE_NONE = 0;
    public const PRICE_DISCOUNT_MAP_EXPOSURE_DURING_CHECKOUT = 1;
    public const PRICE_DISCOUNT_MAP_EXPOSURE_PRE_CHECKOUT = 2;

    public const BEST_OFFER_MODE_NO = 0;
    public const BEST_OFFER_MODE_YES = 1;

    public const BEST_OFFER_ACCEPT_MODE_NO = 0;
    public const BEST_OFFER_ACCEPT_MODE_PERCENTAGE = 1;
    public const BEST_OFFER_ACCEPT_MODE_ATTRIBUTE = 2;

    public const BEST_OFFER_REJECT_MODE_NO = 0;
    public const BEST_OFFER_REJECT_MODE_PERCENTAGE = 1;
    public const BEST_OFFER_REJECT_MODE_ATTRIBUTE = 2;

    public const RESTRICTED_TO_BUSINESS_DISABLED = 0;
    public const RESTRICTED_TO_BUSINESS_ENABLED = 1;

    public const LOT_SIZE_MODE_DISABLED = 0;
    public const LOT_SIZE_MODE_CUSTOM_VALUE = 1;
    public const LOT_SIZE_MODE_ATTRIBUTE = 2;

    public const PRICE_TYPE_FIXED = 'fixed_price';
    public const PRICE_TYPE_START = 'start_price';
    public const PRICE_TYPE_RESERVE = 'reserve_price';
    public const PRICE_TYPE_BUYITNOW = 'buyitnow_price';

    /**
     * @var \Ess\M2ePro\Model\Ebay\Template\SellingFormat\Source[]
     */
    private $sellingSourceModels = [];
    /** @var \Ess\M2ePro\Helper\Module\Configuration */
    private $moduleConfiguration;

    public function __construct(
        \Ess\M2ePro\Helper\Module\Configuration $moduleConfiguration,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Factory $parentFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $parentFactory,
            $modelFactory,
            $activeRecordFactory,
            $helperFactory,
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->moduleConfiguration = $moduleConfiguration;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Ess\M2ePro\Model\ResourceModel\Ebay\Template\SellingFormat::class);
    }

    //########################################

    /**
     * @return string
     */
    public function getNick()
    {
        return \Ess\M2ePro\Model\Ebay\Template\Manager::TEMPLATE_SELLING_FORMAT;
    }

    /**
     * @return bool
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)$this->activeRecordFactory->getObject('Ebay\Listing')
                                               ->getCollection()
                                               ->addFieldToFilter('template_selling_format_id', $this->getId())
                                               ->getSize() ||
            (bool)$this->activeRecordFactory->getObject('Ebay_Listing_Product')
                                            ->getCollection()
                                            ->addFieldToFilter(
                                                'template_selling_format_mode',
                                                \Ess\M2ePro\Model\Ebay\Template\Manager::MODE_TEMPLATE
                                            )
                                            ->addFieldToFilter('template_selling_format_id', $this->getId())
                                            ->getSize();
    }

    //########################################

    public function save()
    {
        $this->getHelper('Data_Cache_Permanent')->removeTagValues('template_sellingformat');

        return parent::save();
    }

    //########################################

    public function delete()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->sellingSourceModels = [];

        $this->getHelper('Data_Cache_Permanent')->removeTagValues('template_sellingformat');

        return parent::delete();
    }

    //########################################

    /**
     * @param \Ess\M2ePro\Model\Magento\Product $magentoProduct
     *
     * @return \Ess\M2ePro\Model\Ebay\Template\SellingFormat\Source
     */
    public function getSource(\Ess\M2ePro\Model\Magento\Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->sellingSourceModels[$productId])) {
            return $this->sellingSourceModels[$productId];
        }

        $this->sellingSourceModels[$productId] = $this->modelFactory->getObject('Ebay_Template_SellingFormat_Source');
        $this->sellingSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->sellingSourceModels[$productId]->setSellingFormatTemplate($this->getParentObject());

        return $this->sellingSourceModels[$productId];
    }

    //########################################

    /**
     * @return int
     */
    public function getListingType()
    {
        return (int)$this->getData('listing_type');
    }

    /**
     * @return bool
     */
    public function isListingTypeFixed()
    {
        return $this->getListingType() == self::LISTING_TYPE_FIXED;
    }

    /**
     * @return bool
     */
    public function isListingTypeAuction()
    {
        return $this->getListingType() == self::LISTING_TYPE_AUCTION;
    }

    /**
     * @return bool
     */
    public function isListingTypeAttribute()
    {
        return $this->getListingType() == self::LISTING_TYPE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getListingTypeSource()
    {
        return [
            'mode' => $this->getListingType(),
            'attribute' => $this->getData('listing_type_attribute'),
        ];
    }

    /**
     * @return array
     */
    public function getListingTypeAttributes()
    {
        $attributes = [];
        $src = $this->getListingTypeSource();

        if ($src['mode'] == self::LISTING_TYPE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getDurationMode()
    {
        return (int)$this->getData('duration_mode');
    }

    /**
     * @return array
     */
    public function getDurationSource()
    {
        $tempSrc = $this->getListingTypeSource();

        $mode = self::DURATION_TYPE_EBAY;
        if ($tempSrc['mode'] == self::LISTING_TYPE_ATTRIBUTE) {
            $mode = self::DURATION_TYPE_ATTRIBUTE;
        }

        return [
            'mode' => (int)$mode,
            'value' => (int)$this->getDurationMode(),
            'attribute' => $this->getData('duration_attribute'),
        ];
    }

    /**
     * @return array
     */
    public function getDurationAttributes()
    {
        $attributes = [];
        $src = $this->getDurationSource();

        if ($src['mode'] == self::DURATION_TYPE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isPrivateListing()
    {
        return (bool)$this->getData('listing_is_private');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getQtyMode()
    {
        return (int)$this->getData('qty_mode');
    }

    /**
     * @return bool
     */
    public function isQtyModeProduct()
    {
        return $this->getQtyMode() == \Ess\M2ePro\Model\Template\SellingFormat::QTY_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isQtyModeNumber()
    {
        return $this->getQtyMode() == \Ess\M2ePro\Model\Template\SellingFormat::QTY_MODE_NUMBER;
    }

    /**
     * @return bool
     */
    public function isQtyModeAttribute()
    {
        return $this->getQtyMode() == \Ess\M2ePro\Model\Template\SellingFormat::QTY_MODE_ATTRIBUTE;
    }

    /**
     * @return bool
     */
    public function isQtyModeProductFixed()
    {
        return $this->getQtyMode() == \Ess\M2ePro\Model\Template\SellingFormat::QTY_MODE_PRODUCT_FIXED;
    }

    /**
     * @return int
     */
    public function getQtyNumber()
    {
        return (int)$this->getData('qty_custom_value');
    }

    /**
     * @return array
     */
    public function getQtySource()
    {
        return [
            'mode' => $this->getQtyMode(),
            'value' => $this->getQtyNumber(),
            'attribute' => $this->getData('qty_custom_attribute'),
            'qty_modification_mode' => $this->getQtyModificationMode(),
            'qty_min_posted_value' => $this->getQtyMinPostedValue(),
            'qty_max_posted_value' => $this->getQtyMaxPostedValue(),
            'qty_percentage' => $this->getQtyPercentage(),
        ];
    }

    /**
     * @return array
     */
    public function getQtyAttributes()
    {
        $attributes = [];
        $src = $this->getQtySource();

        if ($src['mode'] == \Ess\M2ePro\Model\Template\SellingFormat::QTY_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getQtyPercentage()
    {
        return (int)$this->getData('qty_percentage');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getQtyModificationMode()
    {
        return (int)$this->getData('qty_modification_mode');
    }

    /**
     * @return bool
     */
    public function isQtyModificationModeOn()
    {
        return $this->getQtyModificationMode() == self::QTY_MODIFICATION_MODE_ON;
    }

    /**
     * @return bool
     */
    public function isQtyModificationModeOff()
    {
        return $this->getQtyModificationMode() == self::QTY_MODIFICATION_MODE_OFF;
    }

    /**
     * @return int
     */
    public function getQtyMinPostedValue()
    {
        return (int)$this->getData('qty_min_posted_value');
    }

    /**
     * @return int
     */
    public function getQtyMaxPostedValue()
    {
        return (int)$this->getData('qty_max_posted_value');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getLotSizeMode()
    {
        return (int)$this->getData('lot_size_mode');
    }

    /**
     * @return bool
     */
    public function isLotSizeDisabled()
    {
        return $this->getLotSizeMode() == self::LOT_SIZE_MODE_DISABLED;
    }

    /**
     * @return bool
     */
    public function isLotSizeCustom()
    {
        return $this->getLotSizeMode() == self::LOT_SIZE_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isLotSizeAttribute()
    {
        return $this->getLotSizeMode() == self::LOT_SIZE_MODE_ATTRIBUTE;
    }

    /**
     * @return int
     */
    public function getLotSizeCustomValue()
    {
        return (int)$this->getData('lot_size_custom_value');
    }

    /**
     * @return array
     */
    public function getLotSizeSource()
    {
        return [
            'mode' => $this->getLotSizeMode(),
            'value' => $this->getLotSizeCustomValue(),
            'attribute' => $this->getData('lot_size_attribute'),
        ];
    }

    /**
     * @return array
     */
    public function getLotSizeAttributes()
    {
        $attributes = [];
        $src = $this->getLotSizeSource();

        if ($src['mode'] == self::LOT_SIZE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //----------------------------------------

    public function getVatMode(): int
    {
        return (int)$this->getData('vat_mode');
    }

    public function isVatModeEnabled(): bool
    {
        return $this->getVatMode() != self::VAT_MODE_NO;
    }

    public function isVatModeOnTopOfPrice(): bool
    {
        return $this->getVatMode() == self::VAT_MODE_ON_TOP_OF_PRICE;
    }

    /**
     * @return float
     */
    public function getVatPercent()
    {
        return (float)$this->getData('vat_percent');
    }

    /**
     * @return bool
     */
    public function isTaxTableEnabled()
    {
        return (bool)$this->getData('tax_table_mode');
    }

    /**
     * @return array
     */
    public function getTaxCategorySource()
    {
        return [
            'mode' => $this->getData('tax_category_mode'),
            'value' => $this->getData('tax_category_value'),
            'attribute' => $this->getData('tax_category_attribute'),
        ];
    }

    /**
     * @return array
     */
    public function getTaxCategoryAttributes()
    {
        $attributes = [];
        $src = $this->getTaxCategorySource();

        if ($src['mode'] == self::TAX_CATEGORY_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    /**
     * @return bool
     */
    public function isRestrictedToBusinessEnabled()
    {
        return (bool)$this->getData('restricted_to_business');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getPriceVariationMode()
    {
        return (int)$this->getData('price_variation_mode');
    }

    /**
     * @return bool
     */
    public function isPriceVariationModeParent()
    {
        return $this->getPriceVariationMode() == self::PRICE_VARIATION_MODE_PARENT;
    }

    /**
     * @return bool
     */
    public function isPriceVariationModeChildren()
    {
        return $this->getPriceVariationMode() == self::PRICE_VARIATION_MODE_CHILDREN;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getFixedPriceMode()
    {
        return (int)$this->getData('fixed_price_mode');
    }

    /**
     * @return bool
     */
    public function isFixedPriceModeNone()
    {
        return $this->getFixedPriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isFixedPriceModeProduct()
    {
        return $this->getFixedPriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isFixedPriceModeSpecial()
    {
        return $this->getFixedPriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isFixedPriceModeAttribute()
    {
        return $this->getFixedPriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getFixedPriceModifier()
    {
        if (empty($this->getData('fixed_price_modifier'))) {
            return [];
        }

        return \Ess\M2ePro\Helper\Json::decode($this->getData('fixed_price_modifier')) ?: [];
    }

    public function getFixedPriceRoundingOption(): int
    {
        return (int)$this->getData('fixed_price_rounding_option');
    }

    /**
     * @return array
     */
    public function getFixedPriceSource()
    {
        return [
            'mode' => $this->getFixedPriceMode(),
            'modifier' => $this->getFixedPriceModifier(),
            'attribute' => $this->getData('fixed_price_custom_attribute'),
        ];
    }

    /**
     * @return array
     */
    public function getFixedPriceAttributes()
    {
        $attributes = [];
        $src = $this->getFixedPriceSource();

        if ($src['mode'] == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getStartPriceMode()
    {
        return (int)$this->getData('start_price_mode');
    }

    /**
     * @return bool
     */
    public function isStartPriceModeNone()
    {
        return $this->getStartPriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isStartPriceModeProduct()
    {
        return $this->getStartPriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isStartPriceModeSpecial()
    {
        return $this->getStartPriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isStartPriceModeAttribute()
    {
        return $this->getStartPriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    public function getStartPriceCoefficient()
    {
        return $this->getData('start_price_coefficient');
    }

    /**
     * @return array
     */
    public function getStartPriceSource()
    {
        return [
            'mode' => $this->getStartPriceMode(),
            'coefficient' => $this->getStartPriceCoefficient(),
            'attribute' => $this->getData('start_price_custom_attribute'),
        ];
    }

    /**
     * @return array
     */
    public function getStartPriceAttributes()
    {
        $attributes = [];
        $src = $this->getStartPriceSource();

        if ($src['mode'] == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    public function getStartPriceRoundingOption(): int
    {
        return (int)$this->getData('start_price_rounding_option');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getReservePriceMode()
    {
        return (int)$this->getData('reserve_price_mode');
    }

    /**
     * @return bool
     */
    public function isReservePriceModeNone()
    {
        return $this->getReservePriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isReservePriceModeProduct()
    {
        return $this->getReservePriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isReservePriceModeSpecial()
    {
        return $this->getReservePriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isReservePriceModeAttribute()
    {
        return $this->getReservePriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    public function getReservePriceCoefficient()
    {
        return $this->getData('reserve_price_coefficient');
    }

    /**
     * @return array
     */
    public function getReservePriceSource()
    {
        return [
            'mode' => $this->getReservePriceMode(),
            'coefficient' => $this->getReservePriceCoefficient(),
            'attribute' => $this->getData('reserve_price_custom_attribute'),
        ];
    }

    /**
     * @return array
     */
    public function getReservePriceAttributes()
    {
        $attributes = [];
        $src = $this->getReservePriceSource();

        if ($src['mode'] == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    public function getReservePriceRoundingOption(): int
    {
        return (int)$this->getData('reserve_price_rounding_option');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getBuyItNowPriceMode()
    {
        return (int)$this->getData('buyitnow_price_mode');
    }

    /**
     * @return bool
     */
    public function isBuyItNowPriceModeNone()
    {
        return $this->getBuyItNowPriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isBuyItNowPriceModeProduct()
    {
        return $this->getBuyItNowPriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isBuyItNowPriceModeSpecial()
    {
        return $this->getBuyItNowPriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isBuyItNowPriceModeAttribute()
    {
        return $this->getBuyItNowPriceMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    public function getBuyItNowPriceCoefficient()
    {
        return $this->getData('buyitnow_price_coefficient');
    }

    /**
     * @return array
     */
    public function getBuyItNowPriceSource()
    {
        return [
            'mode' => $this->getBuyItNowPriceMode(),
            'coefficient' => $this->getBuyItNowPriceCoefficient(),
            'attribute' => $this->getData('buyitnow_price_custom_attribute'),
        ];
    }

    /**
     * @return array
     */
    public function getBuyItNowPriceAttributes()
    {
        $attributes = [];
        $src = $this->getBuyItNowPriceSource();

        if ($src['mode'] == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    public function getBuyItNowPriceRoundingOption(): int
    {
        return (int)$this->getData('buyitnow_price_rounding_option');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getPriceDiscountStpMode()
    {
        return (int)$this->getData('price_discount_stp_mode');
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpModeNone()
    {
        return $this->getPriceDiscountStpMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpModeProduct()
    {
        return $this->getPriceDiscountStpMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpModeSpecial()
    {
        return $this->getPriceDiscountStpMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpModeAttribute()
    {
        return $this->getPriceDiscountStpMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getPriceDiscountStpSource()
    {
        return [
            'mode' => $this->getPriceDiscountStpMode(),
            'attribute' => $this->getData('price_discount_stp_attribute'),
        ];
    }

    /**
     * @return array
     */
    public function getPriceDiscountStpAttributes()
    {
        $attributes = [];
        $src = $this->getPriceDiscountStpSource();

        if ($src['mode'] == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getPriceDiscountStpType()
    {
        return (int)$this->getData('price_discount_stp_type');
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpTypeRrp()
    {
        return $this->getPriceDiscountStpType() == self::PRICE_DISCOUNT_STP_TYPE_RRP;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpTypeSoldOnEbay()
    {
        return $this->getPriceDiscountStpType() == self::PRICE_DISCOUNT_STP_TYPE_SOLD_ON_EBAY;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpTypeSoldOffEbay()
    {
        return $this->getPriceDiscountStpType() == self::PRICE_DISCOUNT_STP_TYPE_SOLD_OFF_EBAY;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpTypeSoldOnBoth()
    {
        return $this->getPriceDiscountStpType() == self::PRICE_DISCOUNT_STP_TYPE_SOLD_ON_BOTH;
    }

    /**
     * @return array
     */
    public function getPriceDiscountStpAdditionalFlags()
    {
        $soldOnEbayFlag = false;
        $soldOffEbayFlag = false;

        switch ($this->getPriceDiscountStpType()) {
            case self::PRICE_DISCOUNT_STP_TYPE_SOLD_ON_EBAY:
                $soldOnEbayFlag = true;
                break;

            case self::PRICE_DISCOUNT_STP_TYPE_SOLD_OFF_EBAY:
                $soldOffEbayFlag = true;
                break;

            case self::PRICE_DISCOUNT_STP_TYPE_SOLD_ON_BOTH:
                $soldOnEbayFlag = true;
                $soldOffEbayFlag = true;
                break;
        }

        return [
            'sold_on_ebay' => $soldOnEbayFlag,
            'sold_off_ebay' => $soldOffEbayFlag,
        ];
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getPriceDiscountMapMode()
    {
        return (int)$this->getData('price_discount_map_mode');
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapModeNone()
    {
        return $this->getPriceDiscountMapMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapModeProduct()
    {
        return $this->getPriceDiscountMapMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapModeSpecial()
    {
        return $this->getPriceDiscountMapMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapModeAttribute()
    {
        return $this->getPriceDiscountMapMode() == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getPriceDiscountMapSource()
    {
        return [
            'mode' => $this->getPriceDiscountMapMode(),
            'attribute' => $this->getData('price_discount_map_attribute'),
        ];
    }

    /**
     * @return array
     */
    public function getPriceDiscountMapAttributes()
    {
        $attributes = [];
        $src = $this->getPriceDiscountMapSource();

        if ($src['mode'] == \Ess\M2ePro\Model\Template\SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    public function getPriceDiscountMapExposureType(): int
    {
        return (int)$this->getData('price_discount_map_exposure_type');
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapExposureTypeNone()
    {
        return $this->getPriceDiscountMapExposureType() == self::PRICE_DISCOUNT_MAP_EXPOSURE_NONE;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapExposureTypeDuringCheckout()
    {
        return $this->getPriceDiscountMapExposureType() == self::PRICE_DISCOUNT_MAP_EXPOSURE_DURING_CHECKOUT;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapExposureTypePreCheckout()
    {
        return $this->getPriceDiscountMapExposureType() == self::PRICE_DISCOUNT_MAP_EXPOSURE_PRE_CHECKOUT;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function usesConvertiblePrices()
    {
        $isPriceConvertEnabled = $this->moduleConfiguration->isEnableMagentoAttributePriceTypeConvertingMode();

        $attributeHelper = $this->getHelper('Magento\Attribute');

        if ($this->isListingTypeFixed() || $this->isListingTypeAttribute()) {
            if ($this->isFixedPriceModeProduct() || $this->isFixedPriceModeSpecial()) {
                return true;
            }

            if ($this->isPriceDiscountStpModeProduct() || $this->isPriceDiscountStpModeSpecial()) {
                return true;
            }

            if ($this->isPriceDiscountMapModeProduct() || $this->isPriceDiscountMapModeSpecial()) {
                return true;
            }

            if ($isPriceConvertEnabled) {
                if (
                    $this->isFixedPriceModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('fixed_price_custom_attribute'))
                ) {
                    return true;
                }

                if (
                    $this->isPriceDiscountStpModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('price_discount_stp_attribute'))
                ) {
                    return true;
                }

                if (
                    $this->isPriceDiscountMapModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('price_discount_map_attribute'))
                ) {
                    return true;
                }
            }

            if ($this->isListingTypeFixed()) {
                return false;
            }
        }

        if ($this->isStartPriceModeProduct() || $this->isStartPriceModeSpecial()) {
            return true;
        }

        if ($this->isReservePriceModeProduct() || $this->isReservePriceModeSpecial()) {
            return true;
        }

        if ($this->isBuyItNowPriceModeProduct() || $this->isBuyItNowPriceModeSpecial()) {
            return true;
        }

        if ($isPriceConvertEnabled) {
            if (
                $this->isStartPriceModeAttribute() &&
                $attributeHelper->isAttributeInputTypePrice($this->getData('start_price_custom_attribute'))
            ) {
                return true;
            }

            if (
                $this->isReservePriceModeAttribute() &&
                $attributeHelper->isAttributeInputTypePrice($this->getData('reserve_price_custom_attribute'))
            ) {
                return true;
            }

            if (
                $this->isBuyItNowPriceModeAttribute() &&
                $attributeHelper->isAttributeInputTypePrice($this->getData('buyitnow_price_custom_attribute'))
            ) {
                return true;
            }
        }

        if ($this->isBestOfferEnabled()) {
            if ($this->isBestOfferAcceptModePercentage() || $this->isBestOfferRejectModePercentage()) {
                return true;
            }

            if ($isPriceConvertEnabled) {
                if (
                    $this->isBestOfferAcceptModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('best_offer_accept_attribute'))
                ) {
                    return true;
                }

                if (
                    $this->isBestOfferRejectModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('best_offer_reject_attribute'))
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isBestOfferEnabled()
    {
        return (int)$this->getData('best_offer_mode') == self::BEST_OFFER_MODE_YES;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getBestOfferAcceptMode()
    {
        return (int)$this->getData('best_offer_accept_mode');
    }

    /**
     * @return bool
     */
    public function isBestOfferAcceptModeNo()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_NO;
    }

    /**
     * @return bool
     */
    public function isBestOfferAcceptModePercentage()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_PERCENTAGE;
    }

    /**
     * @return bool
     */
    public function isBestOfferAcceptModeAttribute()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE;
    }

    public function getBestOfferAcceptValue()
    {
        return $this->getData('best_offer_accept_value');
    }

    /**
     * @return array
     */
    public function getBestOfferAcceptSource()
    {
        return [
            'mode' => $this->getBestOfferAcceptMode(),
            'value' => $this->getBestOfferAcceptValue(),
            'attribute' => $this->getData('best_offer_accept_attribute'),
        ];
    }

    /**
     * @return array
     */
    public function getBestOfferAcceptAttributes()
    {
        $attributes = [];
        $src = $this->getBestOfferAcceptSource();

        if ($src['mode'] == self::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getBestOfferRejectMode()
    {
        return (int)$this->getData('best_offer_reject_mode');
    }

    /**
     * @return bool
     */
    public function isBestOfferRejectModeNo()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_NO;
    }

    /**
     * @return bool
     */
    public function isBestOfferRejectModePercentage()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_PERCENTAGE;
    }

    /**
     * @return bool
     */
    public function isBestOfferRejectModeAttribute()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_ATTRIBUTE;
    }

    public function getBestOfferRejectValue()
    {
        return $this->getData('best_offer_reject_value');
    }

    /**
     * @return array
     */
    public function getBestOfferRejectSource()
    {
        return [
            'mode' => $this->getBestOfferRejectMode(),
            'value' => $this->getBestOfferRejectValue(),
            'attribute' => $this->getData('best_offer_reject_attribute'),
        ];
    }

    /**
     * @return array
     */
    public function getBestOfferRejectAttributes()
    {
        $attributes = [];
        $src = $this->getBestOfferRejectSource();

        if ($src['mode'] == self::BEST_OFFER_REJECT_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getPayPalImmediatePayment(): int
    {
        return (int)$this->getData('paypal_immediate_payment');
    }

    /**
     * @return bool
     */
    public function isIgnoreVariationsEnabled()
    {
        return (bool)$this->getData('ignore_variations');
    }

    //########################################

    public function isCacheEnabled()
    {
        return true;
    }

    //########################################
}
