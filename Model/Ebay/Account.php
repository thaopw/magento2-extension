<?php

namespace Ess\M2ePro\Model\Ebay;

use Ess\M2ePro\Model\ResourceModel\Ebay\Account as EbayAccountResource;
use Ess\M2ePro\Model\Exception\Logic;

class Account extends \Ess\M2ePro\Model\ActiveRecord\Component\Child\Ebay\AbstractModel
{
    public const MODE_SANDBOX = 0;
    public const MODE_PRODUCTION = 1;

    public const FEEDBACKS_AUTO_RESPONSE_NONE = 0;
    public const FEEDBACKS_AUTO_RESPONSE_CYCLED = 1;
    public const FEEDBACKS_AUTO_RESPONSE_RANDOM = 2;

    public const OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE = 0;
    public const OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT = 1;
    public const OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE = 2;

    public const OTHER_LISTINGS_MAPPING_SKU_MODE_NONE = 0;
    public const OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT = 1;
    public const OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID = 2;
    public const OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE = 3;

    public const OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_NONE = 0;
    public const OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_CUSTOM_ATTRIBUTE = 1;

    public const OTHER_LISTINGS_MAPPING_SKU_DEFAULT_PRIORITY = 1;
    public const OTHER_LISTINGS_MAPPING_TITLE_DEFAULT_PRIORITY = 2;
    public const OTHER_LISTINGS_MAPPING_ITEM_ID_DEFAULT_PRIORITY = 3;

    public const MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT = 0;
    public const MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM = 1;

    public const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE = 0;
    public const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT = 1;

    public const MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO = 'magento';
    public const MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL = 'channel';

    public const MAGENTO_ORDERS_CREATE_CHECKOUT = 2;
    public const MAGENTO_ORDERS_CREATE_CHECKOUT_AND_PAID = 4;

    public const MAGENTO_ORDERS_TAX_MODE_NONE = 0;
    public const MAGENTO_ORDERS_TAX_MODE_CHANNEL = 1;
    public const MAGENTO_ORDERS_TAX_MODE_MAGENTO = 2;
    public const MAGENTO_ORDERS_TAX_MODE_MIXED = 3;

    public const MAGENTO_ORDERS_CUSTOMER_MODE_GUEST = 0;
    public const MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED = 1;
    public const MAGENTO_ORDERS_CUSTOMER_MODE_NEW = 2;

    public const MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT = 0;
    public const MAGENTO_ORDERS_STATUS_MAPPING_MODE_CUSTOM = 1;

