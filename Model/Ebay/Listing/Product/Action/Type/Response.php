<?php

namespace Ess\M2ePro\Model\Ebay\Listing\Product\Action\Type;

use Ess\M2ePro\Model\Ebay\Listing\Product\Variation as EbayVariation;
use Ess\M2ePro\Model\Ebay\Template\ChangeProcessor\ChangeProcessorAbstract as ChangeProcessor;
use Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Product as EbayListingProduct;

abstract class Response extends \Ess\M2ePro\Model\AbstractModel
{
    public const INSTRUCTION_INITIATOR = 'action_response';

    /** @var array */
    protected $params = [];
    /** @var \Ess\M2ePro\Model\Ebay\Listing\Product\Action\RequestData */
    protected $requestData = null;
    /** @var array */
    protected $requestMetaData = [];
    protected $activeRecordFactory;

    private \Ess\M2ePro\Model\Ebay\Listing\Product\Action\DescriptionHasher $descriptionHasher;
    /** @var \Ess\M2ePro\Model\Ebay\Listing\Product\Action\DataHasher */
    private $dataHasher;
    /** @var \Ess\M2ePro\Model\Listing\Product */
    private $listingProduct = null;
    /** @var \Ess\M2ePro\Model\Ebay\Listing\Product\Action\Configurator */
    private $configurator = null;
    /** @var \Ess\M2ePro\Helper\Component\Ebay\Category\Ebay */
    private $componentEbayCategoryEbay;

    public function __construct(
        \Ess\M2ePro\Model\Ebay\Listing\Product\Action\DescriptionHasher $descriptionHasher,
        \Ess\M2ePro\Model\Ebay\Listing\Product\Action\DataHasher $dataHasher,
        \Ess\M2ePro\Helper\Component\Ebay\Category\Ebay $componentEbayCategoryEbay,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    ) {
        parent::__construct($helperFactory, $modelFactory);

        $this->descriptionHasher = $descriptionHasher;
        $this->dataHasher = $dataHasher;
        $this->activeRecordFactory = $activeRecordFactory;
        $this->componentEbayCategoryEbay = $componentEbayCategoryEbay;
    }

    // ---------------------------------------

    abstract public function processSuccess(array $response, array $responseParams = []);

    // ---------------------------------------

    protected function prepareMetadata()
    {
        // backward compatibility for case when we have old request data and new response logic
        $metadata = $this->getRequestMetaData();
        if (!isset($metadata["is_listing_type_fixed"])) {
            $metadata["is_listing_type_fixed"] = $this->getEbayListingProduct()->isListingTypeFixed();
            $this->setRequestMetaData($metadata);
        }
    }

    // ---------------------------------------

    /**
     * @param array $params
     */
    public function setParams(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        return $this->params;
    }

    // ---------------------------------------

    /**
     * @param \Ess\M2ePro\Model\Listing\Product $object
     */
    public function setListingProduct(\Ess\M2ePro\Model\Listing\Product $object)
    {
        $this->listingProduct = $object;
    }

    /**
     * @return \Ess\M2ePro\Model\Listing\Product
     */
    protected function getListingProduct()
    {
        return $this->listingProduct;
    }

    // ---------------------------------------

    /**
     * @param \Ess\M2ePro\Model\Ebay\Listing\Product\Action\Configurator $object
     */
    public function setConfigurator(\Ess\M2ePro\Model\Ebay\Listing\Product\Action\Configurator $object)
    {
        $this->configurator = $object;
    }

    /**
     * @return \Ess\M2ePro\Model\Ebay\Listing\Product\Action\Configurator
     */
    protected function getConfigurator()
    {
        return $this->configurator;
    }

    // ---------------------------------------

    /**
     * @param \Ess\M2ePro\Model\Ebay\Listing\Product\Action\RequestData $object
     */
    public function setRequestData(\Ess\M2ePro\Model\Ebay\Listing\Product\Action\RequestData $object)
    {
        $this->requestData = $object;
    }

    /**
     * @return \Ess\M2ePro\Model\Ebay\Listing\Product\Action\RequestData
     */
    protected function getRequestData()
    {
        return $this->requestData;
    }

    // ---------------------------------------

    public function getRequestMetaData()
    {
        return $this->requestMetaData;
    }

    public function setRequestMetaData($value)
    {
        $this->requestMetaData = $value;

        return $this;
    }

