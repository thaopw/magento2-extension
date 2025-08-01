<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Listing\Unmanaged;

use Ess\M2ePro\Block\Adminhtml\Ebay\Grid\Column\Renderer\Qty as OnlineQty;

class Grid extends \Ess\M2ePro\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    /** @var \Magento\Framework\Locale\CurrencyInterface */
    protected $localeCurrency;

    /** @var \Magento\Framework\App\ResourceConnection */
    protected $resourceConnection;

    /** @var \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory */
    protected $ebayFactory;

    /** @var \Ess\M2ePro\Helper\Data */
    private $dataHelper;

    /** @var \Ess\M2ePro\Helper\Component\Ebay */
    private $ebayHelper;

    public function __construct(
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ess\M2ePro\Helper\Data $dataHelper,
        \Ess\M2ePro\Helper\Component\Ebay $ebayHelper,
        array $data = []
    ) {
        $this->localeCurrency = $localeCurrency;
        $this->resourceConnection = $resourceConnection;
        $this->ebayFactory = $ebayFactory;
        $this->dataHelper = $dataHelper;
        $this->ebayHelper = $ebayHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingUnmanagedGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/ebay_listing_unmanaged/index', ['_current' => true]);
    }

    protected function _prepareCollection()
    {
        $collection = $this->ebayFactory->getObject('Listing\Other')->getCollection();

        $collection->getSelect()->joinLeft(
            ['mp' => $this->activeRecordFactory->getObject('Marketplace')->getResource()->getMainTable()],
            'mp.id = main_table.marketplace_id',
            ['marketplace_title' => 'mp.title']
        );

        $collection->getSelect()->joinLeft(
            ['mea' => $this->activeRecordFactory->getObject('Ebay\Account')->getResource()->getMainTable()],
            'mea.account_id = main_table.account_id',
            ['account_mode' => 'mea.mode']
        );

        if ($accountId = $this->getRequest()->getParam('ebayAccount')) {
            $collection->addFieldToFilter('main_table.account_id', $accountId);
        }

        if ($marketplaceId = $this->getRequest()->getParam('ebayMarketplace')) {
            $collection->addFieldToFilter('main_table.marketplace_id', $marketplaceId);
        }

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS);
        $collection->getSelect()->columns(
            [
                'id' => 'main_table.id',
                'account_id' => 'main_table.account_id',
                'marketplace_id' => 'main_table.marketplace_id',
                'product_id' => 'main_table.product_id',
                'title' => 'second_table.title',
                'sku' => 'second_table.sku',
                'item_id' => 'second_table.item_id',
                'available_qty' => new \Zend_Db_Expr(
                    '(CAST(second_table.online_qty AS SIGNED) - CAST(second_table.online_qty_sold AS SIGNED))'
                ),
                'online_qty_sold' => 'second_table.online_qty_sold',
                'online_price' => 'second_table.online_price',
                'online_main_category' => 'second_table.online_main_category',
                'status' => 'main_table.status',
                'start_date' => 'second_table.start_date',
                'end_date' => 'second_table.end_date',
                'currency' => 'second_table.currency',
                'account_mode' => 'mea.mode',
            ]
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addExportType('*/*/exportCsvUnmanagedGrid', __('CSV'));

        $this->addColumn('product_id', [
            'header' => $this->__('Product ID'),
            'align' => 'left',
            'type' => 'number',
            'width' => '80px',
            'index' => 'product_id',
            'filter_index' => 'main_table.product_id',
            'frame_callback' => [$this, 'callbackColumnProductId'],
            'filter' => \Ess\M2ePro\Block\Adminhtml\Grid\Column\Filter\ProductId::class,
            'filter_condition_callback' => [$this, 'callbackFilterProductId'],
        ]);

        $this->addColumn('title', [
            'header' => $this->__('Product Title / Product SKU / eBay Category'),
            'header_export' => __('Product SKU'),
            'align' => 'left',
            'type' => 'text',
            'index' => 'title',
            'escape' => false,
            'filter_index' => 'second_table.title',
            'frame_callback' => [$this, 'callbackColumnProductTitle'],
            'filter_condition_callback' => [$this, 'callbackFilterTitle'],
        ]);

        $this->addColumn('item_id', [
            'header' => $this->__('Item ID'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'text',
            'index' => 'item_id',
            'filter_index' => 'second_table.item_id',
            'frame_callback' => [$this, 'callbackColumnItemId'],
        ]);

        $this->addColumn('available_qty', [
            'header' => $this->__('Available QTY'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'number',
            'index' => 'available_qty',
            'filter_index' => new \Zend_Db_Expr(
                '(CAST(second_table.online_qty AS SIGNED) - CAST(second_table.online_qty_sold AS SIGNED))'
            ),
            'renderer' => OnlineQty::class,
            'render_online_qty' => OnlineQty::ONLINE_AVAILABLE_QTY,
        ]);

        $this->addColumn('online_qty_sold', [
            'header' => $this->__('Sold QTY'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'number',
            'index' => 'online_qty_sold',
            'filter_index' => 'second_table.online_qty_sold',
            'renderer' => OnlineQty::class,
        ]);

        $this->addColumn('online_price', [
            'header' => $this->__('Price'),
            'align' => 'right',
            'width' => '50px',
            'type' => 'number',
            'index' => 'online_price',
            'filter_index' => 'second_table.online_price',
            'frame_callback' => [$this, 'callbackColumnOnlinePrice'],
        ]);

        $this->addColumn('status', [
            'header' => $this->__('Status'),
            'width' => '100px',
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type' => 'options',
            'sortable' => false,
            'options' => [
                \Ess\M2ePro\Model\Listing\Product::STATUS_LISTED => $this->__('Listed'),
                \Ess\M2ePro\Model\Listing\Product::STATUS_HIDDEN => $this->__('Listed (Hidden)'),
                \Ess\M2ePro\Model\Listing\Product::STATUS_BLOCKED => $this->__('Pending'),
                \Ess\M2ePro\Model\Listing\Product::STATUS_INACTIVE => __('Inactive'),
            ],
            'frame_callback' => [$this, 'callbackColumnStatus'],
        ]);

        $this->addColumn('end_date', [
            'header' => $this->__('End Date'),
            'align' => 'right',
            'width' => '150px',
            'type' => 'datetime',
            'filter' => \Ess\M2ePro\Block\Adminhtml\Magento\Grid\Column\Filter\Datetime::class,
            'filter_time' => true,
            'index' => 'end_date',
            'filter_index' => 'second_table.end_date',
            'renderer' => \Ess\M2ePro\Block\Adminhtml\Ebay\Grid\Column\Renderer\DateTime::class,
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set mass-action identifiers
        // ---------------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        $this->getMassactionBlock()->setGroups([
            'mapping' => $this->__('Linking'),
            'other' => $this->__('Other'),
        ]);

        $this->getMassactionBlock()->addItem('autoMapping', [
            'label' => $this->__('Link Item(s) Automatically'),
            'url' => '',
        ], 'mapping');

        $this->getMassactionBlock()->addItem('createProduct', [
            'label' => $this->__('Create Magento Product And Link Item(s)'),
            'url' => '',
        ], 'mapping');

        $this->getMassactionBlock()->addItem('moving', [
            'label' => $this->__('Move Item(s) to Listing'),
            'url' => '',
        ], 'other');
        $this->getMassactionBlock()->addItem('removing', [
            'label' => $this->__('Remove Item(s) from eBay'),
            'url' => '',
        ], 'other');
        $this->getMassactionBlock()->addItem('unmapping', [
            'label' => $this->__('Unlink Item(s)'),
            'url' => '',
        ], 'mapping');

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('listing/other/view/grid.css');

        return parent::_prepareLayout();
    }

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            if ($isExport) {
                return '';
            }

            $productTitle = $row->getChildObject()->getData('title');
            if (strlen($productTitle) > 60) {
                $productTitle = substr($productTitle, 0, 60) . '...';
            }
            $productTitle = $this->dataHelper->escapeHtml($productTitle);
            $productTitle = $this->dataHelper->escapeJs($productTitle);

            $htmlValue = '&nbsp;<a href="javascript:void(0);"
                                    onclick="ListingOtherMappingObj.openPopUp(
                                    ' . (int)$row->getId() . ',
                                    \'' . $productTitle . '\'
                                    );">' . $this->__('Link') . '</a>';

            return $htmlValue;
        }

        if ($isExport) {
            return $row->getData('product_id');
        }

        $htmlValue = '&nbsp<a href="'
            . $this->getUrl(
                'catalog/product/edit',
                ['id' => $row->getData('product_id')]
            )
            . '" target="_blank">'
            . $row->getData('product_id')
            . '</a>';

        $htmlValue .= '&nbsp&nbsp&nbsp<a href="javascript:void(0);"'
            . ' onclick="EbayListingOtherGridObj.movingHandler.getGridHtml('
            . \Ess\M2ePro\Helper\Json::encode([(int)$row->getData('id')])
            . ')">'
            . $this->__('Move')
            . '</a>';

        return $htmlValue;
    }

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        $title = $row->getChildObject()->getData('title');

        $titleSku = __('SKU');

        $tempSku = $row->getChildObject()->getData('sku');
        if ($tempSku === null) {
            $tempSku = '<i style="color:gray;">receiving...</i>';
        } elseif ($tempSku == '') {
            $tempSku = '<i style="color:gray;">none</i>';
        } else {
            $tempSku = $this->dataHelper->escapeHtml($tempSku);
        }

        if ($isExport) {
            return strip_tags($tempSku);
        }

        $categoryHtml = '';
        if ($category = $row->getChildObject()->getData('online_main_category')) {
            $categoryHtml = <<<HTML
<strong>{$this->__('Category')}:</strong>&nbsp;
{$this->dataHelper->escapeHtml($category)}
HTML;
        }

        $additionalInfo = $this->getProductTitleAdditionalInfo(
            $row->getAccount()->getTitle(),
            $row->getMarketplace()->getTitle(),
            $this->getRequest()->getParam('ebayAccount') === null,
            $this->getRequest()->getParam('ebayMarketplace') === null
        ) ?? '';

        return <<<HTML
