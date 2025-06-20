<?php

namespace Ess\M2ePro\Setup\Upgrade\v1_3_3__v1_3_4;

use Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractFeature;

class RemoveMigrationFromM1 extends AbstractFeature
{
    public function execute()
    {
        $wizardTable = $this->getFullTableName('wizard');

        $this->getConnection()->query(<<<SQL
DELETE FROM {$wizardTable} WHERE `nick` = 'migrationFromMagento1'
SQL
        );
    }
}
