<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Connector\Shipment\Add\Entity;

class Command extends \Ess\M2ePro\Model\Amazon\Connector\Command\RealTime
{
    protected function getRequestData()
    {
        return $this->params['request_data'];
    }

    protected function getCommand(): array
    {
        return ['shipment', 'add', 'entity'];
    }
}
