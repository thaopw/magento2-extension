<?php

namespace Ess\M2ePro\Model\Walmart\Account;

use Ess\M2ePro\Model\Walmart\Account;

class Builder extends \Ess\M2ePro\Model\ActiveRecord\AbstractBuilder
{
    protected function prepareData()
    {
        $data = [];

        // tab: general
        // ---------------------------------------
        $keys = [
            'title',
            'marketplace_id'
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
            'mapping_sku_mode',
            'mapping_sku_priority',
            'mapping_sku_attribute',

            'mapping_upc_mode',
            'mapping_upc_priority',
            'mapping_upc_attribute',

            'mapping_gtin_mode',
            'mapping_gtin_priority',
            'mapping_gtin_attribute',

            'mapping_wpid_mode',
            'mapping_wpid_priority',
            'mapping_wpid_attribute',

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

        $temp = [
            Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT,
            Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE,
            Account::OTHER_LISTINGS_MAPPING_SKU_MODE_PRODUCT_ID,
        ];

        if (isset($tempData['mapping_sku_mode'])) {
            $mappingSettings['sku']['mode'] = (int)$tempData['mapping_sku_mode'];

            if (in_array($tempData['mapping_sku_mode'], $temp)) {
                $mappingSettings['sku']['priority'] = (int)$tempData['mapping_sku_priority'];
            }

            if ($tempData['mapping_sku_mode'] == Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['sku']['attribute'] = (string)$tempData['mapping_sku_attribute'];
            }
        }

        if (isset($tempData['mapping_upc_mode'])) {
            $mappingSettings['upc']['mode'] = (int)$tempData['mapping_upc_mode'];

            if ($tempData['mapping_upc_mode'] == Account::OTHER_LISTINGS_MAPPING_UPC_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['upc']['priority'] = (int)$tempData['mapping_upc_priority'];
                $mappingSettings['upc']['attribute'] = (string)$tempData['mapping_upc_attribute'];
            }
        }

        if (isset($tempData['mapping_gtin_mode'])) {
            $mappingSettings['gtin']['mode'] = (int)$tempData['mapping_gtin_mode'];

            if ($tempData['mapping_gtin_mode'] == Account::OTHER_LISTINGS_MAPPING_GTIN_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['gtin']['priority'] = (int)$tempData['mapping_gtin_priority'];
                $mappingSettings['gtin']['attribute'] = (string)$tempData['mapping_gtin_attribute'];
            }
        }

        if (isset($tempData['mapping_wpid_mode'])) {
            $mappingSettings['wpid']['mode'] = (int)$tempData['mapping_wpid_mode'];

            if ($tempData['mapping_wpid_mode'] == Account::OTHER_LISTINGS_MAPPING_WPID_MODE_CUSTOM_ATTRIBUTE) {
                $mappingSettings['wpid']['priority'] = (int)$tempData['mapping_wpid_priority'];
                $mappingSettings['wpid']['attribute'] = (string)$tempData['mapping_wpid_attribute'];
            }
        }

        if (isset($tempData['mapping_title_mode'])) {
            $mappingSettings['title']['mode'] = (int)$tempData['mapping_title_mode'];

            if (
                $tempData['mapping_title_mode'] == Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_DEFAULT ||
                $tempData['mapping_title_mode'] == Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE
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

        $prefixKeys = [
            'prefix',
            'wfs-prefix',
        ];
        $tempSettings = !empty($tempSettings['prefix']) ? $tempSettings['prefix'] : [];
        foreach ($prefixKeys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey]['prefix'][$key] = $tempSettings[$key];
            }
        }

        // wfs
        // ---------------------------------------
        $tempKey = 'wfs';
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
        ];
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
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

        // refund & cancellation
        // ---------------------------------------
        $tempKey = 'refund_and_cancellation';
        $tempSettings = !empty($this->rawData['magento_orders_settings'][$tempKey])
            ? $this->rawData['magento_orders_settings'][$tempKey] : [];

        $keys = [
            'refund_mode',
        ];
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        // qty reservation
        // ---------------------------------------
        if (isset($this->rawData['magento_orders_settings']['qty_reservation']['days'])) {
            $data['magento_orders_settings']['qty_reservation']['days']
                = $this->rawData['magento_orders_settings']['qty_reservation']['days'];
        }

        // Shipping information
        // ---------------------------------------
        $tempKey = 'shipping_information';
        $tempSettings = !empty($this->rawData['magento_orders_settings'][$tempKey])
            ? $this->rawData['magento_orders_settings'][$tempKey] : [];

        $keys = [
            'ship_by_date',
            'shipping_address_region_override',
        ];
        foreach ($keys as $key) {
            if (isset($tempSettings[$key])) {
                $data['magento_orders_settings'][$tempKey][$key] = $tempSettings[$key];
            }
        }

        $data['magento_orders_settings'] = \Ess\M2ePro\Helper\Json::encode($data['magento_orders_settings']);

        // tab invoice and shipment
        // ---------------------------------------
        $keys = [
            'create_magento_invoice',
            'create_magento_shipment',
        ];
        foreach ($keys as $key) {
            if (isset($this->rawData[$key])) {
                $data[$key] = $this->rawData[$key];
            }
        }

        if (isset($this->rawData['other_carrier']) && isset($this->rawData['other_carrier_url'])) {
            $otherCarriers = [];
            $carriers = array_filter($this->rawData['other_carrier']);
            $carrierURLs = array_filter($this->rawData['other_carrier_url']);

            foreach ($carriers as $index => $code) {
                $otherCarriers[] = [
                    'code' => $code,
                    'url' => isset($carrierURLs[$index]) ? $carrierURLs[$index] : '',
                ];
            }

            $data['other_carriers'] = \Ess\M2ePro\Helper\Json::encode($otherCarriers);
        }

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

    public function getDefaultData()
    {
        return [
            'title' => '',
            'marketplace_id' => 0,

            'related_store_id' => 0,

            'other_listings_synchronization' => 1,
            'other_listings_mapping_mode' => 1,
            'other_listings_mapping_settings' => [
                'sku' => [
                    'mode' => Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT,
                    'priority' => 1,
                ],
            ],
            'mapping_sku_mode' => Account::OTHER_LISTINGS_MAPPING_SKU_MODE_DEFAULT,
            'mapping_sku_priority' => 1,

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
                    'store_id' => null,
                ],
                'number' => [
                    'source' => Account::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO,
                    'prefix' => [
                        'prefix' => '',
                        'wfs-prefix' => '',
                    ],
                ],
                'tax' => [
                    'mode' => Account::MAGENTO_ORDERS_TAX_MODE_MIXED,
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
                ],
                'status_mapping' => [
                    'mode' => Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT,
                    'processing' => Account::MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING,
                    'shipped' => Account::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED,
                ],
                'refund_and_cancellation' => [
                    'refund_mode' => 1,
                ],
                'shipping_information' => [
                    'ship_by_date' => 1,
                    'shipping_address_region_override' => 1,
                ],
                'qty_reservation' => [
                    'days' => 1,
                ],
            ],
            'create_magento_invoice' => 1,
            'create_magento_shipment' => 1,
            'other_carriers' => [],
        ];
    }
}
