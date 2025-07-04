<?php

namespace Ess\M2ePro\Model\Walmart;

use Magento\Sales\Model\Order\Creditmemo;
use Ess\M2ePro\Model\ResourceModel\Walmart\Order as ResourceWalmartOrder;

/**
 * @method \Ess\M2ePro\Model\Order getParentObject()
 * @method \Ess\M2ePro\Model\ResourceModel\Walmart\Order getResource()
 */
class Order extends \Ess\M2ePro\Model\ActiveRecord\Component\Child\Walmart\AbstractModel
{
    public const STATUS_CREATED = 0;
    public const STATUS_UNSHIPPED = 1;
    public const STATUS_SHIPPED_PARTIALLY = 2;
    public const STATUS_SHIPPED = 3;
    public const STATUS_CANCELED = 5;

    /** @var \Ess\M2ePro\Model\Magento\Order\ShipmentFactory */
    private $shipmentFactory;

    private $subTotalPrice = null;
    private $grandTotalPrice = null;

    /** @var \Ess\M2ePro\Model\Walmart\Order\ShippingAddressFactory */
    protected $shippingAddressFactory;
    /** @var \Magento\Sales\Model\Order\Email\Sender\OrderSender */
    private $orderSender;
    /** @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender */
    private $invoiceSender;
    /** @var \Ess\M2ePro\Model\ResourceModel\Walmart\Listing\Other */
    private $listingOtherResourceModel;

