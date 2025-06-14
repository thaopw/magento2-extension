<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Amazon\Listing\View\Settings;

class Grid extends \Ess\M2ePro\Block\Adminhtml\Listing\View\Grid
{
    /** @var  \Ess\M2ePro\Model\Listing */
    protected $listing;

    /** @var \Ess\M2ePro\Model\ResourceModel\Magento\Product\CollectionFactory */
    protected $magentoProductCollectionFactory;

    /** @var \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory */
    protected $amazonFactory;

    /** @var \Magento\Framework\App\ResourceConnection */
    protected $resourceConnection;

    /** @var \Ess\M2ePro\Helper\Component\Amazon */
    private $amazonHelper;

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ess\M2ePro\Helper\Component\Amazon $amazonHelper,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ess\M2ePro\Helper\Data $dataHelper,
        \Ess\M2ePro\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->amazonFactory = $amazonFactory;
        $this->resourceConnection = $resourceConnection;
        $this->amazonHelper = $amazonHelper;
        parent::__construct($context, $backendHelper, $dataHelper, $globalDataHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->listing = $this->globalDataHelper->getValue('view_listing');

        $this->setId('amazonListingViewGrid' . $this->listing['id']);

        $this->showAdvancedFilterProductsOption = false;
    }

    protected function _prepareCollection()
    {
        /** @var \Ess\M2ePro\Model\ResourceModel\Magento\Product\Collection $collection */
        $collection = $this->magentoProductCollectionFactory->create();

        $collection->distinct(true);
        $collection->setListingProductModeOn();
        $collection->setStoreId($this->listing->getStoreId());
        $collection->setListing($this->listing->getId());

        $collection
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->joinStockItem();

        $lpTable = $this->activeRecordFactory->getObject('Listing\Product')->getResource()->getMainTable();
        $collection->joinTable(
            ['lp' => $lpTable],
            'product_id=entity_id',
            [
                'id' => 'id',
                'amazon_status' => 'status',
                'component_mode' => 'component_mode',
                'additional_data' => 'additional_data',
            ],
            [
                'listing_id' => (int)$this->listing['id'],
            ]
        );

        $alpTable = $this->activeRecordFactory->getObject('Amazon_Listing_Product')->getResource()->getMainTable();
        $collection->joinTable(
            ['alp' => $alpTable],
            'listing_product_id=id',
            [
                'template_product_type_id' => 'template_product_type_id',
                'template_shipping_id' => 'template_shipping_id',
                'template_product_tax_code_id' => 'template_product_tax_code_id',
                'general_id' => 'general_id',
                'general_id_search_info' => 'general_id_search_info',
                'search_settings_status' => 'search_settings_status',
                'search_settings_data' => 'search_settings_data',
                'variation_child_statuses' => 'variation_child_statuses',
                'amazon_sku' => 'sku',
                'online_qty' => 'online_qty',
                'online_regular_price' => 'online_regular_price',
                'online_regular_sale_price' => 'IF(
                  `alp`.`online_regular_sale_price_start_date` IS NOT NULL AND
                  `alp`.`online_regular_sale_price_end_date` IS NOT NULL AND
                  `alp`.`online_regular_sale_price_end_date` >= CURRENT_DATE(),
                  `alp`.`online_regular_sale_price`,
                  NULL
                )',
                'online_regular_sale_price_start_date' => 'online_regular_sale_price_start_date',
                'online_regular_sale_price_end_date' => 'online_regular_sale_price_end_date',
                'is_repricing' => 'is_repricing',
                'is_afn_channel' => 'is_afn_channel',
                'is_general_id_owner' => 'is_general_id_owner',
                'is_variation_parent' => 'is_variation_parent',
                'is_variation_product' => 'is_variation_product',
                'defected_messages' => 'defected_messages',
                'variation_parent_afn_state' => 'variation_parent_afn_state',
                'variation_parent_repricing_state' => 'variation_parent_repricing_state',
            ],
            '{{table}}.variation_parent_id is NULL'
        );

