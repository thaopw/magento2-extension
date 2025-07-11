<?php

namespace Ess\M2ePro\Model\Amazon\Account;

use Ess\M2ePro\Model\Amazon\Account;

class Builder extends \Ess\M2ePro\Model\ActiveRecord\AbstractBuilder
{
    private \Ess\M2ePro\Helper\Magento\Store $magentoStoreHelper;
    private \Ess\M2ePro\Model\Amazon\Account\EventDispatcher $eventDispatcher;

    public function __construct(
        \Ess\M2ePro\Helper\Magento\Store $magentoStoreHelper,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\Amazon\Account\EventDispatcher $eventDispatcher,
        array $data = []
    ) {
        parent::__construct($helperFactory, $modelFactory, $data);

        $this->magentoStoreHelper = $magentoStoreHelper;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param \Ess\M2ePro\Model\ActiveRecord\ActiveRecordAbstract|\Ess\M2ePro\Model\ActiveRecord\AbstractModel $model
     * @param array $rawData
     *
     * @return \Ess\M2ePro\Model\ActiveRecord\ActiveRecordAbstract|\Ess\M2ePro\Model\ActiveRecord\AbstractModel
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function build($model, array $rawData)
    {
        $isNewAccount = $model->isObjectNew();
        $result = parent::build($model, $rawData);

        /** @var \Ess\M2ePro\Model\Amazon\Account $amazonAccount */
        $amazonAccount = $result->getChildObject();
        if ($isNewAccount) {
            $this->eventDispatcher->dispatchEventCreatedSettingManageFbaInventory($amazonAccount);
        } else {
            $this->eventDispatcher->dispatchEventUpdatedSettingManageFbaInventory($amazonAccount);
        }

        return $result;
    }

    protected function prepareData(): array
    {
        $data = [];

        // tab: general
        // ---------------------------------------
        $keys = [
            'title',
            'marketplace_id',
            'merchant_id',
        ];
        foreach ($keys as $key) {
            if (isset($this->rawData[$key])) {
                $data[$key] = $this->rawData[$key];
            }
        }

        // tab: Unmanaged listings
        // ---------------------------------------
        $keys = [
            'related_store_id',

            'other_listings_synchronization',
            'other_listings_mapping_mode',
        ];
        foreach ($keys as $key) {
            if (isset($this->rawData[$key])) {
                $data[$key] = $this->rawData[$key];
            }
        }

        // Mapping
        // ---------------------------------------
        $tempData = [];
        $keys = [
            'mapping_general_id_mode',
            'mapping_general_id_priority',
            'mapping_general_id_attribute',

            'mapping_sku_mode',
            'mapping_sku_priority',
            'mapping_sku_attribute',

            'mapping_title_mode',
            'mapping_title_priority',
            'mapping_title_attribute',
        ];
        foreach ($keys as $key) {
            if (isset($this->rawData[$key])) {
                $tempData[$key] = $this->rawData[$key];
            }
        }

        $mappingSettings = [];
        if ($this->getModel()->getId()) {
            $mappingSettings = $this->getModel()->getChildObject()->getSettings(
                'other_listings_mapping_settings'
            );
        }

        if (isset($tempData['mapping_general_id_mode'])) {
            $mappingSettings['general_id']['mode'] = (int)$tempData['mapping_general_id_mode'];

            if (
                $tempData['mapping_general_id_mode'] == Account::OTHER_LISTINGS_MAPPING_GENERAL_ID_MODE_CUSTOM_ATTRIBUTE
            ) {
                $mappingSettings['general_id']['priority'] = (int)$tempData['mapping_general_id_priority'];
                $mappingSettings['general_id']['attribute'] = (string)$tempData['mapping_general_id_attribute'];
            }
        }

        if (isset($tempData['mapping_sku_mode'])) {
            $mappingSettings['sku']['mode'] = (int)$tempData['mapping_sku_mode'];

            if (
                $tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT
                || $tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE
                || $tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID
            ) {
                $mappingSettings['sku']['priority'] = (int)$tempData['mapping_sku_priority'];
            }

            if ($tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['sku']['attribute'] = (string)$tempData['mapping_sku_attribute'];
            }
        }

        if (isset($tempData['mapping_title_mode'])) {
            $mappingSettings['title']['mode'] = (int)$tempData['mapping_title_mode'];

            if (
                $tempData['mapping_title_mode'] == Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT
                || $tempData['mapping_title_mode'] == Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE
            ) {
                $mappingSettings['title']['priority'] = (int)$tempData['mapping_title_priority'];
            }

            if ($tempData['mapping_title_mode'] == Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['title']['attribute'] = (string)$tempData['mapping_title_attribute'];
            }
        }

        $data['other_listings_mapping_settings'] = \Ess\M2ePro\Helper\Json::encode($mappingSettings);

        // tab: orders
        // ---------------------------------------
        $data['magento_orders_settings'] = [];
        if ($this->getModel()->getId()) {
            $data['magento_orders_settings'] = $this->getModel()->getChildObject()->getSettings(
                'magento_orders_settings'
            );
        }

        // m2e orders settings
        // ---------------------------------------
        $tempKey = 'listing';
        $tempSettings = !empty($this->rawData['magento_orders_settings'][$tempKey])
            ? $this->rawData['magento_orders_settings'][$tempKey] : [];

        $keys = [
            'mode',
            'create_from_date',
            'store_mode',
            'store_id',
        ];
        foreach ($keys as $key) {
            if (!isset($tempSettings[$key])) {
                continue;
            }

            if ($key === 'create_from_date') {
                $tempSettings[$key] = $this->convertDate($tempSettings[$key]);
            }

            $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
        }

        // Unmanaged orders settings
        // ---------------------------------------
        $tempKey = 'listing_other';
        $tempSettings = !empty($this->rawData['magento_orders_settings'][$tempKey])
            ? $this->rawData['magento_orders_settings'][$tempKey] : [];

        $keys = [
            'mode',
            'create_from_date',
            'product_mode',
            'product_tax_class_id',
            'store_id',
        ];
        foreach ($keys as $key) {
            if (!isset($tempSettings[$key])) {
                continue;
            }

            if ($key === 'create_from_date') {
                $tempSettings[$key] = $this->convertDate($tempSettings[$key]);
            }

            $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
        }

        // order number settings
        // ---------------------------------------
        $tempKey = 'number';
        $tempSettings = !empty($this->rawData['magento_orders_settings'][$tempKey])
            ? $this->rawData['magento_orders_settings'][$tempKey] : [];

        if (!empty($tempSettings['source'])) {
            $data['magento_orders_settings'][$tempKey]['source'] = $tempSettings['source'];
        }

        if (isset($tempSettings['apply_to_amazon'])) {
            $data['magento_orders_settings'][$tempKey]['apply_to_amazon'] = $tempSettings['apply_to_amazon'];
        }

        $prefixKeys = [
            'prefix',
            'afn-prefix',
            'prime-prefix',
            'b2b-prefix',
        ];
        $tempSettings = !empty($tempSettings['prefix']) ? $tempSettings['prefix'] : [];
        foreach ($prefixKeys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey]['prefix'][$key] = $tempSettings[$key];
            }
        }

