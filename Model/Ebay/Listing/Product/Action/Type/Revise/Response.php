<?php

namespace Ess\M2ePro\Model\Ebay\Listing\Product\Action\Type\Revise;

use Ess\M2ePro\Model\Ebay\Listing\Product\Image\RemoveEpcHostedImagesWithWatermarkService;

class Response extends \Ess\M2ePro\Model\Ebay\Listing\Product\Action\Type\Response
{
    private RemoveEpcHostedImagesWithWatermarkService $removeEpcHostedImagesWithWatermark;

    public function __construct(
        \Ess\M2ePro\Model\Ebay\Listing\Product\Action\DescriptionHasher $descriptionHasher,
        RemoveEpcHostedImagesWithWatermarkService $removeEpcHostedImagesWithWatermark,
        \Ess\M2ePro\Model\Ebay\Listing\Product\Action\DataHasher $dataHasher,
        \Ess\M2ePro\Helper\Component\Ebay\Category\Ebay $componentEbayCategoryEbay,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    ) {
        parent::__construct(
            $descriptionHasher,
            $dataHasher,
            $componentEbayCategoryEbay,
            $activeRecordFactory,
            $helperFactory,
            $modelFactory
        );

        $this->removeEpcHostedImagesWithWatermark = $removeEpcHostedImagesWithWatermark;
    }

    public function processSuccess(array $response, array $responseParams = [])
    {
        $this->prepareMetadata();

        $data = [
            'status' => \Ess\M2ePro\Model\Listing\Product::STATUS_LISTED,
        ];

        $data = $this->appendStatusHiddenValue($data);
        $data = $this->appendStatusChangerValue($data, $responseParams);

        $data = $this->appendOnlineBidsValue($data);
        $data = $this->appendOnlineQtyValues($data);
        $data = $this->appendOnlinePriceValues($data);
        $data = $this->appendOnlineInfoDataValues($data);

        $data = $this->appendItemFeesValues($data, $response);
        $data = $this->appendStartDateEndDateValues($data, $response);
        $data = $this->appendGalleryImagesValues($data, $response);

        $data = $this->appendIsVariationMpnFilledValue($data);
        $data = $this->appendVariationsThatCanNotBeDeleted($data, $response);

        $data = $this->appendIsVariationValue($data);
        $data = $this->appendIsAuctionType($data);

        $data = $this->appendDescriptionValues($data);
        $data = $this->appendImagesValues($data);
        $data = $this->appendProductIdentifiersValues($data);
        $data = $this->appendCategoriesValues($data);
        $data = $this->appendPartsValues($data);
        $data = $this->appendShippingValues($data);
        $data = $this->appendReturnValues($data);
        $data = $this->appendOtherValues($data);
        $data = $this->appendBestOfferValue($data);
        $data = $this->appendStrikeThroughPriceData($data);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = \Ess\M2ePro\Helper\Json::encode($data['additional_data']);
        }

        $this->getListingProduct()->addData($data);
        $this->getListingProduct()->getChildObject()->addData($data);
        $this->getListingProduct()->removeBlockingByError();
        $this->getListingProduct()->save();

        $this->updateVariationsValues(true);
        $this->updateEbayItem();

        $this->removeEpcHostedImagesWithWatermark->process($this->getEbayListingProduct());
    }

    public function processAlreadyStopped(array $response, array $responseParams = [])
    {
        $responseParams['status_changer'] = \Ess\M2ePro\Model\Listing\Product::STATUS_CHANGER_COMPONENT;

        $data = [
            'status' => \Ess\M2ePro\Model\Listing\Product::STATUS_INACTIVE,
        ];

        $data = $this->appendStatusChangerValue($data, $responseParams);
        $data = $this->appendStartDateEndDateValues($data, $response);

        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        $data['additional_data']['ebay_item_fees'] = [];
        $data['additional_data'] = \Ess\M2ePro\Helper\Json::encode($data['additional_data']);

        $this->getListingProduct()->addData($data)->save();
    }

    // ----------------------------------------

    protected function appendOnlineBidsValue($data)
    {
        $metadata = $this->getRequestMetaData();

        if ($metadata["is_listing_type_fixed"]) {
            $data['online_bids'] = null;
        }

        return $data;
    }

    protected function appendOnlineQtyValues($data)
    {
        $data = parent::appendOnlineQtyValues($data);

        $data['online_qty_sold'] = (int)$this->getEbayListingProduct()->getOnlineQtySold();
        isset($data['online_qty']) && $data['online_qty'] += $data['online_qty_sold'];

        return $data;
    }

    protected function appendOnlinePriceValues($data)
    {
        $data = parent::appendOnlinePriceValues($data);

        // if auction item has bids, we do not know correct online_current_price after revise action
        if (
            $this->getRequestData()->hasPriceStart() &&
            $this->getEbayListingProduct()->isListingTypeAuction() &&
            $this->getEbayListingProduct()->getOnlineBids()
        ) {
            unset($data['online_current_price']);
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
            foreach ($response['ebay_item_fees'] as $feeCode => $feeData) {
                if ($feeData['fee'] == 0) {
                    continue;
                }

                if (!isset($data['additional_data']['ebay_item_fees'][$feeCode])) {
                    $data['additional_data']['ebay_item_fees'][$feeCode] = $feeData;
                } else {
                    $data['additional_data']['ebay_item_fees'][$feeCode]['fee'] += $feeData['fee'];
                }
            }
        }

        return $data;
    }

    // ---------------------------------------

    protected function updateEbayItem()
    {
        $data = [
            'account_id' => $this->getAccount()->getId(),
            'marketplace_id' => $this->getMarketplace()->getId(),
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

        /** @var \Ess\M2ePro\Model\Ebay\Item $object */
        $object = $this->getEbayListingProduct()->getEbayItem();
        $object->addData($data)->save();

        return $object;
    }
}