        $tsTable = $this->activeRecordFactory->getObject('Amazon_Template_Shipping')
                                             ->getResource()->getMainTable();
        $collection->joinTable(
            ['ts' => $tsTable],
            'id=template_shipping_id',
            [
                'template_shipping_title' => 'title',
            ],
            null,
            'left'
        );

        $ptTable = $this->activeRecordFactory
            ->getObject('Amazon_Template_ProductType')
            ->getResource()->getMainTable();
        $collection->joinTable(
            ['pt' => $ptTable],
            'id=template_product_type_id',
            [
                'dictionary_product_type_id' => 'dictionary_product_type_id',
                'product_type_title' => 'title',
            ],
            null,
            'left'
        );

        $amazonAccount = $this->listing->getAccount()->getChildObject();

        if (
            $amazonAccount->getMarketplace()->getChildObject()->isProductTaxCodePolicyAvailable() &&
            $amazonAccount->isVatCalculationServiceEnabled()
        ) {
            $ptcTable = $this->activeRecordFactory->getObject('Amazon_Template_ProductTaxCode')
                                                  ->getResource()->getMainTable();
            $collection->joinTable(
                ['tptc' => $ptcTable],
                'id=template_product_tax_code_id',
                [
                    'template_product_tax_code_title' => 'title',
                ],
                null,
                'left'
            );
        }