        // qty reservation
        // ---------------------------------------
        $tempKey = 'qty_reservation';
        $tempSettings = !empty($this->rawData['magento_orders_settings'][$tempKey])
            ? $this->rawData['magento_orders_settings'][$tempKey] : [];

        $keys = [
            'days',
        ];
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // refund & cancellation
        // ---------------------------------------
        $tempKey = 'refund_and_cancellation';
        $tempSettings = !empty($this->rawData['magento_orders_settings'][$tempKey])
            ? $this->rawData['magento_orders_settings'][$tempKey] : [];

        $keys = [
            'refund_mode',
            'credit_memo',
            'credit_memo_buyer_requested_cancel',
        ];
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // fba
        // ---------------------------------------
        $tempKey = 'fba';
        $tempSettings = !empty($this->rawData['magento_orders_settings'][$tempKey])
            ? $this->rawData['magento_orders_settings'][$tempKey] : [];

        $keys = [
            'mode',
            'store_mode',
            'store_id',
            'stock_mode',
        ];
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // tax settings
        // ---------------------------------------
        $tempKey = 'tax';
        $tempSettings = !empty($this->rawData['magento_orders_settings'][$tempKey])
            ? $this->rawData['magento_orders_settings'][$tempKey] : [];

        $keys = [
            'mode',
            'amazon_collect_for_uk',
            'amazon_collect_for_eea',
            'import_tax_id_in_magento_order',
            'round_of_rate_value',
        ];
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        if (isset($tempSettings['amazon_collects'])) {
            if ($this->isNeedExcludeStates()) {
                $data['magento_orders_settings'][$tempKey]['amazon_collects'] = $tempSettings['amazon_collects'];
            } else {
                $data['magento_orders_settings'][$tempKey]['amazon_collects'] = 0;
            }
        }

