<?php

declare(strict_types=1);

namespace Ess\M2ePro\Setup\Upgrade\v1_92_0__v1_92_1;

class Config extends \Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractConfig
{
    public function getFeaturesList(): array
    {
        return [
            '@y26_m04/FixAmazonTemplateShippingTableModeDefaultValue',
        ];
    }
}
