<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Template\Shipping;

class ChangeProcessor extends \Ess\M2ePro\Model\Amazon\Template\ChangeProcessor\ChangeProcessorAbstract
{
    public const INSTRUCTION_INITIATOR = 'template_shipping_change_processor';

    /**
     * @return string
     */
    protected function getInstructionInitiator(): string
    {
        return self::INSTRUCTION_INITIATOR;
    }

    /**
     * @param \Ess\M2ePro\Model\Amazon\Template\Shipping\Diff $diff
     * @param $status
     *
     * @return array
     */
    protected function getInstructionsData(\Ess\M2ePro\Model\ActiveRecord\Diff $diff, $status): array
    {
        $data = [];
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

        return $data;
    }
}
