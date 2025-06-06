<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Relist;

/**
 * Class \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Relist\Response
 */
class Response extends \Ess\M2ePro\Model\Amazon\Listing\Product\Action\Type\Response
{
    public const INSTRUCTION_TYPE_CHECK_QTY = 'success_relist_check_qty';
    public const INSTRUCTION_TYPE_CHECK_PRICE_REGULAR = 'success_relist_check_price_regular';
    public const INSTRUCTION_TYPE_CHECK_PRICE_BUSINESS = 'success_relist_check_price_business';
    public const INSTRUCTION_TYPE_CHECK_DETAILS = 'success_relist_check_details';

    //########################################

    /**
     * @ingeritdoc
     */
    public function processSuccess(array $params = []): void
    {
        $updateRequestDate = $this->getUpdateRequestDate($params);
        $data = [];

        if ($this->getConfigurator()->isDetailsAllowed()) {
            $data['defected_messages'] = null;
        }

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendQtyValues($data, $updateRequestDate);
        $data = $this->appendRegularPriceValues($data);
        $data = $this->appendBusinessPriceValues($data);

        $data = $this->processRecheckInstructions($data);

        if (isset($data['additional_data'])) {
            $data['additional_data'] = \Ess\M2ePro\Helper\Json::encode($data['additional_data']);
        }

        $this->getListingProduct()->addData($data);
        $this->getAmazonListingProduct()->addData($data);
        $this->getAmazonListingProduct()->setIsStoppedManually(false);

        $this->getListingProduct()->removeBlockingByError();

        $this->setLastSynchronizationDates();

        $this->getListingProduct()->save();
    }

    //########################################

    private function processRecheckInstructions(array $data)
    {
        if (!isset($data['additional_data'])) {
            $data['additional_data'] = $this->getListingProduct()->getAdditionalData();
        }

        if (empty($data['additional_data']['recheck_properties'])) {
            return $data;
        }

        $instructionsData = [];

        foreach ($data['additional_data']['recheck_properties'] as $property) {
            $instructionType = null;
            $instructionPriority = 0;

            switch ($property) {
                case 'qty':
                    $instructionType = self::INSTRUCTION_TYPE_CHECK_QTY;
                    $instructionPriority = 80;
                    break;

                case 'price_regular':
                    $instructionType = self::INSTRUCTION_TYPE_CHECK_PRICE_REGULAR;
                    $instructionPriority = 60;
                    break;

                case 'price_business':
                    $instructionType = self::INSTRUCTION_TYPE_CHECK_PRICE_BUSINESS;
                    $instructionPriority = 60;
                    break;

                case 'details':
                    $instructionType = self::INSTRUCTION_TYPE_CHECK_DETAILS;
                    $instructionPriority = 30;
                    break;
            }

            if ($instructionType === null) {
                continue;
            }

            $instructionsData[] = [
                'listing_product_id' => $this->getListingProduct()->getId(),
                'type' => $instructionType,
                'initiator' => self::INSTRUCTION_INITIATOR,
                'priority' => $instructionPriority,
            ];
        }

        $this->activeRecordFactory->getObject('Listing_Product_Instruction')->getResource()->add($instructionsData);

        unset($data['additional_data']['recheck_properties']);

        return $data;
    }

    //########################################
}