        if (isset($tempSettings['excluded_states'])) {
            $data['magento_orders_settings'][$tempKey]['excluded_states'] = explode(
                ',',
                $tempSettings['excluded_states']
            );
        }

        if (isset($tempSettings['excluded_countries'])) {
            $data['magento_orders_settings'][$tempKey]['excluded_countries'] = explode(
                ',',
                $tempSettings['excluded_countries']
            );
        }

        // customer settings
        // ---------------------------------------
        $tempKey = 'customer';
        $tempSettings = !empty($this->rawData['magento_orders_settings'][$tempKey])
            ? $this->rawData['magento_orders_settings'][$tempKey] : [];

        $keys = [
            'mode',
            'id',
            'website_id',
            'group_id',
            'billing_address_mode',
            'import_buyer_company_name',
        ];
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // Check if input data contains another field from customer settings.
        // It's used to determine if account data changed by user interface, or during token re-new.
        if (isset($tempSettings['mode'])) {
            $notificationsKeys = [
                'order_created',
                'invoice_created',
            ];
            $tempSettings = !empty($tempSettings['notifications']) ? $tempSettings['notifications'] : [];
            foreach ($notificationsKeys as $key) {
                $data['magento_orders_settings'][$tempKey]['notifications'][$key] = in_array($key, $tempSettings);
            }
        }

        // status mapping settings
        // ---------------------------------------
        $tempKey = 'status_mapping';
        $tempSettings = !empty($this->rawData['magento_orders_settings'][$tempKey])
            ? $this->rawData['magento_orders_settings'][$tempKey] : [];

        $keys = [
            'mode',
            'processing',
            'shipped',
        ];
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // shipping information
        // ---------------------------------------
        $tempKey = 'shipping_information';
        $tempSettings = !empty($this->rawData['magento_orders_settings'][$tempKey])
            ? $this->rawData['magento_orders_settings'][$tempKey] : [];

        $keys = [
            'import_labels',
            'ship_by_date',
            'update_without_track',
            'shipping_address_region_override',
        ];
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        $data['magento_orders_settings'] = \Ess\M2ePro\Helper\Json::encode($data['magento_orders_settings']);

        // tab: vat calculation service
        // ---------------------------------------
        $keys = [
            'auto_invoicing',
            'invoice_generation',
            'create_magento_invoice',
            'create_magento_shipment',
            'create_magento_shipment_fba_orders',
        ];
        foreach ($keys as $key) {
            if (isset($this->rawData[$key])) {
                $data[$key] = $this->rawData[$key];
            }
        }

        // tab: FBA Inventory
        // ---------------------------------------
        $keys = [
            'fba_inventory_mode',
            'fba_inventory_source_name',
        ];
        foreach ($keys as $key) {
            if (isset($this->rawData[$key])) {
                $data[$key] = $this->rawData[$key];
            }
        }
        // region server data
        if (isset($this->rawData['server_hash'])) {
            $data['server_hash'] = $this->rawData['server_hash'];
        }

        if (isset($this->rawData['info'])) {
            $data['info'] = \Ess\M2ePro\Helper\Json::encode($this->rawData['info']);
        }

        // endregion

