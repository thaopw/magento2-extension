<?php

namespace Ess\M2ePro\Model\Walmart;

/**
 * @method \Ess\M2ePro\Model\Account getParentObject()
 */
class Account extends \Ess\M2ePro\Model\ActiveRecord\Component\Child\Walmart\AbstractModel
{
    public const OTHER_LISTINGS_MAPPING_SKU_MODE_NONE = 0;
    public const OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT = 1;
    public const OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE = 2;
    public const OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID = 3;

    public const OTHER_LISTINGS_MAPPING_UPC_MODE_NONE = 0;
    public const OTHER_LISTINGS_MAPPING_UPC_MODE_CUSTOM_ATTRIBUTE = 2;

    public const OTHER_LISTINGS_MAPPING_GTIN_MODE_NONE = 0;
    public const OTHER_LISTINGS_MAPPING_GTIN_MODE_CUSTOM_ATTRIBUTE = 2;

    public const OTHER_LISTINGS_MAPPING_WPID_MODE_NONE = 0;
    public const OTHER_LISTINGS_MAPPING_WPID_MODE_CUSTOM_ATTRIBUTE = 2;

    public const OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE = 0;
    public const OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT = 1;
    public const OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE = 2;

    public const OTHER_LISTINGS_MAPPING_SKU_DEFAULT_PRIORITY = 1;
    public const OTHER_LISTINGS_MAPPING_UPC_DEFAULT_PRIORITY = 2;
    public const OTHER_LISTINGS_MAPPING_GTIN_DEFAULT_PRIORITY = 3;
    public const OTHER_LISTINGS_MAPPING_WPID_DEFAULT_PRIORITY = 4;
    public const OTHER_LISTINGS_MAPPING_TITLE_DEFAULT_PRIORITY = 5;

    public const MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT = 0;
    public const MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM = 1;

    public const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE = 0;
    public const MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT = 1;

    public const MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO = 'magento';
    public const MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL = 'channel';

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
    public const MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING = 'processing';
    public const MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED = 'complete';

