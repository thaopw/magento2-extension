<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Connector\Shipment\Get\Offers;

class Command extends \Ess\M2ePro\Model\Amazon\Connector\Command\RealTime
{
    protected function getRequestData(): array
    {
        return $this->params['request_data'];
    }

    protected function getCommand(): array
    {
        return ['shipment', 'get', 'offers'];
    }
}
