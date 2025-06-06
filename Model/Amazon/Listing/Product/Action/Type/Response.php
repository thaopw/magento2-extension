<?php

namespace Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type;

use Ess\M2ePro\Model\Amazon\Template\ChangeProcessor\ChangeProcessorAbstract as ChangeProcessor;
use Ess\M2ePro\Model\ResourceModel\Amazon\Listing\Product as AmazonListingProductResource;

abstract class Response extends \Ess\M2ePro\Model\AbstractModel
{
    public const INSTRUCTION_INITIATOR = 'action_response';

    /** @var \Ess\M2ePro\Model\ActiveRecord\Factory  */
    protected $activeRecordFactory;
    /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Action\RequestData */
    protected $requestData;
    /** @var \Ess\M2ePro\Model\Listing\Product */
    private $listingProduct;
    /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Configurator */
    private $configurator;

    /** @var array */
    private $params = [];
    /** @var array */
    protected $requestMetaData = [];

    public function __construct(
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        array $data = []
    ) {
        parent::__construct($helperFactory, $modelFactory, $data);

        $this->activeRecordFactory = $activeRecordFactory;
    }

    // ---------------------------------------

    /**
     * @param array $params
     *
     * @return void
     */
    abstract public function processSuccess(array $params = []): void;

    // ---------------------------------------