    // ---------------------------------------

    /**
     * @return \Ess\M2ePro\Model\Ebay\Listing\Product
     */
    protected function getEbayListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return \Ess\M2ePro\Model\Listing
     */
    protected function getListing()
    {
        return $this->getListingProduct()->getListing();
    }

    /**
     * @return \Ess\M2ePro\Model\Ebay\Listing
     */
    protected function getEbayListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return \Ess\M2ePro\Model\Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getListing()->getMarketplace();
    }

    /**
     * @return \Ess\M2ePro\Model\Ebay\Marketplace
     */
    protected function getEbayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return \Ess\M2ePro\Model\Account
     */
    protected function getAccount()
    {
        return $this->getListing()->getAccount();
    }

    /**
     * @return \Ess\M2ePro\Model\Ebay\Account
     */
    protected function getEbayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return \Ess\M2ePro\Model\Magento\Product\Cache
     */
    protected function getMagentoProduct()
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    // ---------------------------------------

    /**
     * @param $itemId
     *
     * @return \Ess\M2ePro\Model\Ebay\Item
     */
    protected function createEbayItem($itemId)
    {
        $data = [
            'account_id' => $this->getAccount()->getId(),
            'marketplace_id' => $this->getMarketplace()->getId(),
            'item_id' => (double)$itemId,
            'product_id' => (int)$this->getListingProduct()->getProductId(),
            'store_id' => (int)$this->getListing()->getStoreId(),
        ];

        if ($this->getRequestData()->isVariationItem() && $this->getRequestData()->getVariations()) {
            $variations = [];
            $requestMetadata = $this->getRequestMetaData();

            foreach ($this->getRequestData()->getVariations() as $variation) {
                $channelOptions = $variation['specifics'];
                $productOptions = $variation['specifics'];

                if (empty($requestMetadata['variations_specifics_replacements'])) {
                    $variations[] = [
                        'product_options' => $productOptions,
                        'channel_options' => $channelOptions,
                    ];

                    continue;
                }

                foreach ($requestMetadata['variations_specifics_replacements'] as $productValue => $channelValue) {
                    if (!isset($productOptions[$channelValue])) {
                        continue;
                    }

                    $productOptions[$productValue] = $productOptions[$channelValue];
                    unset($productOptions[$channelValue]);
                }

                $variations[] = [
                    'product_options' => $productOptions,
                    'channel_options' => $channelOptions,
                ];
            }

            $data['variations'] = \Ess\M2ePro\Helper\Json::encode($variations);
        }

        if ($this->getListingProduct()->getMagentoProduct()->isGroupedType()) {
            $additionalData = $this->getListingProduct()->getAdditionalData();
            $data['additional_data'] = \Ess\M2ePro\Helper\Json::encode([
                'grouped_product_mode' => $additionalData['grouped_product_mode'],
            ]);
        }

        /** @var \Ess\M2ePro\Model\Ebay\Item $object */
        $object = $this->activeRecordFactory->getObject('Ebay\Item');
        $object->setData($data)->save();

        return $object;
    }

    protected function updateVariationsValues($saveQtySold)
    {
        if (!$this->getRequestData()->isVariationItem()) {
            return;
        }

        $requestVariations = $this->getRequestData()->getVariations();

        $requestMetadata = $this->getRequestMetaData();
        $variationMetadata = !empty($requestMetadata['variation_data']) ? $requestMetadata['variation_data'] : [];

        foreach ($this->getListingProduct()->getVariations(true) as $variation) {
            if ($this->getRequestData()->hasVariations()) {
                if (!isset($variationMetadata[$variation->getId()]['index'])) {
                    continue;
                }

                $requestVariation = $requestVariations[$variationMetadata[$variation->getId()]['index']];

                if ($requestVariation['delete']) {
                    $variation->delete();
                    continue;
                }

                $data = [
                    'online_sku' => $requestVariation['sku'],
                    'add' => 0,
                    'delete' => 0,
                ];

                if (isset($requestVariation['price'])) {
                    $data['online_price'] = $requestVariation['price'];
                }

                /** @var EbayVariation $ebayVariation */
                $ebayVariation = $variation->getChildObject();

                $data['online_qty_sold'] = $saveQtySold ? (int)$ebayVariation->getOnlineQtySold() : 0;
                $data['online_qty'] = $requestVariation['qty'] + $data['online_qty_sold'];

                $variation->getChildObject()->addData($data)->save();

                if (!empty($requestVariation['details'])) {
                    $variationAdditionalData = $variation->getAdditionalData();
                    $variationAdditionalData['online_product_details'] = $requestVariation['details'];

                    $variation->setData(
                        'additional_data',
                        \Ess\M2ePro\Helper\Json::encode($variationAdditionalData)
                    );
                    $variation->save();
                }
            }

            $variation->getChildObject()->setStatus($this->getListingProduct()->getStatus());
        }
    }

    // ---------------------------------------

    protected function appendStatusHiddenValue($data)
    {
        if (
            ($this->getRequestData()->hasQty() && $this->getRequestData()->getQty() <= 0) ||
            ($this->getRequestData()->hasVariations() && $this->getRequestData()->getVariationQty() <= 0)
        ) {
            $data['status'] = \Ess\M2ePro\Model\Listing\Product::STATUS_HIDDEN;
        }

        return $data;
    }

    protected function appendStatusChangerValue($data, $responseParams)
    {
        if (isset($this->params['status_changer'])) {
            $data['status_changer'] = (int)$this->params['status_changer'];
        }

        if (isset($responseParams['status_changer'])) {
            $data['status_changer'] = (int)$responseParams['status_changer'];
        }

        return $data;
    }

    // ---------------------------------------

    protected function appendOnlineBidsValue($data)
    {
        $metadata = $this->getRequestMetaData();

        if ($metadata["is_listing_type_fixed"]) {
            $data['online_bids'] = null;
        } else {
            $data['online_bids'] = 0;
        }

        return $data;
    }

    protected function appendOnlineQtyValues($data)
    {
        $data['online_qty_sold'] = 0;

        if ($this->getRequestData()->hasVariations()) {
            $data['online_qty'] = $this->getRequestData()->getVariationQty();
        } elseif ($this->getRequestData()->hasQty()) {
            $data['online_qty'] = $this->getRequestData()->getQty();
        }

        return $data;
    }

    protected function appendOnlinePriceValues($data)
    {
        $metadata = $this->getRequestMetaData();

        if ($metadata["is_listing_type_fixed"]) {
            $data['online_start_price'] = null;
            $data['online_reserve_price'] = null;
            $data['online_buyitnow_price'] = null;

            if ($this->getRequestData()->hasVariations() && $this->getConfigurator()->isPriceAllowed()) {
                $calculateWithEmptyQty = $this->getEbayListingProduct()->isOutOfStockControlEnabled();
                $data['online_current_price'] = $this->getRequestData()->getVariationPrice($calculateWithEmptyQty);
            } elseif ($this->getRequestData()->hasPriceFixed()) {
                $data['online_current_price'] = $this->getRequestData()->getPriceFixed();
            }
        } else {
            if ($this->getRequestData()->hasPriceStart()) {
                $data['online_start_price'] = $this->getRequestData()->getPriceStart();
                $data['online_current_price'] = $this->getRequestData()->getPriceStart();
            }

            if ($this->getRequestData()->hasPriceReserve()) {
                $data['online_reserve_price'] = $this->getRequestData()->getPriceReserve();
            }

            if ($this->getRequestData()->hasPriceBuyItNow()) {
                $data['online_buyitnow_price'] = $this->getRequestData()->getPriceBuyItNow();
            }
        }

        if ($this->getConfigurator()->isPriceAllowed()) {
            $data[EbayListingProduct::COLUMN_PRICE_LAST_UPDATE_DATE] = \Ess\M2ePro\Helper\Date::createCurrentGmt();
        }

        return $data;
    }

    protected function appendOnlineInfoDataValues($data)
    {
        if ($this->getRequestData()->hasSku()) {
            $data['online_sku'] = $this->getRequestData()->getSku();
        }

        if ($this->getRequestData()->hasTitle()) {
            $data['online_title'] = $this->getRequestData()->getTitle();
        }

        if ($this->getRequestData()->hasSubtitle()) {
            $data['online_sub_title'] = $this->getRequestData()->getSubtitle();
        }

        if ($this->getRequestData()->hasDuration()) {
            $data['online_duration'] = $this->getRequestData()->getDuration();
        }

        if ($this->getRequestData()->hasPrimaryCategory()) {
            $tempPath = $this->componentEbayCategoryEbay->getPath(
                $this->getRequestData()->getPrimaryCategory(),
                $this->getMarketplace()->getId()
            );

            if ($tempPath) {
                $data['online_main_category'] = $tempPath . ' (' . $this->getRequestData()->getPrimaryCategory() . ')';
            } else {
                $data['online_main_category'] = $this->getRequestData()->getPrimaryCategory();
            }
        }

        return $data;
    }

    // ---------------------------------------

    protected function appendItemFeesValues($data, $response)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (isset($response['ebay_item_fees'])) {
            $data['additional_data']['ebay_item_fees'] = $response['ebay_item_fees'];
        }

        return $data;
    }

    protected function appendStartDateEndDateValues($data, $response)
    {
        if (isset($response['ebay_start_date_raw'])) {
            $data['start_date'] = $this->getHelper('Component\Ebay')->timeToString(
                $response['ebay_start_date_raw']
            );
        }

        if (isset($response['ebay_end_date_raw'])) {
            $data['end_date'] = $this->getHelper('Component\Ebay')->timeToString(
                $response['ebay_end_date_raw']
            );
        }

        return $data;
    }

    protected function appendGalleryImagesValues($data, $response)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (isset($response['is_eps_ebay_images_mode'])) {
            $data['additional_data']['is_eps_ebay_images_mode'] = $response['is_eps_ebay_images_mode'];
        }

        return $data;
    }

    protected function appendIsVariationMpnFilledValue($data)
    {
        if (!$this->getRequestData()->hasVariations()) {
            return $data;
        }

        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        $isVariationMpnFilled = false;

        foreach ($this->getRequestData()->getVariations() as $variation) {
            if (empty($variation['details']['mpn'])) {
                continue;
            }

            $isVariationMpnFilled = true;
            break;
        }

        $data['additional_data']['is_variation_mpn_filled'] = $isVariationMpnFilled;

        if (!$isVariationMpnFilled) {
            $data['additional_data']['without_mpn_variation_issue'] = true;
        }

        return $data;
    }

    protected function appendVariationsThatCanNotBeDeleted(array $data, array $response)
    {
        if (!$this->getRequestData()->isVariationItem()) {
            return $data;
        }

        $variations = isset($response['variations_that_can_not_be_deleted'])
            ? $response['variations_that_can_not_be_deleted'] : [];

        $data['additional_data']['variations_that_can_not_be_deleted'] = $variations;

        return $data;
    }

    protected function appendIsVariationValue(array $data)
    {
        $data["online_is_variation"] = $this->getRequestData()->isVariationItem();

        return $data;
    }

    protected function appendIsAuctionType(array $data)
    {
        $metadata = $this->getRequestMetaData();
        $data["online_is_auction_type"] = !$metadata["is_listing_type_fixed"];

        return $data;
    }

    protected function appendDescriptionValues($data): array
    {
        $requestData = $this->getRequestData();
        $hash = $this->descriptionHasher->hashProductDescriptionFields(
            $requestData->getDescription(),
            $requestData->getProductDetailsIncludeEbayDetails(),
            $requestData->getProductDetailsIncludeImage()
        );

        $data[\Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Product::COLUMN_ONLINE_DESCRIPTION] = $hash;

        return $data;
    }

    protected function appendImagesValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['images_data'])) {
            return $data;
        }

        $data['online_images'] = $this->getHelper('Data')->hashString(
            \Ess\M2ePro\Helper\Json::encode($requestMetadata['images_data']),
            'md5'
        );

        return $data;
    }

    protected function appendProductIdentifiersValues(array $data): array
    {
        $requestData = $this->getRequestData();
        $hash = $this->dataHasher->hashProductIdentifiers(
            $requestData->getProductDetailsUpc(),
            $requestData->getProductDetailsEan(),
            $requestData->getProductDetailsIsbn(),
            $requestData->getProductDetailsEpid(),
            $requestData->getProductDetailsBrand(),
            $requestData->getProductDetailsMpn()
        );

        $data[\Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Product::COLUMN_ONLINE_PRODUCT_IDENTIFIERS_HASH] = $hash;

        return $data;
    }

    protected function appendCategoriesValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['categories_data'])) {
            return $data;
        }

        $data['online_categories_data'] = \Ess\M2ePro\Helper\Json::encode(
            $requestMetadata['categories_data']
        );

        return $data;
    }

    protected function appendPartsValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!array_key_exists('parts_data_hash', $requestMetadata)) {
            return $data;
        }

        $data['online_parts_data'] = $requestMetadata['parts_data_hash'];

        return $data;
    }

    protected function appendShippingValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['shipping_data'])) {
            return $data;
        }

        $data['online_shipping_data'] = $this->getHelper('Data')->hashString(
            \Ess\M2ePro\Helper\Json::encode($requestMetadata['shipping_data']),
            'md5'
        );

        return $data;
    }

    protected function appendReturnValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['return_data'])) {
            return $data;
        }

        $data['online_return_data'] = $this->getHelper('Data')->hashString(
            \Ess\M2ePro\Helper\Json::encode($requestMetadata['return_data']),
            'md5'
        );

        return $data;
    }

    protected function appendOtherValues($data): array
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['other_data'])) {
            return $data;
        }

        $data['online_other_data'] = $this->helperFactory
            ->getObjectByClass(\Ess\M2ePro\Helper\Data::class)
            ->hashString(\Ess\M2ePro\Helper\Json::encode($requestMetadata['other_data']), 'md5');

        if (isset($requestMetadata['other_data']['product_details']['video_id'])) {
            $data['online_video_id'] = $requestMetadata['other_data']['product_details']['video_id'];
        }

        if (isset($requestMetadata['compliance_documents'])) {
            $data['online_compliance_documents'] = \Ess\M2ePro\Helper\Json::encode(
                $requestMetadata['compliance_documents']
            );
        }

        return $data;
    }

    protected function appendBestOfferValue(array $data): array
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['best_offer_hash'])) {
            return $data;
        }

        $data['online_best_offer'] = $requestMetadata['best_offer_hash'];

        return $data;
    }

    protected function appendStrikeThroughPriceData(array $data): array
    {
        $data['online_strike_through_price'] = null;

        $requestMetadata = $this->getRequestMetaData();
        if (!empty($requestMetadata['online_strike_through_price'])) {
            $data['online_strike_through_price'] = $requestMetadata['online_strike_through_price'];
        }

        return $data;
    }

    public function throwRepeatActionInstructions()
    {
        $instructions = [];

        if ($this->getConfigurator()->isQtyAllowed()) {
            $instructions[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => ChangeProcessor::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => 80,
            ];
        }

        if ($this->getConfigurator()->isPriceAllowed()) {
            $instructions[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => ChangeProcessor::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => 80,
            ];
        }

        if ($this->getConfigurator()->isTitleAllowed()) {
            $instructions[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => ChangeProcessor::INSTRUCTION_TYPE_TITLE_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => 60,
            ];
        }

        if ($this->getConfigurator()->isSubtitleAllowed()) {
            $instructions[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => ChangeProcessor::INSTRUCTION_TYPE_SUBTITLE_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => 60,
            ];
        }

        if ($this->getConfigurator()->isDescriptionAllowed()) {
            $instructions[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => ChangeProcessor::INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => 30,
            ];
        }

        if ($this->getConfigurator()->isImagesAllowed()) {
            $instructions[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => ChangeProcessor::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => 30,
            ];
        }

        if ($this->getConfigurator()->isCategoriesAllowed()) {
            $instructions[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => ChangeProcessor::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => 60,
            ];
        }

        if ($this->getConfigurator()->isShippingAllowed()) {
            $instructions[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => ChangeProcessor::INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => 60,
            ];
        }

        if ($this->getConfigurator()->isReturnAllowed()) {
            $instructions[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => ChangeProcessor::INSTRUCTION_TYPE_RETURN_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => 60,
            ];
        }

        if ($this->getConfigurator()->isOtherAllowed()) {
            $instructions[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => ChangeProcessor::INSTRUCTION_TYPE_OTHER_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => 30,
            ];
        }

        if ($this->getConfigurator()->isVariationsAllowed()) {
            $instructions[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => ChangeProcessor::INSTRUCTION_TYPE_VARIATION_IMAGES_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => 30,
            ];
        }

        $this->activeRecordFactory->getObject('Listing_Product_Instruction')->getResource()->add($instructions);
    }
}