    /**
     * @var \Ess\M2ePro\Model\Marketplace
     */
    private $marketplaceModel = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Ess\M2ePro\Model\ResourceModel\Walmart\Account::class);
    }

    public function getWalmartItems($asObjects = false, array $filters = [])
    {
        return $this->getRelatedSimpleItems('Walmart\Item', 'account_id', $asObjects, $filters);
    }

    public function getProcessingList(): array
    {
        return $this->getRelatedSimpleItems('Walmart_Listing_Product_Action_ProcessingList', 'account_id', true);
    }

    public function deleteProcessingList(): void
    {
        $items = $this->getProcessingList();

        foreach ($items as $item) {
            $item->delete();
        }
    }

    /**
     * @return \Ess\M2ePro\Model\Marketplace
     */
    public function getMarketplace()
    {
        if ($this->marketplaceModel === null) {
            $this->marketplaceModel = $this->walmartFactory->getCachedObjectLoaded(
                'Marketplace',
                $this->getMarketplaceId()
            );
        }

        return $this->marketplaceModel;
    }

    /**
     * @param \Ess\M2ePro\Model\Marketplace $instance
     */
    public function setMarketplace(\Ess\M2ePro\Model\Marketplace $instance)
    {
        $this->marketplaceModel = $instance;
    }

    public function getServerHash()
    {
        return $this->getData('server_hash');
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    public function getIdentifier(): string
    {
        return $this->getData(\Ess\M2ePro\Model\ResourceModel\Walmart\Account::COLUMN_IDENTIFIER);
    }

    /**
     * @return int
     */
    public function getRelatedStoreId()
    {
        return (int)$this->getData('related_store_id');
    }

    // ---------------------------------------

    public function setOrdersWfsLastSynchronization(\DateTime $value): self
    {
        $this->setData('orders_wfs_last_synchronization', $value->format('Y-m-d H:i:s'));

        return $this;
    }

    public function getOrdersWfsLastSynchronization(): ?\DateTime
    {
        $value = $this->getData('orders_wfs_last_synchronization');
        if (empty($value)) {
            return null;
        }

        return \Ess\M2ePro\Helper\Date::createDateGmt($value);
    }

    // ---------------------------------------

    public function getInfo()
    {
        return $this->getData('info');
    }

    /**
     * @return array|null
     */
    public function getDecodedInfo()
    {
        $tempInfo = $this->getInfo();

        return $tempInfo === null ? null : \Ess\M2ePro\Helper\Json::decode($tempInfo);
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
     * @return string
     */
    public function getInventoryLastSynchronization()
    {
        return $this->getData('inventory_last_synchronization');
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
     * @return bool
     */
    public function isImportShipByDateToMagentoOrder(): bool
    {
        return (bool)$this->getSetting(
            'magento_orders_settings',
            ['shipping_information', 'ship_by_date'],
            true
        );
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
    public function getOtherListingsMappingUpcMode()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['upc', 'mode'],
            self::OTHER_LISTINGS_MAPPING_UPC_MODE_NONE
        );

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingUpcPriority()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['upc', 'priority'],
            self::OTHER_LISTINGS_MAPPING_UPC_DEFAULT_PRIORITY
        );

        return (int)$setting;
    }

    public function getOtherListingsMappingUpcAttribute()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['upc', 'attribute']
        );

        return $setting;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOtherListingsMappingGtinMode()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['gtin', 'mode'],
            self::OTHER_LISTINGS_MAPPING_GTIN_MODE_NONE
        );

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingGtinPriority()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['gtin', 'priority'],
            self::OTHER_LISTINGS_MAPPING_GTIN_DEFAULT_PRIORITY
        );

        return (int)$setting;
    }

    public function getOtherListingsMappingGtinAttribute()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['gtin', 'attribute']
        );

        return $setting;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOtherListingsMappingWpidMode()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['wpid', 'mode'],
            self::OTHER_LISTINGS_MAPPING_WPID_MODE_NONE
        );

        return (int)$setting;
    }

    /**
     * @return int
     */
    public function getOtherListingsMappingWpidPriority()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['wpid', 'priority'],
            self::OTHER_LISTINGS_MAPPING_WPID_DEFAULT_PRIORITY
        );

        return (int)$setting;
    }

    public function getOtherListingsMappingWpidAttribute()
    {
        $setting = $this->getSetting(
            'other_listings_mapping_settings',
            ['wpid', 'attribute']
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
    public function isOtherListingsMappingGtinModeNone()
    {
        return $this->getOtherListingsMappingGtinMode() == self::OTHER_LISTINGS_MAPPING_GTIN_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingGtinModeCustomAttribute()
    {
        return $this->getOtherListingsMappingGtinMode() == self::OTHER_LISTINGS_MAPPING_GTIN_MODE_CUSTOM_ATTRIBUTE;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOtherListingsMappingWpidModeNone()
    {
        return $this->getOtherListingsMappingWpidMode() == self::OTHER_LISTINGS_MAPPING_WPID_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingWpidModeCustomAttribute()
    {
        return $this->getOtherListingsMappingWpidMode() == self::OTHER_LISTINGS_MAPPING_WPID_MODE_CUSTOM_ATTRIBUTE;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOtherListingsMappingUpcModeNone()
    {
        return $this->getOtherListingsMappingUpcMode() == self::OTHER_LISTINGS_MAPPING_UPC_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isOtherListingsMappingUpcModeCustomAttribute()
    {
        return $this->getOtherListingsMappingUpcMode() == self::OTHER_LISTINGS_MAPPING_UPC_MODE_CUSTOM_ATTRIBUTE;
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
        $setting = $this->getSetting('magento_orders_settings', ['listing_other', 'product_tax_class_id']);

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

    public function getMagentoOrdersNumberWFSPrefix(): string
    {
        $settings = $this->getSetting('magento_orders_settings', ['number', 'prefix']);

        return isset($settings['wfs-prefix']) ? $settings['wfs-prefix'] : '';
    }

    // ---------------------------------------

    public function getQtyReservationDays(): int
    {
        $reservationDays = $this->getSetting('magento_orders_settings', ['qty_reservation', 'days']);
        if (empty($reservationDays)) {
            return 1;
        }

        return (int)$reservationDays;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeNone()
    {
        $setting = $this->getSetting('magento_orders_settings', ['tax', 'mode']);

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeChannel()
    {
        $setting = $this->getSetting('magento_orders_settings', ['tax', 'mode']);

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_CHANNEL;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeMagento()
    {
        $setting = $this->getSetting('magento_orders_settings', ['tax', 'mode']);

        return $setting == self::MAGENTO_ORDERS_TAX_MODE_MAGENTO;
    }

    /**
     * @return bool
     */
    public function isMagentoOrdersTaxModeMixed()
    {
        $setting = $this->getSetting('magento_orders_settings', ['tax', 'mode']);

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

    // ---------------------------------------

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

    public function getMagentoOrdersStatusProcessing()
    {
        if ($this->isMagentoOrdersStatusMappingDefault()) {
            return self::MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING;
        }

        return $this->getSetting('magento_orders_settings', ['status_mapping', 'processing']);
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

    // ---------------------------------------

    public function isMagentoOrdersInvoiceEnabled()
    {
        return (bool)$this->getData('create_magento_invoice');
    }

    public function isMagentoOrdersShipmentEnabled()
    {
        return (bool)$this->getData('create_magento_shipment');
    }

    public function getOtherCarriers()
    {
        return $this->getSettings('other_carriers');
    }
    public function isCacheEnabled()
    {
        return true;
    }

    public function isRegionOverrideRequired(): bool
    {
        return (bool)$this->getSetting(
            'magento_orders_settings',
            ['shipping_information', 'shipping_address_region_override'],
            1
        );
    }

    public function isMagentoOrdersWfsModeEnabled(): bool
    {
        return $this->getSetting('magento_orders_settings', ['wfs', 'mode'], 0) === 1;
    }

    public function isMagentoOrdersWfsStoreModeEnabled(): bool
    {
        return $this->getSetting('magento_orders_settings', ['wfs', 'store_mode'], 0) === 1;
    }

    public function getMagentoOrdersWfsStoreId(): int
    {
        $setting = $this->getSetting('magento_orders_settings', ['wfs', 'store_id'], 0);

        return (int)$setting;
    }

    public function isMagentoOrdersWfsStockEnabled(): bool
    {
        return $this->getSetting('magento_orders_settings', ['wfs', 'stock_mode'], 0) === 1;
    }
}