        if ($this->isFilterOrSortByPriceIsUsed(null, 'amazon_online_price')) {
            $collection->joinIndexerParent();
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', [
            'header' => $this->__('Product ID'),
            'align' => 'right',
            'width' => '100px',
            'type' => 'number',
            'index' => 'entity_id',
            'store_id' => $this->listing->getStoreId(),
            'renderer' => \Ess\M2ePro\Block\Adminhtml\Magento\Grid\Column\Renderer\ProductId::class,
        ]);

        $this->addColumn('name', [
            'header' => $this->__('Product Title / Product SKU'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'name',
            'filter_index' => 'name',
            'escape' => false,
            'frame_callback' => [$this, 'callbackColumnProductTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        $this->addColumn('sku', [
            'header' => $this->__('Channel SKU'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'text',
            'index' => 'amazon_sku',
            'show_defected_messages' => false,
            'filter_index' => 'amazon_sku',
            'renderer' => \Ess\M2ePro\Block\Adminhtml\Amazon\Grid\Column\Renderer\Sku::class,
        ]);

        $this->addColumn('general_id', [
            'header' => $this->__('ASIN / ISBN'),
            'align' => 'left',
            'width' => '140px',
            'type' => 'text',
            'index' => 'general_id',
            'filter_index' => 'general_id',
            'filter' => \Ess\M2ePro\Block\Adminhtml\Amazon\Grid\Column\Filter\GeneralId::class,
            'frame_callback' => [$this, 'callbackColumnGeneralId'],
            'filter_condition_callback' => [$this, 'callbackFilterGeneralId'],
        ]);

        $this->addColumn('product_type', [
            'header' => $this->__('Product Type'),
            'align' => 'left',
            'width' => '140px',
            'type' => 'text',
            'index' => 'product_type',
            'filter_index' => 'product_type',
            'frame_callback' => [$this, 'callbackColumnProductType'],
            'filter_condition_callback' => [$this, 'callbackFilterProductType'],
        ]);

        $this->addColumn('shipping_template', [
            'header' => 'Shipping Policy',
            'align' => 'left',
            'width' => '170px',
            'type' => 'text',
            'index' => 'template_shipping_title',
            'filter' => \Ess\M2ePro\Block\Adminhtml\Amazon\Grid\Column\Filter\PolicySettings::class,
            'filter_index' => 'template_shipping_title',
            'filter_condition_callback' => [$this, 'callbackFilterShippingSettings'],
            'frame_callback' => [$this, 'callbackColumnTemplateShipping'],
        ]);

        if (
            $this->listing->getMarketplace()->getChildObject()->isProductTaxCodePolicyAvailable() &&
            $this->listing->getAccount()->getChildObject()->isVatCalculationServiceEnabled()
        ) {
            $this->addColumn('product_tax_code_template', [
                'header' => $this->__('Product Tax Code Policy'),
                'align' => 'left',
                'width' => '170px',
                'type' => 'text',
                'index' => 'template_product_tax_code_title',
                'filter_index' => 'template_product_tax_code_title',
                'frame_callback' => [$this, 'callbackColumnTemplateProductTaxCode'],
            ]);
        }

        $this->addColumn('actions', [
            'header' => $this->__('Actions'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'action',
            'index' => 'actions',
            'filter' => false,
            'sortable' => false,
            'renderer' => \Ess\M2ePro\Block\Adminhtml\Magento\Grid\Column\Renderer\Action::class,
            'field' => 'id',
            'group_order' => $this->getGroupOrder(),
            'actions' => $this->getColumnActionsItems(),
        ]);

        return parent::_prepareColumns();
    }

    protected function getGroupOrder()
    {
        $groups = [
            'edit_template_shipping' => $this->__('Shipping Policy'),
            'edit_product_type' => $this->__('Product Type'),
            'other' => $this->__('Other'),
        ];

        if (
            $this->listing->getMarketplace()->getChildObject()->isProductTaxCodePolicyAvailable() &&
            $this->listing->getAccount()->getChildObject()->isVatCalculationServiceEnabled()
        ) {
            $groups['edit_template_product_tax_code'] = $this->__('Product Tax Code Policy');
        }

        return $groups;
    }

    protected function getColumnActionsItems()
    {
        $actions = [
            'assignProductType' => [
                'caption' => $this->__('Assign'),
                'group' => 'edit_product_type',
                'field' => 'id',
                'onclick_action' => 'ListingGridObj.actions[\'assignProductTypeAction\']',
            ],

            'unassignProductType' => [
                'caption' => $this->__('Unassign'),
                'group' => 'edit_product_type',
                'field' => 'id',
                'onclick_action' => 'ListingGridObj.unassignProductTypeConfrim',
            ],
        ];

        $actions['assignTemplateShipping'] = [
            'caption' => $this->__('Assign'),
            'group' => 'edit_template_shipping',
            'field' => 'id',
            'onclick_action' => 'ListingGridObj.actions[\'assignTemplateShippingIdAction\']',
        ];

        $actions['unassignTemplateShipping'] = [
            'caption' => $this->__('Unassign'),
            'group' => 'edit_template_shipping',
            'field' => 'id',
            'onclick_action' => 'ListingGridObj.unassignTemplateShippingIdActionConfrim',
        ];

        if (
            $this->listing->getMarketplace()->getChildObject()->isProductTaxCodePolicyAvailable() &&
            $this->listing->getAccount()->getChildObject()->isVatCalculationServiceEnabled()
        ) {
            $actions['assignTemplateProductTaxCode'] = [
                'caption' => $this->__('Assign'),
                'group' => 'edit_template_product_tax_code',
                'field' => 'id',
                'onclick_action' => 'ListingGridObj.actions[\'assignTemplateProductTaxCodeIdAction\']',
            ];

            $actions['unassignTemplateProductTaxCode'] = [
                'caption' => $this->__('Unassign'),
                'group' => 'edit_template_product_tax_code',
                'field' => 'id',
                'onclick_action' => 'ListingGridObj.unassignTemplateProductTaxCodeIdActionConfrim',
            ];
        }

        $actions['remapProduct'] = [
            'caption' => $this->__('Link to another Magento Product'),
            'group' => 'other',
            'field' => 'id',
            'only_remap_product' => true,
            'style' => 'width: 130px',
            'onclick_action' => 'ListingGridObj.actions[\'remapProductAction\']',
        ];

        return $actions;
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        // ---------------------------------------

        // Set mass-action
        // ---------------------------------------
        $groups = [
            'shipping_policy' => $this->__('Shipping Policy'),
            'product_type' => $this->__('Product Type'),
            'edit_template_product_tax_code' => $this->__('Product Tax Code Policy'),
            'other' => $this->__('Other'),
        ];

        $this->getMassactionBlock()->setGroups($groups);

        $this->getMassactionBlock()->addItem('assignTemplateShippingId', [
            'label' => $this->__('Assign'),
            'url' => '',
        ], 'shipping_policy');

        $this->getMassactionBlock()->addItem('unassignTemplateShippingId', [
            'label' => $this->__('Unassign'),
            'url' => '',
        ], 'shipping_policy');

        $this->getMassactionBlock()->addItem('assignProductType', [
            'label' => $this->__('Assign'),
            'url' => '',
        ], 'product_type');

        $this->getMassactionBlock()->addItem('unassignProductType', [
            'label' => $this->__('Unassign'),
            'url' => '',
        ], 'product_type');

        if (
            $this->listing->getMarketplace()->getChildObject()->isProductTaxCodePolicyAvailable() &&
            $this->listing->getAccount()->getChildObject()->isVatCalculationServiceEnabled()
        ) {
            $this->getMassactionBlock()->addItem('assignTemplateProductTaxCodeId', [
                'label' => $this->__('Assign'),
                'url' => '',
            ], 'edit_template_product_tax_code');

            $this->getMassactionBlock()->addItem('unassignTemplateProductTaxCodeId', [
                'label' => $this->__('Unassign'),
                'url' => '',
            ], 'edit_template_product_tax_code');
        }

        $this->getMassactionBlock()->addItem('moving', [
            'label' => $this->__('Move Item(s) to Another Listing'),
            'url' => '',
        ], 'other');

        $this->getMassactionBlock()->addItem('duplicate', [
            'label' => $this->__('Duplicate'),
            'url' => '',
        ], 'other');

        $this->getMassactionBlock()->addItem('transferring', [
            'label' => $this->__('Sell on Another Marketplace'),
            'url' => '',
        ], 'other');

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = $this->dataHelper->escapeHtml($productTitle);

        $value = '<span>' . $productTitle . '</span>';

        $sku = $this->modelFactory->getObject('Magento\Product')
                                  ->setProductId($row->getData('entity_id'))
                                  ->getSku();

        $value .= '<br/><strong>' . $this->__('SKU') .
            ':</strong><span class="white-space-pre-wrap"> ' . $this->dataHelper->escapeHtml($sku) . '</span><br/>';

        $listingProductId = (int)$row->getData('id');
        /** @var \Ess\M2ePro\Model\Listing\Product $listingProduct */
        $listingProduct = $this->amazonFactory->getObjectLoaded('Listing\Product', $listingProductId);

        if (!$listingProduct->getChildObject()->getVariationManager()->isVariationProduct()) {
            return $value;
        }

        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();

        if ($variationManager->isRelationParentType()) {
            $productAttributes = (array)$variationManager->getTypeModel()->getProductAttributes();
            $virtualProductAttributes = $variationManager->getTypeModel()->getVirtualProductAttributes();
            $virtualChannelAttributes = $variationManager->getTypeModel()->getVirtualChannelAttributes();

            $value .= '<div style="font-size: 11px; font-weight: bold; color: grey; margin-left: 7px"><br/>';
            $attributesStr = '';
            if (empty($virtualProductAttributes) && empty($virtualChannelAttributes)) {
                $attributesStr = implode(', ', $productAttributes);
            } else {
                foreach ($productAttributes as $attribute) {
                    if (in_array($attribute, array_keys($virtualProductAttributes))) {
                        $attributesStr .= '<span style="border-bottom: 2px dotted grey">' . $attribute .
                            ' (' . $virtualProductAttributes[$attribute] . ')</span>, ';
                    } elseif (in_array($attribute, array_keys($virtualChannelAttributes))) {
                        $attributesStr .= '<span>' . $attribute .
                            ' (' . $virtualChannelAttributes[$attribute] . ')</span>, ';
                    } else {
                        $attributesStr .= $attribute . ', ';
                    }
                }
                $attributesStr = rtrim($attributesStr, ', ');
            }
            $value .= $attributesStr;

            return $value;
        }

        $productOptions = $variationManager->getTypeModel()->getProductOptions();

        if (!empty($productOptions)) {
            $value .= '<div style="font-size: 11px; color: grey; margin-left: 7px"><br/>';
            foreach ($productOptions as $attribute => $option) {
                if ($option === '' || $option === null) {
                    $option = '--';
                }
                $value .= '<strong>' . $this->dataHelper->escapeHtml($attribute) .
                    '</strong>:&nbsp;' . $this->dataHelper->escapeHtml($option) . '<br/>';
            }
            $value .= '</div>';
        }

        return $value;
    }

    public function callbackColumnGeneralId($generalId, $row, $column, $isExport)
    {
        if (empty($generalId)) {
            if ($row->getData('is_general_id_owner') == 1) {
                return $this->__('New ASIN/ISBN');
            }

            return $this->getGeneralIdColumnValueEmptyGeneralId($row);
        }

        return $this->getGeneralIdColumnValueNotEmptyGeneralId($row);
    }

    /**
     * @param string $generalId
     * @param \Magento\Catalog\Model\Product $row
     * @param \Ess\M2ePro\Block\Adminhtml\Widget\Grid\Column\Extended\Rewrite $column
     * @param bool $isExport
     *
     * @return false|string
     */
    public function callbackColumnProductType(
        string $generalId,
        \Magento\Catalog\Model\AbstractModel $row,
        \Ess\M2ePro\Block\Adminhtml\Widget\Grid\Column\Extended\Rewrite $column,
        bool $isExport
    ) {
        $productTypeId = (int)$row->getData('template_product_type_id');
        $productTypeTitle = (int)$row->getData('product_type_title');

        if ($productTypeId === 0) {
            return $this->__('N/A');
        }

        $url = $this->getUrl('*/amazon_template_productType/edit', [
            'id' => $productTypeId,
            'close_on_save' => true,
        ]);

        return sprintf(
            '<a target="_blank" href="%s">%s</a>',
            $url,
            $this->dataHelper->escapeHtml($row->getData('product_type_title'))
        );
    }

    private function getGeneralIdColumnValueEmptyGeneralId($row)
    {
        // ---------------------------------------
        if ((int)$row->getData('amazon_status') != \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED) {
            return '<i style="color:gray;">' . $this->__('receiving...') . '</i>';
        }

        $searchSettingsStatus = $row->getData('search_settings_status');

        // ---------------------------------------
        if ($searchSettingsStatus == \Ess\M2ePro\Model\Amazon\Listing\Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS) {
            $tip = $this->__('Automatic ASIN/ISBN Search in Progress.');
            $iconSrc = $this->getViewFileUrl('Ess_M2ePro::images/search_statuses/processing.gif');

            return <<<HTML
&nbsp;
<a href="javascript: void(0);" title="{$tip}">
    <img src="{$iconSrc}" alt="">
</a>
HTML;
        }

        // ---------------------------------------

        return $this->__('N/A');
    }

    private function getGeneralIdColumnValueNotEmptyGeneralId($row)
    {
        $generalId = $row->getData('general_id');

        $url = $this->amazonHelper->getItemUrl($generalId, $this->listing->getMarketplaceId());

        $generalIdOwnerHtml = '';
        if ($row->getData('is_general_id_owner') == \Ess\M2ePro\Model\Amazon\Listing\Product::IS_GENERAL_ID_OWNER_YES) {
            $generalIdOwnerHtml = '<br/><span style="font-size: 10px; color: grey;">' .
                $this->__('creator of ASIN/ISBN') .
                '</span>';
        }

        if ((int)$row->getData('amazon_status') != \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED) {
            return <<<HTML
<a href="{$url}" target="_blank">{$generalId}</a>{$generalIdOwnerHtml}
HTML;
        }

        $generalIdSearchInfo = $row->getData('general_id_search_info');

        if (!empty($generalIdSearchInfo)) {
            $generalIdSearchInfo = \Ess\M2ePro\Helper\Json::decode($generalIdSearchInfo);
        }

        if (!empty($generalIdSearchInfo['is_set_automatic'])) {
            $tip = $this->__('ASIN/ISBN was found automatically');

            $text = <<<HTML
<a href="{$url}" target="_blank" title="{$tip}" style="color:#40AADB;">{$generalId}</a>
HTML;
        } else {
            $text = <<<HTML
<a href="{$url}" target="_blank">{$generalId}</a>
HTML;
        }

        return $text . $generalIdOwnerHtml;
    }

    public function callbackColumnTemplateShipping($value, $row, $column, $isExport)
    {
        $html = $this->__('N/A');

        if (
            $row->getData('template_shipping_id')
        ) {
            $url = $this->getUrl('*/amazon_template_shipping/edit', [
                'id' => $row->getData('template_shipping_id'),
                'close_on_save' => true,
            ]);

            $templateTitle = $this->dataHelper->escapeHtml($row->getData('template_shipping_title'));

            return <<<HTML
<a target="_blank" href="{$url}">{$templateTitle}</a>
HTML;
        } elseif ($this->listing->getChildObject()->getData('template_shipping_id')) {
            $shippingSettings = $this->__('Use from Listing Settings');

            return <<<HTML
<div style="padding: 4px">
    <span style="color: #666666">{$shippingSettings}</span><br/>
</div>
HTML;
        }

        return $html;
    }

    public function callbackColumnTemplateProductTaxCode($value, $row, $column, $isExport)
    {
        $html = $this->__('N/A');

        if ($row->getData('template_product_tax_code_id')) {
            $url = $this->getUrl('*/amazon_template_productTaxCode/edit', [
                'id' => $row->getData('template_product_tax_code_id'),
            ]);

            $templateTitle = $this->dataHelper->escapeHtml($row->getData('template_product_tax_code_title'));

            return <<<HTML
<a target="_blank" href="{$url}">{$templateTitle}</a>
HTML;
        }

        return $html;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            [
                ['attribute' => 'sku', 'like' => '%' . $value . '%'],
                ['attribute' => 'name', 'like' => '%' . $value . '%'],
            ]
        );
    }

    protected function callbackFilterGeneralId($collection, $column)
    {
        $inputValue = $column->getFilter()->getValue('input');
        if ($inputValue !== null) {
            $collection->addFieldToFilter('general_id', ['like' => '%' . $inputValue . '%']);
        }

        $selectValue = $column->getFilter()->getValue('select');
        if ($selectValue !== null) {
            $collection->addFieldToFilter('is_general_id_owner', $selectValue);
        }
    }

    protected function callbackFilterShippingSettings($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $inputValue = null;

        if (is_array($value) && isset($value['input'])) {
            $inputValue = $value['input'];
        } elseif (is_string($value)) {
            $inputValue = $value;
        }

        if ($inputValue !== null) {
            /** @var \Ess\M2ePro\Model\ResourceModel\Magento\Product\Collection $collection */
            $collection->addAttributeToFilter('template_shipping_title', ['like' => '%' . $inputValue . '%']);
        }

        if (isset($value['select'])) {
            switch ($value['select']) {
                case '0':
                    $collection->addAttributeToFilter('template_shipping_id', ['null' => true]);
                    break;
                case '1':
                    $collection->addAttributeToFilter('template_shipping_id', ['notnull' => true]);
                    break;
            }
        }
    }

    protected function callbackFilterProductType($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        $inputValue = null;

        if (is_array($value) && isset($value['input'])) {
            $inputValue = $value['input'];
        } elseif (is_string($value)) {
            $inputValue = $value;
        }

        if ($inputValue !== null) {
            /** @var \Ess\M2ePro\Model\ResourceModel\Magento\Product\Collection $collection */
            $collection->addAttributeToFilter(
                'product_type_title',
                ['like' => '%' . $inputValue . '%']
            );
        }

        if (isset($value['select'])) {
            switch ($value['select']) {
                case '0':
                    $collection->addAttributeToFilter('template_product_type_id', ['null' => true]);
                    break;
                case '1':
                    $collection->addAttributeToFilter('template_product_type_id', ['notnull' => true]);
                    break;
            }
        }
    }

    public function getRowUrl($item)
    {
        return false;
    }

    protected function _toHtml()
    {
        $this->js->add(
            <<<JS
    require([
        'M2ePro/Amazon/Listing/Transferring'
    ],function() {
        window.AmazonListingTransferringObj = new AmazonListingTransferring(
            {$this->listing->getId()}
        );
    });
JS
        );

        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->js->add(
                <<<JS
    ListingGridObj.afterInitPage();
JS
            );
        }

        return parent::_toHtml();
    }
}
