<?php

/**
 * @method \Ess\M2ePro\Model\Order\Item getParentObject()
 */

namespace Ess\M2ePro\Model\Amazon\Order;

use Ess\M2ePro\Model\Amazon\Order\Item\CustomizationDetails\TextPrintingCustomization;
use Ess\M2ePro\Model\Order\Exception\ProductCreationDisabled;

class Item extends \Ess\M2ePro\Model\ActiveRecord\Component\Child\Amazon\AbstractModel
{
    public const CUSTOMIZATION_DETAILS_TYPE_TEXT_PRINTING = 'text_printing';

    /** @var \Ess\M2ePro\Model\Magento\Product\BuilderFactory */
    private $productBuilderFactory;
    /** @var \Magento\Catalog\Model\ProductFactory */
    private $productFactory;
    /** @var \Ess\M2ePro\Model\Amazon\Item|null */
    private $channelItem = null;
    /** @var \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Other */
    private $listingOtherResourceModel;

    public function __construct(
        \Ess\M2ePro\Model\Magento\Product\BuilderFactory $productBuilderFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Factory $parentFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Other $listingOtherResourceModel,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productBuilderFactory = $productBuilderFactory;
        $this->productFactory = $productFactory;
        $this->listingOtherResourceModel = $listingOtherResourceModel;

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
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Ess\M2ePro\Model\ResourceModel\Amazon\Order\Item::class);
    }

    public function getProxy()
    {
        return $this->modelFactory->getObject('Amazon_Order_Item_ProxyObject', [
            'item' => $this,
        ]);
    }

    /**
     * @return \Ess\M2ePro\Model\Amazon\Order
     */
    public function getAmazonOrder(): \Ess\M2ePro\Model\Amazon\Order
    {
        return $this->getParentObject()->getOrder()->getChildObject();
    }

    /**
     * @return \Ess\M2ePro\Model\Amazon\Account
     */
    public function getAmazonAccount(): \Ess\M2ePro\Model\Amazon\Account
    {
        return $this->getAmazonOrder()->getAmazonAccount();
    }

    /**
     * @return \Ess\M2ePro\Model\Amazon\Item|null
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getChannelItem(): ?\Ess\M2ePro\Model\Amazon\Item
    {
        if ($this->channelItem !== null) {
            return $this->channelItem;
        }

        /** @var \Ess\M2ePro\Model\Amazon\Item $channelItem */
        $channelItem = $this
            ->activeRecordFactory
            ->getObject('Amazon\Item')->getCollection()
            ->addFieldToFilter(
                'account_id',
                $this->getParentObject()->getOrder()->getAccountId()
            )
            ->addFieldToFilter(
                'marketplace_id',
                $this->getParentObject()->getOrder()->getMarketplaceId()
            )
            ->addFieldToFilter('sku', $this->getSku())
            ->setOrder(
                'create_date',
                \Magento\Framework\Data\Collection::SORT_ORDER_DESC
            )
            ->getFirstItem();

        if ($channelItem->isObjectNew()) {
            return null;
        }

        return $this->channelItem = $channelItem;
    }

    public function getAmazonOrderItemId()
    {
        return $this->getData('amazon_order_item_id');
    }

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getSku()
    {
        return $this->getData('sku');
    }

    public function getGeneralId()
    {
        return $this->getData('general_id');
    }

    /**
     * @return int
     */
    public function getIsIsbnGeneralId(): int
    {
        return (int)$this->getData('is_isbn_general_id');
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return (float)$this->getData('price');
    }

    /**
     * @return float
     */
    public function getShippingPrice(): float
    {
        return (float)$this->getData('shipping_price');
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->getData('currency');
    }

    /**
     * @return int
     */
    public function getQtyPurchased(): int
    {
        return (int)$this->getData('qty_purchased');
    }

    /**
     * @return float
     */
    public function getGiftPrice(): float
    {
        return (float)$this->getData('gift_price');
    }

    public function getGiftType()
    {
        return $this->getData('gift_type');
    }

    public function getGiftMessage()
    {
        return $this->getData('gift_message');
    }

    public function hasCustomizedInfo(): bool
    {
        return $this->getCustomizedInfo() !== null;
    }

    public function getCustomizedInfo(): ?string
    {
        return $this->getData('buyer_customized_info');
    }

    public function isShippingPalletDelivery(): bool
    {
        return (bool)$this->getData(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Order\Item::COLUMN_IS_SHIPPING_PALLET_DELIVERY
        );
    }

    /**
     * @return array
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getTaxDetails(): array
    {
        return $this->getSettings('tax_details');
    }

    /**
     * @return float
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getTaxAmount(): float
    {
        $taxDetails = $this->getTaxDetails();

        return isset($taxDetails['product']['value']) ? (float)$taxDetails['product']['value'] : 0.0;
    }

    /**
     * @return float
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getShippingTaxAmount(): float
    {
        $taxDetails = $this->getTaxDetails();

        return isset($taxDetails['shipping']['value']) ? (float)$taxDetails['shipping']['value'] : 0.0;
    }

    /**
     * @return string|null
     */
    public function getFulfillmentCenterId(): ?string
    {
        return $this->getData('fulfillment_center_id');
    }

    /**
     * @return array
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getDiscountDetails(): array
    {
        return $this->getSettings('discount_details');
    }

    /**
     * @return float
     */
    public function getDiscountAmount()
    {
        $discountDetails = $this->getDiscountDetails();

        return !empty($discountDetails['promotion']['value'])
            ? ($discountDetails['promotion']['value'] / $this->getQtyPurchased()) : 0.0;
    }

    /**
     * @return array
     */
    public function getVariationProductOptions(): array
    {
        $channelItem = $this->getChannelItem();

        if ($channelItem === null) {
            return [];
        }

        return $channelItem->getVariationProductOptions();
    }

    /**
     * @return array
     */
    public function getVariationChannelOptions(): array
    {
        $channelItem = $this->getChannelItem();

        if ($channelItem === null) {
            return [];
        }

        return $channelItem->getVariationChannelOptions();
    }

    /**
     * @return int
     */
    public function getAssociatedStoreId()
    {
        // Item was listed by M2E
        // ---------------------------------------
        if ($this->getChannelItem() !== null) {
            $storeId = $this->getAmazonAccount()->isMagentoOrdersListingsStoreCustom()
                ? $this->getAmazonAccount()->getMagentoOrdersListingsStoreId()
                : $this->getChannelItem()->getStoreId();
        } else {
            $storeId = $this->getAmazonAccount()->getMagentoOrdersListingsOtherStoreId();
        }
        // ---------------------------------------

        // If order fulfilled by Amazon it has priority
        // ---------------------------------------
        if (
            $this->getAmazonOrder()->isFulfilledByAmazon() &&
            $this->getAmazonAccount()->isMagentoOrdersFbaStoreModeEnabled()
        ) {
            $storeId = $this->getAmazonAccount()->getMagentoOrdersFbaStoreId();
        }

        // ---------------------------------------

        return $storeId;
    }

    public function canCreateMagentoOrder(): bool
    {
        return $this->isOrdersCreationEnabled();
    }

    public function isReservable(): bool
    {
        return $this->isOrdersCreationEnabled();
    }

    private function isOrdersCreationEnabled(): bool
    {
        $channelItem = $this->getChannelItem();

        if ($channelItem === null) {
            return $this->isOrdersCreationEnabledForListingsOther(
                $this->getAmazonAccount(),
                $this->getAmazonOrder()
            );
        }

        if (
            $this->listingOtherResourceModel->isItemFromOtherListing(
                $channelItem->getProductId(),
                $channelItem->getAccountId(),
                $channelItem->getMarketplaceId()
            )
        ) {
            return $this->isOrdersCreationEnabledForListingsOther(
                $this->getAmazonAccount(),
                $this->getAmazonOrder()
            );
        }

        return $this->isOrdersCreationEnabledForListings(
            $this->getAmazonAccount(),
            $this->getAmazonOrder()
        );
    }

    private function isOrdersCreationEnabledForListingsOther(
        \Ess\M2ePro\Model\Amazon\Account $amazonAccount,
        \Ess\M2ePro\Model\Amazon\Order $amazonOrder
    ): bool {
        if (!$amazonAccount->isMagentoOrdersListingsOtherModeEnabled()) {
            return false;
        }

        return $amazonOrder->getPurchaseCreateDate()
            >= $amazonAccount->getMagentoOrdersListingsOtherCreateFromDateOrAccountCreateDate();
    }

    private function isOrdersCreationEnabledForListings(
        \Ess\M2ePro\Model\Amazon\Account $amazonAccount,
        \Ess\M2ePro\Model\Amazon\Order $amazonOrder
    ): bool {
        if (!$amazonAccount->isMagentoOrdersListingsModeEnabled()) {
            return false;
        }

        return $amazonOrder->getPurchaseCreateDate()
            >= $amazonAccount->getMagentoOrdersListingsCreateFromDateOrAccountCreateDate();
    }

    /**
     * @return int|mixed
     * @throws \Ess\M2ePro\Model\Exception
     */
    public function getAssociatedProductId()
    {
        // Item was listed by M2E
        // ---------------------------------------
        if ($this->getChannelItem() !== null) {
            return $this->getChannelItem()->getProductId();
        }

        // ---------------------------------------

        // Unmanaged Item
        // ---------------------------------------
        $sku = $this->getSku();
        if ($sku != '' && strlen($sku) <= \Ess\M2ePro\Helper\Magento\Product::SKU_MAX_LENGTH) {
            $product = $this->productFactory->create()
                                            ->setStoreId($this->getAmazonOrder()->getAssociatedStoreId())
                                            ->getCollection()
                                            ->addAttributeToSelect('sku')
                                            ->addAttributeToFilter('sku', $sku)
                                            ->getFirstItem();

            if ($product->getId()) {
                $this->_eventManager->dispatch('ess_associate_amazon_order_item_to_product', [
                    'product' => $product,
                    'order_item' => $this->getParentObject(),
                ]);

                return $product->getId();
            }
        }
        // ---------------------------------------

        $product = $this->createProduct();

        $this->_eventManager->dispatch('ess_associate_amazon_order_item_to_product', [
            'product' => $product,
            'order_item' => $this->getParentObject(),
        ]);

        return $product->getId();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     * @throws \Ess\M2ePro\Model\Exception
     */
    protected function createProduct(): \Magento\Catalog\Model\Product
    {
        if (!$this->getAmazonAccount()->isMagentoOrdersListingsOtherProductImportEnabled()) {
            throw new ProductCreationDisabled(
                $this->getHelper('Module\Translation')->__(
                    'Product creation is disabled in "Account > Orders > Product Not Found".'
                )
            );
        }

        $storeId = $this->getAmazonAccount()->getMagentoOrdersListingsOtherStoreId();
        if ($storeId == 0) {
            $storeId = $this->getHelper('Magento\Store')->getDefaultStoreId();
        }

        $sku = $this->getSku();
        if (mb_strlen($sku) > \Ess\M2ePro\Helper\Magento\Product::SKU_MAX_LENGTH) {
            $hashLength = 10;
            $savedSkuLength = \Ess\M2ePro\Helper\Magento\Product::SKU_MAX_LENGTH - $hashLength - 1;
            $hash = $this->getHelper('Data')->generateUniqueHash($sku, $hashLength);

            $isSaveStart = (bool)$this->getHelper('Module')->getConfig()->getGroupValue(
                '/order/magento/settings/',
                'save_start_of_long_sku_for_new_product'
            );

            if ($isSaveStart) {
                $sku = substr($sku, 0, $savedSkuLength) . '-' . $hash;
            } else {
                $sku = $hash . '-' . substr($sku, strlen($sku) - $savedSkuLength, $savedSkuLength);
            }
        }

        $productData = [
            'title' => $this->getTitle(),
            'sku' => $sku,
            'description' => '',
            'short_description' => '',
            'qty' => $this->getQtyForNewProduct(),
            'price' => $this->getPrice(),
            'store_id' => $storeId,
            'tax_class_id' => $this->getAmazonAccount()->getMagentoOrdersListingsOtherProductTaxClassId(),
        ];

        // Create product in magento
        // ---------------------------------------
        /** @var \Ess\M2ePro\Model\Magento\Product\Builder $productBuilder */
        $productBuilder = $this->productBuilderFactory->create()->setData($productData);
        $productBuilder->buildProduct();
        // ---------------------------------------

        $this->getParentObject()->getOrder()->addSuccessLog(
            'Product for Amazon Item "%title%" was Created in Magento Catalog.',
            ['!title' => $this->getTitle()]
        );

        return $productBuilder->getProduct();
    }

    protected function getQtyForNewProduct()
    {
        $otherListing = $this->parentFactory->getObject(\Ess\M2ePro\Helper\Component\Amazon::NICK, 'Listing\Other')
                                            ->getCollection()
                                            ->addFieldToFilter(
                                                'account_id',
                                                $this->getParentObject()->getOrder()->getAccountId()
                                            )
                                            ->addFieldToFilter(
                                                'marketplace_id',
                                                $this->getParentObject()->getOrder()->getMarketplaceId()
                                            )
                                            ->addFieldToFilter('sku', $this->getSku())
                                            ->getFirstItem();

        if ((int)$otherListing->getOnlineQty() > $this->getQtyPurchased()) {
            return $otherListing->getOnlineQty();
        }

        return $this->getQtyPurchased();
    }

    public function hasCustomizationDetailsWithTextPrintingType(): bool
    {
        return !empty($this->getCustomizationDetailsWithTextPrintingType());
    }

    /**
     * @return TextPrintingCustomization[]
     */
    public function getCustomizationDetailsWithTextPrintingType(): array
    {
        $result = [];
        foreach ($this->getCustomizationDetails()[self::CUSTOMIZATION_DETAILS_TYPE_TEXT_PRINTING] ?? [] as $data) {
            $result[] = new TextPrintingCustomization($data['label'], $data['value']);
        }

        return $result;
    }

    public function hasCustomizationDetails(): bool
    {
        return !empty($this->getCustomizationDetails());
    }

    private function getCustomizationDetails(): array
    {
        $value = $this->getData(\Ess\M2ePro\Model\ResourceModel\Amazon\Order\Item::COLUMN_CUSTOMIZATION_DETAILS);
        if (empty($value)) {
            return [];
        }

        return json_decode($value, true);
    }
}
