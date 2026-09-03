<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Listing;

class ChangeProcessor extends \Ess\M2ePro\Model\Amazon\Template\ChangeProcessor\ChangeProcessorAbstract
{
    public const INSTRUCTION_TYPE_CONDITION_DATA_CHANGED = 'listing_condition_data_changed';
    public const INSTRUCTION_TYPE_SKU_SETTINGS_CHANGED = 'listing_sku_settings_changed';

    public const INSTRUCTION_INITIATOR = 'listing_change_processor';

    protected function getInstructionInitiator(): string
    {
        return self::INSTRUCTION_INITIATOR;
    }

    /**
     * @param Diff $diff
     * @param $status
     *
     * @return array
     */
    protected function getInstructionsData(\Ess\M2ePro\Model\ActiveRecord\Diff $diff, $status): array
    {
        $data = [];

        if ($diff->isQtyDifferent()) {
            $priority = 5;

            if ($status == \Ess\M2ePro\Model\Listing\Product::STATUS_LISTED) {
                $priority = 40;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        if ($diff->isConditionDifferent()) {
            $priority = 5;

            if ($status == \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED) {
                $priority = 30;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_CONDITION_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        if ($diff->isDetailsDifferent()) {
            $priority = 5;

            if ($status == \Ess\M2ePro\Model\Listing\Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_DETAILS_DATA_CHANGED,
                'priority' => $priority,
            ];
        }

        if ($diff->isSkuSettingsDifferent()) {
            $priority = 0;

            if ($status == \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED) {
                $priority = 30;
            }

            $data[] = [
                'type' => self::INSTRUCTION_TYPE_SKU_SETTINGS_CHANGED,
                'priority' => $priority,
            ];
        }

        return $data;
    }
}