    public function setParams(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getParams()
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
     * @param \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Configurator $object
     */
    public function setConfigurator(\Ess\M2ePro\Model\Amazon\Listing\Product\Action\Configurator $object)
    {
        $this->configurator = $object;
    }

    /**
     * @return \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Configurator
     */
    protected function getConfigurator()
    {
        return $this->configurator;
    }

    // ---------------------------------------

    /**
     * @param \Ess\M2ePro\Model\Amazon\Listing\Product\Action\RequestData $object
     */
    public function setRequestData(\Ess\M2ePro\Model\Amazon\Listing\Product\Action\RequestData $object)
    {
        $this->requestData = $object;
    }

    /**
     * @return \Ess\M2ePro\Model\Amazon\Listing\Product\Action\RequestData
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

    /**
     * @return \Ess\M2ePro\Model\Amazon\Listing\Product
     */
    protected function getAmazonListingProduct()
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
     * @return \Ess\M2ePro\Model\Amazon\Listing
     */
    protected function getAmazonListing()
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
     * @return \Ess\M2ePro\Model\Amazon\Marketplace
     */
    protected function getAmazonMarketplace()
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
     * @return \Ess\M2ePro\Model\Amazon\Account
     */
    protected function getAmazonAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return \Ess\M2ePro\Model\Magento\Product
     */
    protected function getMagentoProduct()
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    protected function appendStatusChangerValue($data)
    {
        if (isset($this->params['status_changer'])) {
            $data['status_changer'] = (int)$this->params['status_changer'];
        }

        return $data;
    }

    // ---------------------------------------

    protected function appendQtyValues($data, ?\DateTime $updateRequestDate)
    {
        if ($this->getRequestData()->hasQty()) {
            $data[AmazonListingProductResource::COLUMN_ONLINE_QTY] = (int)$this->getRequestData()->getQty();
        }

        $data[AmazonListingProductResource::COLUMN_ONLINE_QTY_LAST_UPDATE_DATE] = null;
        if ($updateRequestDate !== null) {
            $data[AmazonListingProductResource::COLUMN_ONLINE_QTY_LAST_UPDATE_DATE] =
                $updateRequestDate->format('Y-m-d H:i:s');
        }

        $onlineQtyLastUpdateDate = $this->getAmazonListingProduct()->getOnlineQtyLastUpdateDate();
        if (
            $onlineQtyLastUpdateDate !== null
            && $updateRequestDate !== null
            && $onlineQtyLastUpdateDate > $updateRequestDate
        ) {
            unset($data[AmazonListingProductResource::COLUMN_ONLINE_QTY]);
            unset($data[AmazonListingProductResource::COLUMN_ONLINE_QTY_LAST_UPDATE_DATE]);
        }

        if (isset($data[AmazonListingProductResource::COLUMN_ONLINE_QTY])) {
            if ((int)$data[AmazonListingProductResource::COLUMN_ONLINE_QTY] > 0) {
                $data['status'] = \Ess\M2ePro\Model\Listing\Product::STATUS_LISTED;
            } else {
                $data['status'] = \Ess\M2ePro\Model\Listing\Product::STATUS_INACTIVE;
            }
        }

        $data['online_handling_time'] = $this->getRequestData()->getHandlingTime();

        if ($this->getRequestData()->hasRestockDate()) {
            $data['online_restock_date'] = $this->getRequestData()->getRestockDate();
        }

        return $data;
    }

    protected function appendRegularPriceValues($data)
    {
        if (!$this->getRequestData()->hasRegularPrice()) {
            return $data;
        }

        $data['online_regular_price'] = (float)$this->getRequestData()->getRegularPrice();

        $data['online_regular_sale_price'] = null;
        $data['online_regular_sale_price_start_date'] = null;
        $data['online_regular_sale_price_end_date'] = null;

        if ($this->getRequestData()->hasRegularSalePrice()) {
            $salePrice = (float)$this->getRequestData()->getRegularSalePrice();

            if ($salePrice > 0) {
                $data['online_regular_sale_price'] = $salePrice;
                $data['online_regular_sale_price_start_date'] = $this->getRequestData()->getRegularSalePriceStartDate();
                $data['online_regular_sale_price_end_date'] = $this->getRequestData()->getRegularSalePriceEndDate();
            } else {
                $data['online_regular_sale_price'] = 0;
            }
        }

        $data[AmazonListingProductResource::COLUMN_ONLINE_REGULAR_MAP_PRICE] = $this->requestData->getMapPrice();

        return $data;
    }

    protected function appendBusinessPriceValues($data)
    {
        if ($this->getRequestData()->hasDeleteBusinessPriceFlag()) {
            $data['online_business_price'] = null;
            $data['online_business_discounts'] = null;

            return $data;
        }

        if (!$this->getRequestData()->hasBusinessPrice()) {
            return $data;
        }

        $data['online_business_price'] = (float)$this->getRequestData()->getBusinessPrice();

        if ($this->getRequestData()->hasBusinessDiscounts()) {
            $businessDiscounts = $this->getRequestData()->getBusinessDiscounts();

            $data['online_business_discounts'] = \Ess\M2ePro\Helper\Json::encode($businessDiscounts['values']);
        } else {
            $data['online_business_discounts'] = null;
        }

        return $data;
    }

    protected function appendDetailsValues($data)
    {
        $requestMetadata = $this->getRequestMetaData();
        if (!isset($requestMetadata['details_data'])) {
            return $data;
        }

        $data['online_details_data'] = $this->getHelper('Data')->hashString(
            \Ess\M2ePro\Helper\Json::encode($requestMetadata['details_data']),
            'md5'
        );

        return $data;
    }

    protected function appendGiftSettingsStatus($data)
    {
        if (!$this->getRequestData()->hasGiftWrap() && !$this->getRequestData()->hasGiftMessage()) {
            return $data;
        }

        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (!$this->getRequestData()->getGiftWrap() && !$this->getRequestData()->getGiftMessage()) {
            $data['additional_data']['online_gift_settings_disabled'] = true;
        } else {
            $data['additional_data']['online_gift_settings_disabled'] = false;
        }

        return $data;
    }

    protected function setLastSynchronizationDates()
    {
        if (!$this->getConfigurator()->isQtyAllowed() && !$this->getConfigurator()->isRegularPriceAllowed()) {
            return;
        }

        $additionalData = $this->getListingProduct()->getAdditionalData();

        if ($this->getConfigurator()->isQtyAllowed()) {
            $additionalData['last_synchronization_dates']['qty']
                = \Ess\M2ePro\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');
        }

        if ($this->getConfigurator()->isRegularPriceAllowed()) {
            $additionalData['last_synchronization_dates']['price']
                = \Ess\M2ePro\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');
        }

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
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

        if ($this->getConfigurator()->isRegularPriceAllowed() || $this->getConfigurator()->isBusinessPriceAllowed()) {
            $instructions[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => ChangeProcessor::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => 80,
            ];
        }

        if ($this->getConfigurator()->isDetailsAllowed()) {
            $instructions[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => ChangeProcessor::INSTRUCTION_TYPE_DETAILS_DATA_CHANGED,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => 60,
            ];
        }

        $this->activeRecordFactory->getObject('Listing_Product_Instruction')->getResource()->add($instructions);
    }

    protected function getUpdateRequestDate(array $params): ?\DateTime
    {
        if (isset($params['system_items_update_request_date'])) {
            return \Ess\M2ePro\Helper\Date::createDateGmt($params['system_items_update_request_date']);
        }

        return null;
    }
}
