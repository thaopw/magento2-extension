<?php

declare(strict_types=1);

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Listing\AutoAction\Mode\AdvancedFilter;

class Grid extends \Ess\M2ePro\Block\Adminhtml\Listing\AutoAction\Mode\AdvancedFilter\AbstractGrid
{
    private \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter\CollectionFactory $autoFilterCollectionFactory;
    private \Ess\M2ePro\Helper\Component\Ebay\Category $componentEbayCategory;
    private \Ess\M2ePro\Model\ResourceModel\Ebay\Template\Category $ebayTemplateCategoryResource;
    private \Ess\M2ePro\Model\ResourceModel\Ebay\Template\StoreCategory $ebayTemplateStoreCategoryResource;

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter\CollectionFactory $autoFilterCollectionFactory,
        \Ess\M2ePro\Helper\Component\Ebay\Category $componentEbayCategory,
        \Ess\M2ePro\Model\ResourceModel\Ebay\Template\Category $ebayTemplateCategoryResource,
        \Ess\M2ePro\Model\ResourceModel\Ebay\Template\StoreCategory $ebayTemplateStoreCategoryResource,
        \Ess\M2ePro\Model\Listing $listing,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        parent::__construct($listing, $context, $backendHelper, $data);
        $this->autoFilterCollectionFactory = $autoFilterCollectionFactory;
        $this->componentEbayCategory = $componentEbayCategory;
        $this->ebayTemplateCategoryResource = $ebayTemplateCategoryResource;
        $this->ebayTemplateStoreCategoryResource = $ebayTemplateStoreCategoryResource;
    }

    protected function _prepareCollection()
    {
        $collection = $this->autoFilterCollectionFactory->createWithEbayChildMode();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Listing\Auto\Advanced\Filter::COLUMN_LISTING_ID,
            ['eq' => (int)$this->getListing()->getId()]
        );
        $collection->joinLeft(
            ['primary_category' => $this->ebayTemplateCategoryResource->getMainTable()],
            '`primary_category`.`id` = `second_table`.`adding_template_category_id`',
            [
                'primary_category_id' => 'category_id',
                'primary_category_path' => 'category_path',
            ]
        );
        $collection->joinLeft(
            ['secondary_category' => $this->ebayTemplateCategoryResource->getMainTable()],
            '`secondary_category`.`id` = `second_table`.`adding_template_category_secondary_id`',
            [
                'secondaary_category_id' => 'category_id',
                'secondaary_category_path' => 'category_path',
            ]
        );

        $collection->joinLeft(
            ['primary_store_category' => $this->ebayTemplateStoreCategoryResource->getMainTable()],
            '`primary_store_category`.`id` = `second_table`.`adding_template_store_category_id`',
            [
                'primary_store_category_id' => 'category_id',
                'primary_store_category_path' => 'category_path',
            ]
        );
        $collection->joinLeft(
            ['secondary_store_category' => $this->ebayTemplateStoreCategoryResource->getMainTable()],
            '`secondary_store_category`.`id` = `second_table`.`adding_template_store_category_secondary_id`',
            [
                'secondary_store_category_id' => 'category_id',
                'secondary_store_category_path' => 'category_path',
            ]
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', [
            'header' => __('Title'),
            'align' => 'left',
            'type' => 'text',
            'escape' => true,
            'index' => 'title',
            'filter_index' => 'title',
        ]);

        $this->addColumn('filters', [
            'header' => $this->__('Advanced Filter'),
            'align' => 'left',
            'type' => 'text',
            'sortable' => false,
            'filter' => false,
            'frame_callback' => [$this, 'callbackColumnFilters'],
        ]);

        $this->addColumn('categories', [
            'header' => $this->__('eBay Categories'),
            'align' => 'left',
            'type' => 'text',
            'sortable' => false,
            'filter' => false,
            'frame_callback' => [$this, 'callbackColumnEbayCategories'],
        ]);

        $this->addColumn('action', [
            'header' => $this->__('Actions'),
            'align' => 'left',
            'type' => 'text',
            'sortable' => false,
            'filter' => false,
            'actions' => [
                0 => [
                    'label' => __('Edit Rule'),
                    'value' => 'advancedFilterStepOne',
                ],
                1 => [
                    'label' => __('Delete Rule'),
                    'value' => 'advancedFilterDelete',
                ],
            ],
            'frame_callback' => [$this, 'callbackColumnActions'],
        ]);

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/ebay_listing_autoAction/getAdvancedFilterGrid', ['_current' => true]);
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------
    }

    /**
     * @param $value
     * @param \Ess\M2ePro\Model\Listing\Auto\Advanced\Filter $row
     * @param $column
     * @param $isExport
     *
     * @return string
     */
    public function callbackColumnEbayCategories($value, $row, $column, $isExport): string
    {
        $html = '';

        $categoryTitles = $this->componentEbayCategory->getCategoryTitles();
        $categoryConfiguration = [
            [
                'type' => \Ess\M2ePro\Helper\Component\Ebay\Category::TYPE_EBAY_MAIN,
                'title' => $categoryTitles[\Ess\M2ePro\Helper\Component\Ebay\Category::TYPE_EBAY_MAIN],
                'id_key' => 'primary_category_id',
                'path_key' => 'primary_category_path',
            ],
            [
                'type' => \Ess\M2ePro\Helper\Component\Ebay\Category::TYPE_EBAY_SECONDARY,
                'title' => $categoryTitles[\Ess\M2ePro\Helper\Component\Ebay\Category::TYPE_EBAY_SECONDARY],
                'id_key' => 'secondary_category_id',
                'path_key' => 'secondary_category_path',
            ],
            [
                'type' => \Ess\M2ePro\Helper\Component\Ebay\Category::TYPE_STORE_MAIN,
                'title' => $categoryTitles[\Ess\M2ePro\Helper\Component\Ebay\Category::TYPE_EBAY_MAIN],
                'id_key' => 'primary_store_category_id',
                'path_key' => 'primary_store_category_path',
            ],
            [
                'type' => \Ess\M2ePro\Helper\Component\Ebay\Category::TYPE_STORE_SECONDARY,
                'title' => $categoryTitles[\Ess\M2ePro\Helper\Component\Ebay\Category::TYPE_EBAY_MAIN],
                'id_key' => 'secondary_store_category_id',
                'path_key' => 'secondary_store_category_path',
            ],
        ];

        foreach ($categoryConfiguration as $config) {
            $categoryId = $row->getData($config['id_key']);
            $categoryPath = $row->getData($config['path_key']);

            if (!empty($categoryId) && !empty($categoryPath)) {
                $titleHtml = sprintf(
                    '<span style="text-decoration: underline">%s</span>',
                    $config['title']
                );
                $categoryHtml = sprintf(
                    '<p style="padding: 2px 0 0 10px">%s (%s)</p>',
                    $categoryPath,
                    $categoryId
                );

                $html .= sprintf('<div>%s%s</div>', $titleHtml, $categoryHtml);
            }
        }

        return $html;
    }

    /**
     * @param $value
     * @param \Ess\M2ePro\Model\Listing\Auto\Advanced\Filter $row
     * @param $column
     * @param $isExport
     *
     * @return string
     */
    public function callbackColumnFilters($value, $row, $column, $isExport): string
    {
        $condition = json_decode($row->getCondition(), true);

        return $this->renderMagentoConditionsHtml($condition);
    }

    private function renderMagentoConditionsHtml(array $node): string
    {
        $operators = [
            '==' => __('is'),
            '!=' => __('is not'),
            '>=' => __('equals or greater than'),
            '<=' => __('equals or less than'),
            '>' => __('greater than'),
            '<' => __('less than'),
            '{}' => __('contains'),
            '!{}' => __('does not contain'),
            '()' => __('is one of'),
            '!()' => __('is not one of'),
            '??' => __('is empty'),
            '!??' => __('is not empty'),
        ];

        $html = '';

        $rulePrefix = \Ess\M2ePro\Model\Listing\Auto\Advanced\Filter::RULE_MODEL_PREFIX;
        if (isset($node['aggregator'])) {
            $aggregator = strtoupper($node['aggregator']);
            $label = __('If <strong>%aggregator</strong> of these Conditions are <strong>%aggregator_value</strong>', [
                'aggregator' => $aggregator,
                'aggregator_value' => $node['value'] ? __('TRUE') : __('FALSE'),
            ]);

            $html .= "<div>";
            $html .= "<span>$label</span>";

            if (
                !empty($node[$rulePrefix])
                && is_array($node[$rulePrefix])
            ) {
                $html .= "<ul class='rule-list'>";
                foreach ($node[$rulePrefix] as $childNode) {
                    $html .= "<li>" . $this->renderMagentoConditionsHtml($childNode) . "</li>";
                }
                $html .= "</ul>";
            }
            $html .= "</div>";
        } elseif (isset($node['attribute'])) {
            $attributeCode = $this->_escaper->escapeHtml($node['attribute']);
            $operatorCode = $node['operator'];
            $operator = $operators[$operatorCode] ?? $operatorCode;
            $value = $node['value'];

            if (is_array($value)) {
                $value = implode(
                    ', ',
                    array_map(
                        fn($item) => $this->_escaper->escapeHtml($item),
                        $value
                    )
                );
            } else {
                $value = $this->_escaper->escapeHtml($value);
            }

            $html .= "<div class='rule-item'>";
            $html .= "<span class='rule-attribute'>$attributeCode</span> ";
            $html .= "<span class='rule-operator' style='font-weight: bold'>$operator</span> ";
            $html .= "<span class='rule-value'>$value</span>";
            $html .= "</div>";
        }

        return $html;
    }

    public function callbackColumnActions($value, $row, $column, $isExport)
    {
        $actions = $column->getActions();
        $id = (int)$row->getData('id');

        if (count($actions) == 1) {
            $action = reset($actions);
            $onclick = 'ListingAutoActionObj[\'' . $action['value'] . '\'](' . $id . ');';

            return '<a href="javascript: void(0);" onclick="' . $onclick . '">' . $action['label'] . '</a>';
        }

        $optionsHtml = '<option></option>';

        foreach ($actions as $option) {
            $optionsHtml .= <<<HTML
            <option value="{$option['value']}">{$option['label']}</option>
HTML;
        }

        return <<<HTML
<div style="padding: 5px;">
    <select class="admin__control-select"
            style="margin: auto; display: block;"
            onchange="ListingAutoActionObj[this.value]({$id});">
        {$optionsHtml}
    </select>
</div>
HTML;
    }
}
