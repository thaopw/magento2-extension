<?php

namespace Ess\M2ePro\Setup\Upgrade\v1_0_0__v1_1_0;

use Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractConfig;

class Config extends AbstractConfig
{
    //########################################

    public function getFeaturesList()
    {
        return [
            'SynchronizationPolicySchedule',
            'ServicingMessages',
            'MaintenanceModeKey',
            'RemoveUnsupportedAmazonMarketplaces',
            'RemoveAutocomplete',
            'RemoveKillNow',
            'ModuleInPrimaryConfig',
            'MagentoMarketplaceURL',
            'SupportURLs',
            'RenameServerGroup'
        ];
    }

    //########################################
}
