<?php

namespace Ess\M2ePro\Setup\Upgrade\v1_3_3__v1_3_4;

use Ess\M2ePro\Model\Setup\Upgrade\Entity\AbstractFeature;

class MsrpRrp extends AbstractFeature
{
    public function execute()
    {
        $this->getTableModifier('amazon_template_description_definition')
             ->addColumn(
                 'msrp_rrp_mode',
                 'SMALLINT UNSIGNED NOT NULL',
                 '0',
                 'manufacturer_part_number_custom_attribute',
                 false,
                 false
             )
             ->addColumn('msrp_rrp_custom_attribute', 'VARCHAR(255)', 'NULL', 'msrp_rrp_mode', false, false)
             ->commit();
    }
}