<span>{$this->dataHelper->escapeHtml($title)}</span><br/>
<strong>{$titleSku}:</strong>&nbsp;<span class="white-space-pre-wrap">{$tempSku}</span><br/>
{$categoryHtml}
{$additionalInfo}
HTML;
    }

    private function getProductTitleAdditionalInfo(
        string $accountTitle,
        string $marketplaceTitle,
        bool $accountUnfiltered,
        bool $marketplaceUnfiltered
    ): ?string {
        if ($accountUnfiltered && $marketplaceUnfiltered) {
            return sprintf(
                '<br/><strong>%s:</strong> %s, <strong>%s:</strong> %s',
                __('Account'),
                $accountTitle,
                __('Marketplace'),
                $marketplaceTitle
            );
        }

        if ($accountUnfiltered) {
            return sprintf('<br/><strong>%s:</strong> %s', __('Account'), $accountTitle);
        }

        if ($marketplaceUnfiltered) {
            return sprintf('<br/><strong>%s:</strong> %s', __('Marketplace'), $marketplaceTitle);
        }

        return null;
    }

    public function callbackColumnItemId($value, $row, $column, $isExport)
    {
        $value = $row->getChildObject()->getData('item_id');

        if ($isExport) {
            return $value;
        }

        if (empty($value)) {
            return $this->__('N/A');
        }

        $url = $this->ebayHelper->getItemUrl(
            $row->getChildObject()->getData('item_id'),
            $row->getData('account_mode'),
            $row->getData('marketplace_id')
        );
        $value = '<a href="' . $url . '" target="_blank">' . $value . '</a>';

        return $value;
    }

    public function callbackColumnOnlinePrice($value, $row, $column, $isExport)
    {
        $value = $row->getChildObject()->getData('online_price');
        if ($value === null || $value === '') {
            if ($isExport) {
                return '';
            }

            return $this->__('N/A');
        }

        if ((float)$value <= 0) {
            if ($isExport) {
                return 0;
            }

            return '<span style="color: #f00;">0</span>';
        }

        return $this->localeCurrency->getCurrency($row->getChildObject()->getData('currency'))->toCurrency($value);
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        if ($isExport) {
            return $value;
        }

        $coloredStatuses = [
            \Ess\M2ePro\Model\Listing\Product::STATUS_LISTED => 'green',
            \Ess\M2ePro\Model\Listing\Product::STATUS_HIDDEN => 'red',
            \Ess\M2ePro\Model\Listing\Product::STATUS_BLOCKED => 'orange',
            \Ess\M2ePro\Model\Listing\Product::STATUS_INACTIVE => 'red',
        ];

        $status = $row->getData('status');

        if ($status !== null && isset($coloredStatuses[$status])) {
            $value = '<span style="color: ' . $coloredStatuses[$status] . ';">' . $value . '</span>';
        }

        return $value . $this->getLockedTag($row);
    }

    public function callbackColumnStartTime($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return $this->__('N/A');
        }

        return $value;
    }

    protected function callbackFilterProductId($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= 'product_id >= ' . (int)$value['from'];
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= 'product_id <= ' . (int)$value['to'];
        }

        if (isset($value['is_mapped']) && $value['is_mapped'] !== '') {
            if (!empty($where)) {
                $where = '(' . $where . ') AND ';
            }

            if ($value['is_mapped']) {
                $where .= 'product_id IS NOT NULL';
            } else {
                $where .= 'product_id IS NULL';
            }
        }

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'second_table.title LIKE ? OR
             second_table.sku LIKE ? OR
              second_table.online_main_category LIKE ?',
            '%' . $value . '%'
        );
    }

    private function getLockedTag($row)
    {
        /** @var \Ess\M2ePro\Model\Listing\Other $listingOther */
        $listingOther = $this->ebayFactory->getObjectLoaded('Listing\Other', (int)$row['id']);
        $processingLocks = $listingOther->getProcessingLocks();

        $html = '';

        foreach ($processingLocks as $processingLock) {
            switch ($processingLock->getTag()) {
                case 'relist_action':
                    $html .= '<br/><span style="color: #605fff">[Relist in Progress...]</span>';
                    break;

                case 'revise_action':
                    $html .= '<br/><span style="color: #605fff">[Revise in Progress...]</span>';
                    break;

                case 'stop_action':
                    $html .= '<br/><span style="color: #605fff">[Stop in Progress...]</span>';
                    break;

                default:
                    break;
            }
        }

        return $html;
    }

    protected function _beforeToHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest() || $this->getRequest()->getParam('isAjax')) {
            $this->js->addRequireJs(
                [
                'jQuery' => 'jquery',
                ],
                <<<JS

            EbayListingOtherGridObj.afterInitPage();
JS
            );
        }

        return parent::_beforeToHtml();
    }

    public function getRowUrl($item)
    {
        return false;
    }
}
