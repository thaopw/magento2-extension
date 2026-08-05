<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Connector\Shipment\Cancel\Entity;

class Command extends \Ess\M2ePro\Model\Amazon\Connector\Command\RealTime
{
    protected function getRequestData(): array
    {
        return [
            'shipment_id' => $this->params['shipment_id']
        ];
    }

    protected function getCommand(): array
    {
        return ['shipment', 'cancel', 'entity'];
    }
}
