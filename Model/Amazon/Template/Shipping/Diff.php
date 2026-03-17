<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Template\Shipping;

class Diff extends \Ess\M2ePro\Model\ActiveRecord\Diff
{
    public function isDifferent(): bool
    {
        return $this->isDetailsDifferent();
    }

    public function isDetailsDifferent(): bool
    {
        $keys = [
            'mode',
            'template_id',
            'custom_attribute',
        ];

        return $this->isSettingsDifferent($keys);
    }
}
