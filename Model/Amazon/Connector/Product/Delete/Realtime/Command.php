<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Connector\Product\Delete\Realtime;

class Command extends \Ess\M2ePro\Model\Amazon\Connector\Command\RealTime
{
    protected function getRequestData(): array
    {
        return [
            'items' => $this->params['items'],
            'realtime' => true,
        ];
    }

    protected function getCommand(): array
    {
        return ['product', 'delete', 'entities'];
    }
}