        return $data;
    }

    /**
     * @param \DateTime|string $date
     */
    private function convertDate($date): string
    {
        if (is_string($date)) {
            return $date;
        }

        return \Ess\M2ePro\Helper\Date::createWithGmtTimeZone($date)->format('Y-m-d H:i:s');
    }

    public function getDefaultData(): array
    {
        return [
            // general
            'title' => '',
            'marketplace_id' => 0,
            'merchant_id' => '',

            // listing_other
            'related_store_id' => 0,

            'other_listings_synchronization' => 1, // yes
            'other_listings_mapping_mode' => 1, // yes
            'other_listings_mapping_settings' => [],
            'mapping_sku_mode' => Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT,
            'mapping_sku_priority' => 1,

            // order
            'magento_orders_settings' => [
                'listing' => [
                    'mode' => 1,
                    'create_from_date' => null,
                    'store_mode' => Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT,
                    'store_id' => null,
                ],
                'listing_other' => [
                    'mode' => 1,
                    'create_from_date' => null,
                    'product_mode' => Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE,
                    'product_tax_class_id' => \Ess\M2ePro\Model\Magento\Product::TAX_CLASS_ID_NONE,
                    'store_id' => $this->magentoStoreHelper->getDefaultStoreId(),
                ],
                'number' => [
                    'source' => Account::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO,
                    'prefix' => [
                        'mode' => 0,
                        'prefix' => '',
                        'afn-prefix' => '',
                        'prime-prefix' => '',
                        'b2b-prefix' => '',
                    ],
                    'apply_to_amazon' => 0,
                ],
                'tax' => [
                    'mode' => Account::MAGENTO_ORDERS_TAX_MODE_MIXED,
                    'amazon_collects' => 1,
                    'excluded_states' => $this->getGeneralExcludedStates(),
                    'excluded_countries' => [],
                    'amazon_collect_for_uk' => Account::SKIP_TAX_FOR_UK_SHIPMENT_NONE,
                    'amazon_collect_for_eea' => 0,
                    'import_tax_id_in_magento_order' => 0,
                    'round_of_rate_value' => Account::MAGENTO_ORDERS_TAX_ROUND_OF_RATE_NO,
                ],
                'customer' => [
                    'mode' => Account::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST,
                    'id' => null,
                    'website_id' => null,
                    'group_id' => null,
                    'notifications' => [
                        'invoice_created' => false,
                        'order_created' => false,
                    ],
                    'import_buyer_company_name' => 1,
                ],
                'status_mapping' => [
                    'mode' => Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT,
                    'processing' => Account::MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING,
                    'shipped' => Account::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED,
                ],
                'qty_reservation' => [
                    'days' => 1,
                ],
                'refund_and_cancellation' => [
                    'refund_mode' => 1,
                    'credit_memo' => 0,
                    'credit_memo_buyer_requested_cancel' => 0,
                ],
                'fba' => [
                    'mode' => 1,
                    'store_mode' => 0,
                    'store_id' => null,
                    'stock_mode' => 0,
                ],
                'shipping_information' => [
                    'import_labels' => 1,
                    'ship_by_date' => 1,
                    'update_without_track' => 0,
                    'shipping_address_region_override' => 1,
                ],
            ],

            // vcs_upload_invoices
            'auto_invoicing' => 0,
            'invoice_generation' => 0,
            'create_magento_invoice' => 1,
            'create_magento_shipment' => 1,
            'create_magento_shipment_fba_orders' => 1,

            // fba_inventory
            'fba_inventory_mode' => 0,
            'fba_inventory_source_name' => null,
        ];
    }

    private function isNeedExcludeStates(): bool
    {
        if ($this->rawData['marketplace_id'] != \Ess\M2ePro\Helper\Component\Amazon::MARKETPLACE_US) {
            return false;
        }

        if (
            $this->rawData['magento_orders_settings']['listing']['mode'] == 0
            && $this->rawData['magento_orders_settings']['listing_other']['mode'] == 0
        ) {
            return false;
        }

        if (!isset($this->rawData['magento_orders_settings']['tax']['excluded_states'])) {
            return false;
        }

        return true;
    }

    /**
     * @return string[]
     */
    private function getGeneralExcludedStates(): array
    {
        return [
            'AL',
            'AK',
            'AZ',
            'AR',
            'CA',
            'CO',
            'CT',
            'DC',
            'GA',
            'HI',
            'ID',
            'IL',
            'IN',
            'IA',
            'KY',
            'LA',
            'ME',
            'MD',
            'MA',
            'MI',
            'MN',
            'MS',
            'NE',
            'NV',
            'NJ',
            'NM',
            'NY',
            'NC',
            'ND',
            'OH',
            'OK',
            'PA',
            'PR',
            'RI',
            'SC',
            'SD',
            'TX',
            'UT',
            'VT',
            'VA',
            'WA',
            'WV',
            'WI',
            'WY',
        ];
    }
}