    public const MAGENTO_ORDERS_STATUS_MAPPING_NEW = 'pending';
    public const MAGENTO_ORDERS_STATUS_MAPPING_PAID = 'processing';
    public const MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED = 'complete';

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Ess\M2ePro\Model\ResourceModel\Ebay\Account::class);
    }

    // ----------------------------------------

    public function save()
    {
        $this->getHelper('Data_Cache_Permanent')->removeTagValues('account');

        return parent::save();
    }

    // ----------------------------------------

    /**
     * @param bool $asObjects
     * @param array $filters
     *
     * @return array|\Ess\M2ePro\Model\ActiveRecord\AbstractModel[]
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getStoreCategoryTemplates($asObjects = false, array $filters = [])
    {
        return $this->getRelatedSimpleItems('Ebay_Template_StoreCategory', 'account_id', $asObjects, $filters);
    }

    /**
     * @param bool $asObjects
     * @param array $filters
     *
     * @return array|\Ess\M2ePro\Model\ActiveRecord\AbstractModel[]
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getFeedbacks($asObjects = false, array $filters = [])
    {
        return $this->getRelatedSimpleItems('Ebay\Feedback', 'account_id', $asObjects, $filters);
    }

    /**
     * @param bool $asObjects
     * @param array $filters
     *
     * @return array|\Ess\M2ePro\Model\ActiveRecord\AbstractModel[]
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getFeedbackTemplates($asObjects = false, array $filters = [])
    {
        return $this->getRelatedSimpleItems('Ebay_Feedback_Template', 'account_id', $asObjects, $filters);
    }

    /**
     * @param bool $asObjects
     * @param array $filters
     *
     * @return array|\Ess\M2ePro\Model\ActiveRecord\AbstractModel[]
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getEbayItems($asObjects = false, array $filters = [])
    {
        return $this->getRelatedSimpleItems('Ebay\Item', 'account_id', $asObjects, $filters);
    }

    // ----------------------------------------

    /**
     * @return bool
     */
    public function hasFeedbackTemplate()
    {
        return (bool)$this->activeRecordFactory->getObject('Ebay_Feedback_Template')->getCollection()
                                               ->addFieldToFilter('account_id', $this->getId())
                                               ->getSize();
    }

    // ----------------------------------------

    /**
     * @return int
     */
    public function getMode()
    {
        return (int)$this->getData('mode');
    }

    public function getServerHash()
    {
        return $this->getData('server_hash');
    }

    public function getUserId()
    {
        return $this->getData('user_id');
    }

    /**
     * @return bool
     */
    public function isModeProduction()
    {
        return $this->getMode() == self::MODE_PRODUCTION;
    }

    /**
     * @return bool
     */
    public function isModeSandbox()
    {
        return $this->getMode() == self::MODE_SANDBOX;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getFeedbacksReceive()
    {
        return (int)$this->getData('feedbacks_receive');
    }

    /**
     * @return bool
     */
    public function isFeedbacksReceive()
    {
        return $this->getFeedbacksReceive() == 1;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getFeedbacksAutoResponse()
    {
        return (int)$this->getData('feedbacks_auto_response');
    }

    /**
     * @return bool
     */
    public function isFeedbacksAutoResponseDisabled()
    {
        return $this->getFeedbacksAutoResponse() == self::FEEDBACKS_AUTO_RESPONSE_NONE;
    }

    /**
     * @return bool
     */
    public function isFeedbacksAutoResponseCycled()
    {
        return $this->getFeedbacksAutoResponse() == self::FEEDBACKS_AUTO_RESPONSE_CYCLED;
    }

    /**
     * @return bool
     */
    public function isFeedbacksAutoResponseRandom()
    {
        return $this->getFeedbacksAutoResponse() == self::FEEDBACKS_AUTO_RESPONSE_RANDOM;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getFeedbacksAutoResponseOnlyPositive()
    {
        return (int)$this->getData('feedbacks_auto_response_only_positive');
    }

    /**
     * @return bool
     */
    public function isFeedbacksAutoResponseOnlyPositive()
    {
        return $this->getFeedbacksAutoResponseOnlyPositive() == 1;
    }

    // ----------------------------------------

    /**
     * @return int
     */
    public function getOtherListingsSynchronization()
    {
        return (int)$this->getData('other_listings_synchronization');
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingMode()
    {
        return (int)$this->getData('other_listings_mapping_mode');
    }

    /**
     * @return array
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getOtherListingsMappingSettings()
    {
        return $this->getSettings('other_listings_mapping_settings');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOtherListingsMappingSkuMode()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['sku', 'mode'],
            self::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE
        );

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingSkuPriority()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['sku', 'priority'],
            self::OTHER_LISTINGS_MAPPING_SKU_DEFAULT_PRIORITY
        );

        return (int)$setting;
    }

    public function getOtherListingsMappingSkuAttribute()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['sku', 'attribute']
        );

        return $setting;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOtherListingsMappingTitleMode()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['title', 'mode'],
            self::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE
        );

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingTitlePriority()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['title', 'priority'],
            self::OTHER_LISTINGS_MAPPING_TITLE_DEFAULT_PRIORITY
        );

        return (int)$setting;
    }

    public function getOtherListingsMappingTitleAttribute()
    {
        $setting = $this->getSetting('other_listings_mapping_settings', ['title', 'attribute']);

        return $setting;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOtherListingsMappingItemIdMode()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['item_id', 'mode'],
            self::OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_NONE
        );

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingItemIdPriority()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['item_id', 'priority'],
            self::OTHER_LISTINGS_MAPPING_ITEM_ID_DEFAULT_PRIORITY
        );

        return (int)$setting;
    }

    public function getOtherListingsMappingItemIdAttribute()
    {
        $setting = $this->getSetting('other_listings_mapping_settings', ['item_id', 'attribute']);

        return $setting;
    }

    // ----------------------------------------

    /**
     * @return bool
     */
    public function isOtherListingsSynchronizationEnabled()
    {
        return $this->getOtherListingsSynchronization() == 1;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingEnabled()
    {
        return $this->getOtherListingsMappingMode() == 1;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOtherListingsMappingSkuModeNone()
    {
        return $this->getOtherListingsMappingSkuMode() == self::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingSkuModeDefault()
    {
        return $this->getOtherListingsMappingSkuMode() == self::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingSkuModeCustomAttribute()
    {
        return $this->getOtherListingsMappingSkuMode() == self::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingSkuModeProductId()
    {
        return $this->getOtherListingsMappingSkuMode() == self::OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOtherListingsMappingTitleModeNone()
    {
        return $this->getOtherListingsMappingTitleMode() == self::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingTitleModeDefault()
    {
        return $this->getOtherListingsMappingTitleMode() == self::OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingTitleModeCustomAttribute()
    {
        return $this->getOtherListingsMappingTitleMode() == self::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOtherListingsMappingItemIdModeNone()
    {
        return $this->getOtherListingsMappingItemIdMode() == self::OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingItemIdModeCustomAttribute()
    {
        return $this->getOtherListingsMappingItemIdMode() == self::OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_CUSTOM_ATTRIBUTE;
    }

    // ----------------------------------------

    /**
     * @param int $marketplaceId
     *
     * @return int
     */
    public function getRelatedStoreId($marketplaceId)
    {
        $storeId = $this->getSetting('marketplaces_data', [(int)$marketplaceId, 'related_store_id']);

        return $storeId !== null ? (int)$storeId : \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    // ----------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersListingsModeEnabled()
    {
        return $this->getSetting('magento_orders_settings', ['listing', 'mode'], 1) == 1;
    }

    public function getMagentoOrdersListingsCreateFromDateOrCreateAccountDate(): \DateTime
    {
        $date = $this->getMagentoOrdersListingsCreateFromDate();
        if ($date !== null) {
            return $date;
        }

        /** @var \Ess\M2ePro\Model\Account $parentObject */
        $parentObject = $this->getParentObject();

        return $parentObject->getCreateDate();
    }

    public function getMagentoOrdersListingsCreateFromDate(): ?\DateTime
    {
        $date = $this->getSetting('magento_orders_settings', ['listing', 'create_from_date']);
        if (empty($date)) {
            return null;
        }

        return \Ess\M2ePro\Helper\Date::createDateGmt($date);
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersListingsStoreCustom()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['listing', 'store_mode'],
            self::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT
        );

        return $setting == self::MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM;
    }

    /**
     * @return int
     */
    public function getMagentoOrdersListingsStoreId()
    {
        $setting = $this->getSetting('magento_orders_settings', ['listing', 'store_id'], 0);

        return (int)$setting;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersListingsOtherModeEnabled()
    {
        return $this->getSetting('magento_orders_settings', ['listing_other', 'mode'], 1) == 1;
    }

    public function getMagentoOrdersListingsOtherCreateFromDateOrCreateAccountDate(): \DateTime
    {
        $date = $this->getMagentoOrdersListingsOtherCreateFromDate();
        if ($date !== null) {
            return $date;
        }

        /** @var \Ess\M2ePro\Model\Account $parentObject */
        $parentObject = $this->getParentObject();

        return $parentObject->getCreateDate();
    }

    public function getMagentoOrdersListingsOtherCreateFromDate(): ?\DateTime
    {
        $date = $this->getSetting('magento_orders_settings', ['listing_other', 'create_from_date']);
        if (empty($date)) {
            return null;
        }

        return \Ess\M2ePro\Helper\Date::createDateGmt($date);
    }

    /**
     * @return int
     */
    public function getMagentoOrdersListingsOtherStoreId()
    {
        $setting = $this->getSetting('magento_orders_settings', ['listing_other', 'store_id'], 0);

        return (int)$setting;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersListingsOtherProductImportEnabled()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['listing_other', 'product_mode'],
            self::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT
        );

        return $setting == self::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT;
    }

    /**
     * @return int
     */
    public function getMagentoOrdersListingsOtherProductTaxClassId()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['listing_other', 'product_tax_class_id'],
            \Ess\M2ePro\Model\Magento\Product::TAX_CLASS_ID_NONE
        );

        return (int)$setting;
    }

    // ---------------------------------------

    public function getMagentoOrdersNumberSource()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['number', 'source'],
            self::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO
        );

        return $setting;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersNumberSourceMagento()
    {
        return $this->getMagentoOrdersNumberSource() == self::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersNumberSourceChannel()
    {
        return $this->getMagentoOrdersNumberSource() == self::MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL;
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getMagentoOrdersNumberRegularPrefix()
    {
        $settings = $this->getSetting('magento_orders_settings', ['number', 'prefix']);

        return isset($settings['prefix']) ? $settings['prefix'] : '';
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersNumberMarketplacePrefixUsed()
    {
        $settings = $this->getSetting('magento_orders_settings', ['number', 'prefix']);

        return isset($settings['use_marketplace_prefix']) ? (bool)$settings['use_marketplace_prefix'] : false;
    }

    public function isImportShipByDateToMagentoOrder(): bool
    {
        return (bool)$this->getSetting(
            'magento_orders_settings',
            ['shipping_information', 'ship_by_date'],
            true
        );
    }

    public function isRegionOverrideRequired(): bool
    {
        return (bool)$this->getSetting(
            'magento_orders_settings',
            ['shipping_information', 'shipping_address_region_override'],
            1
        );
    }

    public function isSkipEvtinModeOn(): bool
    {
        return (bool)$this->getData(\Ess\M2ePro\Model\ResourceModel\Ebay\Account::COLUMN_SKIP_EVTIN);
    }

    public function getMagentoOrdersCreationMode()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['creation', 'mode'],
            self::MAGENTO_ORDERS_CREATE_CHECKOUT_AND_PAID
        );

        return $setting;
    }

    /**
     * @return bool
     */
    public function shouldCreateMagentoOrderWhenCheckedOut()
    {
        return $this->getMagentoOrdersCreationMode() == self::MAGENTO_ORDERS_CREATE_CHECKOUT;
    }

    /**
     * @return bool
     */
    public function shouldCreateMagentoOrderWhenCheckedOutAndPaid()
    {
        return $this->getMagentoOrdersCreationMode() == self::MAGENTO_ORDERS_CREATE_CHECKOUT_AND_PAID;
    }

    /**
     * @return int
     */
    public function getQtyReservationDays()
    {
        $setting = $this->getSetting('magento_orders_settings', ['qty_reservation', 'days'], 1);

        return (int)$setting;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeNone()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['tax', 'mode'],
            self::MAGENTO_ORDERS_TAX_MODE_MIXED
        );

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeChannel()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['tax', 'mode'],
            self::MAGENTO_ORDERS_TAX_MODE_MIXED
        );

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_CHANNEL;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeMagento()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['tax', 'mode'],
            self::MAGENTO_ORDERS_TAX_MODE_MIXED
        );

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_MAGENTO;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeMixed()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['tax', 'mode'],
            self::MAGENTO_ORDERS_TAX_MODE_MIXED
        );

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_MIXED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerGuest()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['customer', 'mode'],
            self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST
        );

        return $setting == self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerPredefined()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['customer', 'mode'],
            self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST
        );

        return $setting == self::MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerNew()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['customer', 'mode'],
            self::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST
        );

        return $setting == self::MAGENTO_ORDERS_CUSTOMER_MODE_NEW;
    }

    /**
     * @return int
     */
    public function getMagentoOrdersCustomerId()
    {
        $setting = $this->getSetting('magento_orders_settings', ['customer', 'id']);

        return (int)$setting;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerNewSubscribed()
    {
        return $this->getSetting('magento_orders_settings', ['customer', 'subscription_mode'], 0) == 1;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerNewNotifyWhenCreated()
    {
        $setting = $this->getSetting('magento_orders_settings', ['customer', 'notifications', 'customer_created']);

        return (bool)$setting;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerNewNotifyWhenOrderCreated()
    {
        $setting = $this->getSetting('magento_orders_settings', ['customer', 'notifications', 'order_created']);

        return (bool)$setting;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersCustomerNewNotifyWhenInvoiceCreated()
    {
        $setting = $this->getSetting('magento_orders_settings', ['customer', 'notifications', 'invoice_created']);

        return (bool)$setting;
    }

    /**
     * @return int
     */
    public function getMagentoOrdersCustomerNewWebsiteId()
    {
        $setting = $this->getSetting('magento_orders_settings', ['customer', 'website_id']);

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getMagentoOrdersCustomerNewGroupId()
    {
        $setting = $this->getSetting('magento_orders_settings', ['customer', 'group_id']);

        return (int)$setting;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersStatusMappingDefault()
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['status_mapping', 'mode'],
            self::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT
        );

        return $setting == self::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT;
    }

    // ---------------------------------------

    public function getMagentoOrdersStatusNew()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return self::MAGENTO_ORDERS_STATUS_MAPPING_NEW;
        }

        return $this->getSetting('magento_orders_settings', ['status_mapping', 'new']);
    }

    public function getMagentoOrdersStatusPaid()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return self::MAGENTO_ORDERS_STATUS_MAPPING_PAID;
        }

        return $this->getSetting('magento_orders_settings', ['status_mapping', 'paid']);
    }

    public function getMagentoOrdersStatusShipped()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return self::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED;
        }

        return $this->getSetting('magento_orders_settings', ['status_mapping', 'shipped']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isRefundEnabled()
    {
        $setting = $this->getSetting('magento_orders_settings', ['refund_and_cancellation', 'refund_mode']);

        return (bool)$setting;
    }

    public function isCreateCreditMemoEnabled(): bool
    {
        $setting = $this->getSetting('magento_orders_settings', ['refund_and_cancellation', 'credit_memo']);

        return (bool)$setting;
    }

    public function isAutomaticallyApproveBuyerCancellationRequestedEnabled(): bool
    {
        $setting = $this->getSetting(
            'magento_orders_settings',
            ['refund_and_cancellation', 'approve_buyer_cancellation_requested']
        );

        return (bool)$setting;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersInvoiceEnabled()
    {
        return (bool)$this->getData('create_magento_invoice');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersShipmentEnabled()
    {
        return (bool)$this->getData('create_magento_shipment');
    }

    // ----------------------------------------

    /**
     * @return array
     * @throws Logic
     */
    public function getUserPreferences()
    {
        return $this->getSettings('user_preferences');
    }

    public function updateUserPreferences()
    {
        /** @var \Ess\M2ePro\Model\Ebay\Connector\Dispatcher $dispatcherObject */
        $dispatcherObject = $this->modelFactory->getObject('Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'account',
            'get',
            'userPreferences',
            [],
            null,
            null,
            $this->getId()
        );

        $dispatcherObject->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        if (empty($responseData['user_preferences'])) {
            return;
        }

        $this->setData(
            'user_preferences',
            \Ess\M2ePro\Helper\Json::encode($responseData['user_preferences'])
        )->save();
    }

    // ---------------------------------------

    /**
     * @return bool
     * @throws Logic
     */
    public function getOutOfStockControl()
    {
        $userPreferences = $this->getUserPreferences();

        if (isset($userPreferences['OutOfStockControlPreference'])) {
            return strtolower($userPreferences['OutOfStockControlPreference']) === 'true';
        }

        return false;
    }

    // ----------------------------------------

    public function getRateTables()
    {
        return $this->getSettings('rate_tables');
    }

    public function updateRateTables()
    {
        if (!$this->isTokenExist()) {
            return;
        }

        /** @var \Ess\M2ePro\Model\Ebay\Connector\Dispatcher $dispatcherObject */
        $dispatcherObject = $this->modelFactory->getObject('Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'account',
            'get',
            'shippingRateTables',
            [],
            null,
            null,
            $this->getId()
        );

        $dispatcherObject->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        if (empty($responseData)) {
            return;
        }

        $this->setData('rate_tables', \Ess\M2ePro\Helper\Json::encode($responseData))
             ->save();
    }

    public function isRateTablesExist()
    {
        return !empty($this->getRateTables());
    }

    public function getSellApiTokenExpiredDate()
    {
        return $this->getData('sell_api_token_expired_date');
    }

    //The is_token_exist flag is needed for migration from Trading Api token to Sell Api token
    public function isTokenExist(): bool
    {
        return (bool)$this->getData('is_token_exist');
    }

    public function setIsTokenExist(bool $isTokenExist)
    {
        $this->setData('is_token_exist', $isTokenExist);
    }

    public function getFeedbacksLastUsedId()
    {
        return $this->getData('feedbacks_last_used_id');
    }

    public function getEbayStoreTitle(): string
    {
        return $this->getData(EbayAccountResource::COLUMN_EBAY_STORE_TITLE);
    }

    public function setEbayStoreTitle(string $storeTitle): self
    {
        $this->setData(EbayAccountResource::COLUMN_EBAY_STORE_TITLE, $storeTitle);

        return $this;
    }

    public function getEbayStoreUrl(): string
    {
        return $this->getData(EbayAccountResource::COLUMN_EBAY_STORE_URL);
    }

    public function setEbayStoreUrl(string $storeUrl): self
    {
        $this->setData(EbayAccountResource::COLUMN_EBAY_STORE_URL, $storeUrl);

        return $this;
    }

    public function getEbayStoreSubscriptionLevel(): string
    {
        return $this->getData(EbayAccountResource::COLUMN_EBAY_STORE_SUBSCRIPTION_LEVEL);
    }

    public function setEbayStoreSubscriptionLevel(string $storeSubscriptionLevel): self
    {
        $this->setData(EbayAccountResource::COLUMN_EBAY_STORE_SUBSCRIPTION_LEVEL, $storeSubscriptionLevel);

        return $this;
    }

    public function getEbayStoreDescription(): string
    {
        return $this->getData(EbayAccountResource::COLUMN_EBAY_STORE_DESCRIPTION);
    }

    public function setEbayStoreDescription(string $storeDescription): self
    {
        $this->setData(EbayAccountResource::COLUMN_EBAY_STORE_DESCRIPTION, $storeDescription);

        return $this;
    }

    public function getEbayStoreCategory($id)
    {
        $connection = $this->getResource()->getConnection();

        $tableAccountStoreCategories = $this->getHelper('Module_Database_Structure')
                                            ->getTableNameWithPrefix('m2epro_ebay_account_store_category');

        $dbSelect = $connection->select()
                               ->from($tableAccountStoreCategories, '*')
                               ->where('`account_id` = ?', (int)$this->getId())
                               ->where('`category_id` = ?', (int)$id)
                               ->order(['sorder ASC']);

        $categories = $connection->fetchAll($dbSelect);

        return !empty($categories) ? $categories[0] : [];
    }

    /**
     * @return array
     */
    public function getEbayStoreCategories()
    {
        $tableAccountStoreCategories = $this->getHelper('Module_Database_Structure')
                                            ->getTableNameWithPrefix('m2epro_ebay_account_store_category');

        $connRead = $this->getResource()->getConnection();

        $dbSelect = $connRead->select()
                             ->from($tableAccountStoreCategories, '*')
                             ->where('`account_id` = ?', (int)$this->getId())
                             ->order(['sorder ASC']);

        return $connRead->fetchAll($dbSelect);
    }

    public function buildEbayStoreCategoriesTreeRec($data, $rootId)
    {
        $children = [];

        foreach ($data as $node) {
            if ($node['parent_id'] == $rootId) {
                $children[] = [
                    'id' => $node['category_id'],
                    'text' => $node['title'],
                    'allowDrop' => false,
                    'allowDrag' => false,
                    'children' => [],
                ];
            }
        }

        foreach ($children as &$child) {
            $child['children'] = $this->buildEbayStoreCategoriesTreeRec($data, $child['id']);
        }

        return $children;
    }

    public function buildEbayStoreCategoriesTree()
    {
        return $this->buildEbayStoreCategoriesTreeRec($this->getEbayStoreCategories(), 0);
    }

    // ----------------------------------------

    public function updateShippingDiscountProfiles($marketplaceId)
    {
        /** @var \Ess\M2ePro\Model\Ebay\Connector\Dispatcher $dispatcherObj */
        $dispatcherObj = $this->modelFactory->getObject('Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector(
            'account',
            'get',
            'shippingDiscountProfiles',
            [],
            null,
            $marketplaceId,
            $this->getId(),
            null
        );

        $dispatcherObj->process($connectorObj);
        $data = $connectorObj->getResponseData();

        if (empty($data)) {
            return;
        }

        if ($this->getData('ebay_shipping_discount_profiles') === null) {
            $profiles = [];
        } else {
            $profiles = \Ess\M2ePro\Helper\Json::decode(
                $this->getData('ebay_shipping_discount_profiles')
            );
        }

        $profiles[$marketplaceId] = $data;

        $this->setData(
            'ebay_shipping_discount_profiles',
            \Ess\M2ePro\Helper\Json::encode($profiles)
        )->save();
    }

    // ----------------------------------------

    public function isCacheEnabled()
    {
        return true;
    }

    public function isFinalFeeUpdateEnabled(): bool
    {
        $setting = $this->getSetting('magento_orders_settings', ['final_fee', 'auto_retrieve_enabled']);

        return (bool)$setting;
    }

    public function getEbaySite(): string
    {
        $site = (string)$this->getData(\Ess\M2ePro\Model\ResourceModel\Ebay\Account::COLUMN_EBAY_SITE);
        if (!empty($site)) {
            return $site;
        }

        $info = $this->getInfo();
        if (empty($info)) {
            return '';
        }

        $infoData = json_decode($info, true);
        if (!is_array($infoData) || !isset($infoData['Site'])) {
            return '';
        }

        return $infoData['Site'];
    }

    public function getInfo()
    {
        return $this->getData(\Ess\M2ePro\Model\ResourceModel\Ebay\Account::COLUMN_INFO);
    }
}