    public function __construct(
        \Ess\M2ePro\Model\Magento\Order\ShipmentFactory $shipmentFactory,
        \Ess\M2ePro\Model\Walmart\Order\ShippingAddressFactory $shippingAddressFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Walmart\Factory $walmartFactory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Factory $parentFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Ess\M2ePro\Model\ResourceModel\Walmart\Listing\Other $listingOtherResourceModel,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->shipmentFactory = $shipmentFactory;
        $this->shippingAddressFactory = $shippingAddressFactory;
        $this->orderSender = $orderSender;
        $this->invoiceSender = $invoiceSender;
        $this->listingOtherResourceModel = $listingOtherResourceModel;

        parent::__construct(
            $walmartFactory,
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
    }

    public function _construct()
    {
        parent::_construct();

        $this->_init(\Ess\M2ePro\Model\ResourceModel\Walmart\Order::class);
    }

    /**
     * @return \Ess\M2ePro\Model\Walmart\Order\ProxyObject
     */
    public function getProxy()
    {
        return $this->modelFactory->getObject('Walmart_Order_ProxyObject', ['order' => $this]);
    }

    /**
     * @return \Ess\M2ePro\Model\Walmart\Account
     */
    public function getWalmartAccount()
    {
        return $this->getParentObject()->getAccount()->getChildObject();
    }

    public function getWalmartOrderId()
    {
        return $this->getData('walmart_order_id');
    }

    public function getBuyerName()
    {
        return $this->getData('buyer_name');
    }

    public function getBuyerEmail()
    {
        return $this->getData('buyer_email');
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    public function getShippingService()
    {
        return $this->getData('shipping_service');
    }

    /**
     * @return float
     */
    public function getShippingPrice()
    {
        return (float)$this->getData('shipping_price');
    }

    /**
     * @return \Ess\M2ePro\Model\Walmart\Order\ShippingAddress
     */
    public function getShippingAddress()
    {
        $address = \Ess\M2ePro\Helper\Json::decode($this->getData('shipping_address'));

        return $this->shippingAddressFactory->create(
            [
                'order' => $this->getParentObject(),
            ]
        )->setData($address);
    }

    public function getShippingDateTo()
    {
        return $this->getData('shipping_date_to');
    }

    /**
     * @return float
     */
    public function getPaidAmount()
    {
        return (float)$this->getData('paid_amount');
    }

    /**
     * @return array
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getTaxDetails()
    {
        return $this->getSettings('tax_details');
    }

    /**
     * @return float
     */
    public function getProductPriceTaxAmount()
    {
        $taxDetails = $this->getTaxDetails();

        return !empty($taxDetails['product']) ? (float)$taxDetails['product'] : 0.0;
    }

    /**
     * @return float
     */
    public function getShippingPriceTaxAmount()
    {
        $taxDetails = $this->getTaxDetails();

        return !empty($taxDetails['shipping']) ? (float)$taxDetails['shipping'] : 0.0;
    }

    /**
     * @return float|int
     */
    public function getProductPriceTaxRate()
    {
        $taxAmount = $this->getProductPriceTaxAmount();
        if ($taxAmount <= 0) {
            return 0;
        }

        if ($this->getSubtotalPrice() <= 0) {
            return 0;
        }

        $taxRate = ($taxAmount / $this->getSubtotalPrice()) * 100;

        return round($taxRate, 4);
    }

    /**
     * @return float|int
     */
    public function getShippingPriceTaxRate()
    {
        $taxAmount = $this->getShippingPriceTaxAmount();
        if ($taxAmount <= 0) {
            return 0;
        }

        if ($this->getShippingPrice() <= 0) {
            return 0;
        }

        $taxRate = ($taxAmount / $this->getShippingPrice()) * 100;

        return round($taxRate, 4);
    }

    /**
     * @return bool
     */
    public function isCreated()
    {
        return $this->getStatus() == self::STATUS_CREATED;
    }

    /**
     * @return bool
     */
    public function isUnshipped()
    {
        return $this->getStatus() == self::STATUS_UNSHIPPED;
    }

    /**
     * @return bool
     */
    public function isPartiallyShipped()
    {
        return $this->getStatus() == self::STATUS_SHIPPED_PARTIALLY;
    }

    /**
     * @return bool
     */
    public function isShipped()
    {
        return $this->getStatus() == self::STATUS_SHIPPED;
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return $this->getStatus() == self::STATUS_CANCELED;
    }

    /**
     * @return float|null
     */
    public function getSubtotalPrice()
    {
        if ($this->subTotalPrice === null) {
            $this->subTotalPrice = $this->getResource()->getItemsTotal($this->getId());
        }

        return $this->subTotalPrice;
    }

    /**
     * @return float
     */
    public function getGrandTotalPrice()
    {
        if ($this->grandTotalPrice === null) {
            $this->grandTotalPrice = $this->getSubtotalPrice();
            $this->grandTotalPrice += $this->getProductPriceTaxAmount();
            $this->grandTotalPrice += $this->getShippingPrice();
            $this->grandTotalPrice += $this->getShippingPriceTaxAmount();
        }

        return round($this->grandTotalPrice, 2);
    }

    public function getStatusForMagentoOrder()
    {
        $status = '';
        $this->isUnshipped() && $status = $this->getWalmartAccount()->getMagentoOrdersStatusProcessing();
        $this->isPartiallyShipped() && $status = $this->getWalmartAccount()->getMagentoOrdersStatusProcessing();
        $this->isShipped() && $status = $this->getWalmartAccount()->getMagentoOrdersStatusShipped();

        return $status;
    }

    /**
     * @return int|null
     */
    public function getAssociatedStoreId()
    {
        $channelItems = $this->getParentObject()->getChannelItems();

        if (empty($channelItems)) {
            $storeId = $this->getWalmartAccount()->getMagentoOrdersListingsOtherStoreId();
        } else {
            /** @var \Ess\M2ePro\Model\Walmart\Item $firstChannelItem */
            $firstChannelItem = reset($channelItems);
            $itemIsFromOtherListing = $this->listingOtherResourceModel->isItemFromOtherListing(
                $firstChannelItem->getProductId(),
                $firstChannelItem->getAccountId(),
                $firstChannelItem->getMarketplaceId()
            );

            if ($itemIsFromOtherListing) {
                $storeId = $this->getWalmartAccount()->getMagentoOrdersListingsOtherStoreId();
            } elseif ($this->getWalmartAccount()->isMagentoOrdersListingsStoreCustom()) {
                $storeId = $this->getWalmartAccount()->getMagentoOrdersListingsStoreId();
            } else {
                $storeId = $firstChannelItem->getStoreId();
            }
        }

        if ($this->isWalmartFulfillment() && $this->getWalmartAccount()->isMagentoOrdersWfsStoreModeEnabled()) {
            $storeId = $this->getWalmartAccount()->getMagentoOrdersWfsStoreId();
        }

        if ($storeId == 0) {
            $storeId = $this->getHelper('Magento\Store')->getDefaultStoreId();
        }

        return $storeId;
    }

    public function isReservable(): bool
    {
        if (
            $this->isWalmartFulfillment() &&
            (!$this->getWalmartAccount()->isMagentoOrdersWfsModeEnabled() ||
                !$this->getWalmartAccount()->isMagentoOrdersWfsStockEnabled())
        ) {
            return false;
        }

        return true;
    }

    public function getPurchaseCreateDate(): string
    {
        return $this->getDataByKey(ResourceWalmartOrder::COLUMN_PURCHASE_CREATE_DATE);
    }

    public function isWalmartFulfillment(): bool
    {
        return (bool)$this->getData('is_wfs');
    }

    /**
     * Check possibility for magento order creation
     * @return bool
     */
    public function canCreateMagentoOrder()
    {
        if ($this->isCanceled()) {
            return false;
        }

        if (
            $this->isWalmartFulfillment()
            && !$this->getWalmartAccount()->isMagentoOrdersWfsModeEnabled()
        ) {
            return false;
        }

        return true;
    }

    public function canAcknowledgeOrder(): bool
    {
        if ($this->isWalmartFulfillment()) {
            return false;
        }

        foreach ($this->getParentObject()->getItemsCollection()->getItems() as $item) {
            /**@var \Ess\M2ePro\Model\Walmart\Order\Item $item */
            if (!$item->canCreateMagentoOrder()) {
                return false;
            }
        }

        return true;
    }

    public function beforeCreateMagentoOrder()
    {
        if ($this->isCanceled()) {
            throw new \Ess\M2ePro\Model\Exception(
                'Magento Order Creation is not allowed for canceled Walmart Orders.'
            );
        }
    }

    public function afterCreateMagentoOrder()
    {
        if ($this->getWalmartAccount()->isMagentoOrdersCustomerNewNotifyWhenOrderCreated()) {
            $this->orderSender->send($this->getParentObject()->getMagentoOrder());
        }

        if ($this->isWalmartFulfillment() && !$this->getWalmartAccount()->isMagentoOrdersWfsStockEnabled()) {
            $this->_eventManager->dispatch('ess_walmart_wfs_magento_order_place_after', [
                'magento_order' => $this->getParentObject()->getMagentoOrder(),
            ]);
        }
    }

    /**
     * @return bool
     */
    public function canCreateInvoice()
    {
        if (!$this->getWalmartAccount()->isMagentoOrdersInvoiceEnabled()) {
            return false;
        }

        if ($this->isCanceled()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if ($magentoOrder->hasInvoices() || !$magentoOrder->canInvoice()) {
            return false;
        }

        return true;
    }

    /**
     * @return \Magento\Sales\Model\Order\Invoice|null
     * @throws \Exception
     */
    public function createInvoice()
    {
        if (!$this->canCreateInvoice()) {
            return null;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();

        // Create invoice
        // ---------------------------------------
        /** @var \Ess\M2ePro\Model\Magento\Order\Invoice $invoiceBuilder */
        $invoiceBuilder = $this->modelFactory->getObject('Magento_Order_Invoice');
        $invoiceBuilder->setMagentoOrder($magentoOrder);
        $invoiceBuilder->buildInvoice();
        // ---------------------------------------

        $invoice = $invoiceBuilder->getInvoice();

        if ($this->getWalmartAccount()->isMagentoOrdersCustomerNewNotifyWhenInvoiceCreated()) {
            $this->invoiceSender->send($invoice);
        }

        return $invoice;
    }

    /**
     * @return bool
     */
    public function canCreateShipments()
    {
        if (!$this->getWalmartAccount()->isMagentoOrdersShipmentEnabled()) {
            return false;
        }

        if (!$this->isShipped()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if ($magentoOrder->hasShipments() || !$magentoOrder->canShip()) {
            return false;
        }

        return true;
    }

    /**
     * @return \Magento\Sales\Model\Order\Shipment[]|null
     */
    public function createShipments()
    {
        if (!$this->canCreateShipments()) {
            return null;
        }

        /** @var \Ess\M2ePro\Model\Magento\Order\Shipment $shipmentBuilder */
        $shipmentBuilder = $this->shipmentFactory->create($this->getParentObject()->getMagentoOrder());
        $shipmentBuilder->setMagentoOrder($this->getParentObject()->getMagentoOrder());
        $shipmentBuilder->buildShipments();

        return $shipmentBuilder->getShipments();
    }

    /**
     * @return bool
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    private function canCreateTracks(): bool
    {
        $trackingDetails = $this->getShippingTrackingDetails();
        if (empty($trackingDetails)) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if (!$magentoOrder->hasShipments()) {
            return false;
        }

        return true;
    }

    private function getShippingTrackingDetails(): array
    {
        /** @var \Ess\M2ePro\Model\Order\Item[] $items */
        $items = $this->getParentObject()->getItemsCollection()->getItems();

        $trackingDetails = [];
        foreach ($items as $item) {
            /** @var \Ess\M2ePro\Model\Walmart\Order\Item $walmartOrderItem */
            $walmartOrderItem = $item->getChildObject();
            $trackingDetail = $walmartOrderItem->getTrackingDetails();
            if ($trackingDetail === []) {
                continue;
            }

            $trackingDetails[$trackingDetail['number']] = $trackingDetail;
        }

        return array_values($trackingDetails);
    }

    /**
     * @return array|null
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function createTracks()
    {
        if (!$this->canCreateTracks()) {
            return null;
        }

        $tracks = [];

        try {
            /** @var \Ess\M2ePro\Model\Magento\Order\Shipment\Track $trackBuilder */
            $trackBuilder = $this->modelFactory->getObject('Magento_Order_Shipment_Track');
            $trackBuilder->setMagentoOrder($this->getParentObject()->getMagentoOrder());
            $trackBuilder->setTrackingDetails($this->getShippingTrackingDetails());
            /** @var \Ess\M2ePro\Helper\Component\Walmart $componentWalmart */
            $componentWalmart = $this->getHelper('Component\Walmart');
            $trackBuilder->setSupportedCarriers($componentWalmart->getCarriers());
            $trackBuilder->buildTracks();
            $tracks = $trackBuilder->getTracks();
        } catch (\Exception $e) {
            $this->getParentObject()->addErrorLog(
                'Tracking details were not imported. Reason: %msg%',
                ['msg' => $e->getMessage()]
            );
        }

        if (!empty($tracks)) {
            $this->getParentObject()->addSuccessLog('Tracking details were imported.');
        }

        return $tracks;
    }

    /**
     * @return bool
     */
    public function canUpdateShippingStatus()
    {
        if ($this->isCanceled()) {
            return false;
        }

        return true;
    }

    /**
     * @param array $trackingDetails
     * @param array $items
     *
     * @return bool
     */
    public function updateShippingStatus(array $trackingDetails = [], array $items = [])
    {
        if (!$this->canUpdateShippingStatus()) {
            return false;
        }

        if (empty($trackingDetails['tracking_number'])) {
            $this->getParentObject()->addInfoLog(
                'Order status was not updated to Shipped because tracking number is missing.
                Please add the valid tracking number to the order.'
            );

            return false;
        }

        if (!isset($trackingDetails['fulfillment_date'])) {
            $trackingDetails['fulfillment_date'] = $this->getHelper('Data')->getCurrentGmtDate();
        }

        if (!empty($trackingDetails['carrier_code'])) {
            $trackingDetails['carrier_title'] = $this->getHelper('Component_Walmart')->getCarrierTitle(
                $trackingDetails['carrier_code'],
                isset($trackingDetails['carrier_title']) ? $trackingDetails['carrier_title'] : ''
            );
        }

        if (!empty($trackingDetails['carrier_title'])) {
            if (
                $trackingDetails['carrier_title'] == \Ess\M2ePro\Model\Order\Shipment\Handler::CUSTOM_CARRIER_CODE &&
                !empty($trackingDetails['shipping_method'])
            ) {
                $trackingDetails['carrier_title'] = $trackingDetails['shipping_method'];

                $otherCarriers = $this->getWalmartAccount()->getOtherCarriers();
                $shippingMethod = strtolower($trackingDetails['shipping_method']);
                foreach ($otherCarriers as $otherCarrier) {
                    if (strtolower($otherCarrier['code']) === $shippingMethod) {
                        $trackingDetails['url'] = $otherCarrier['url'];
                        break;
                    }
                }
            }
        }

        $params = [
            'walmart_order_id' => $this->getWalmartOrderId(),
            'fulfillment_date' => $trackingDetails['fulfillment_date'],
            'items' => [],
        ];

        foreach ($items as $item) {
            if (!isset($item['walmart_order_item_id']) || !isset($item['qty'])) {
                continue;
            }

            if ((int)$item['qty'] <= 0) {
                continue;
            }

            $data = [
                'walmart_order_item_id' => $item['walmart_order_item_id'],
                'qty' => (int)$item['qty'],
                'tracking_details' => [
                    'ship_date' => $trackingDetails['fulfillment_date'],
                    'method' => $this->getShippingService(),
                    'carrier' => $trackingDetails['carrier_title'],
                    'number' => $trackingDetails['tracking_number'],
                ],
            ];

            if (isset($trackingDetails['url'])) {
                $data['tracking_details']['url'] = $trackingDetails['url'];
            }

            $params['items'][] = $data;
        }

        /** @var \Ess\M2ePro\Model\Order\Change $change */
        $change = $this->activeRecordFactory
            ->getObject('Order_Change')
            ->getCollection()
            ->addFieldToFilter('order_id', $this->getParentObject()->getId())
            ->addFieldToFilter('action', \Ess\M2ePro\Model\Order\Change::ACTION_UPDATE_SHIPPING)
            ->addFieldToFilter('processing_attempt_count', 0)
            ->getFirstItem();

        $existingParams = $change->getParams();

        $newTrackingNumber = !empty($trackingDetails['tracking_number']) ? $trackingDetails['tracking_number'] : '';
        $oldTrackingNumber = !empty($existingParams['items'][0]['tracking_details']['number'])
            ? $existingParams['items'][0]['tracking_details']['number']
            : '';

        if (!$change->getId() || $newTrackingNumber !== $oldTrackingNumber) {
            $this->activeRecordFactory->getObject('Order_Change')->create(
                $this->getParentObject()->getId(),
                \Ess\M2ePro\Model\Order\Change::ACTION_UPDATE_SHIPPING,
                $this->getParentObject()->getLog()->getInitiator(),
                \Ess\M2ePro\Helper\Component\Walmart::NICK,
                $params
            );

            return true;
        }

        $existingParams = $change->getParams();
        foreach ($params['items'] as $newItem) {
            foreach ($existingParams['items'] as &$existingItem) {
                if ($newItem['walmart_order_item_id'] === $existingItem['walmart_order_item_id']) {
                    /** @var \Ess\M2ePro\Model\Order\Item $orderItem */
                    $orderItem = $this->walmartFactory->getObject('Order_Item')
                                                      ->getCollection()
                                                      ->addFieldToFilter('order_id', $this->getId())
                                                      ->addFieldToFilter(
                                                          'walmart_order_item_id',
                                                          $existingItem['walmart_order_item_id']
                                                      )
                                                      ->getFirstItem();
                    /**
                     * Walmart returns the same Order Item more than one time with single QTY.
                     */
                    $maxQtyTotal = 1;
                    if ($orderItem->getId() && empty($orderItem->getChildObject()->getMergedWalmartOrderItemIds())) {
                        $maxQtyTotal = $orderItem->getChildObject()->getQtyPurchased();
                    }

                    $newQtyTotal = $newItem['qty'] + $existingItem['qty'];
                    $newQtyTotal >= $maxQtyTotal && $newQtyTotal = $maxQtyTotal;

                    $existingItem['qty'] = $newQtyTotal;

                    continue 2;
                }
            }

            unset($existingItem);
            $existingParams['items'][] = $newItem;
        }

        $change->setData('params', \Ess\M2ePro\Helper\Json::encode($existingParams));
        $change->save();

        return true;
    }

    /**
     * @return bool
     */
    public function canRefund()
    {
        if ($this->getStatus() == self::STATUS_CANCELED) {
            return false;
        }

        if (!$this->getWalmartAccount()->isRefundEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * @param array $items
     *
     * @return bool
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function refund(array $items = [], ?Creditmemo $creditMemo = null)
    {
        if (!$this->canRefund()) {
            return false;
        }

        $params = [
            'order_id' => $this->getWalmartOrderId(),
            'currency' => $this->getCurrency(),
            'items' => $items,
        ];

        $action = \Ess\M2ePro\Model\Order\Change::ACTION_CANCEL;

        if (
            $this->isShipped() ||
            $this->isPartiallyShipped() ||
            $this->getParentObject()->isOrderStatusUpdatingToShipped()
        ) {
            if (empty($items)) {
                $this->getParentObject()->addErrorLog(
                    'Walmart Order was not refunded. Reason: %msg%',
                    [
                        'msg' => 'Refund request was not submitted.
                                    To be processed through Walmart API, the refund must be applied to certain products
                                    in an order. Please indicate the number of each line item, that need to be refunded,
                                    in Credit Memo form.',
                    ]
                );

                return false;
            }

            $action = \Ess\M2ePro\Model\Order\Change::ACTION_REFUND;
        }

        $this->activeRecordFactory->getObject('Order\Change')->create(
            $this->getParentObject()->getId(),
            $action,
            $this->getParentObject()->getLog()->getInitiator(),
            \Ess\M2ePro\Helper\Component\Walmart::NICK,
            $params
        );

        return true;
    }

    public function canCreateCreditMemo(): bool
    {
        return false;
    }
}
