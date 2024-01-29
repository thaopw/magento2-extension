<?php

namespace Ess\M2ePro\Setup\Upgrade\v1_56_1__v1_57_0;

use Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractConfig;

class Config extends AbstractConfig
{
    public function getFeaturesList(): array
    {
        return [
            '@y24_m01/AddListingProductAdvancedFilterTable',
        ];
    }
}
