<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Connector\Product\Delete\Realtime;

class Processor
{
    private \Ess\M2ePro\Model\Amazon\Connector\Dispatcher $dispatcher;

    public function __construct(\Ess\M2ePro\Model\Amazon\Connector\Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function process(\Ess\M2ePro\Model\Account $account, array $items): void
    {
        $connector = $this->dispatcher->getConnectorByClass(
            Command::class,
            ['items' => $items],
            $account
        );

        $this->dispatcher->process($connector);
    }
}
