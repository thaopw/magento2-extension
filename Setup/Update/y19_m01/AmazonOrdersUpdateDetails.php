<?php

namespace Ess\M2ePro\Setup\Update\y19_m01;

use Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractFeature;

/**
 * Class \Ess\M2ePro\Setup\Update\y19\AmazonOrdersUpdateDetails_m01
 */
class AmazonOrdersUpdateDetails extends AbstractFeature
{
    public function execute()
    {
        $this->getConfigModifier('synchronization')
            ->getEntity('/amazon/orders/receive_details/', 'interval')
            ->updateValue('7200');
    }
}
