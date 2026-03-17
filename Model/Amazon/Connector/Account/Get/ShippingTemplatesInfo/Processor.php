<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Connector\Account\Get\ShippingTemplatesInfo;

class Processor
{
    private \Ess\M2ePro\Model\Amazon\Connector\DispatcherFactory $dispatcherFactory;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Connector\DispatcherFactory $dispatcherFactory
    ) {
        $this->dispatcherFactory = $dispatcherFactory;
    }

    public function process(\Ess\M2ePro\Model\Account $account): Response
    {
        $dispatcher = $this->dispatcherFactory->create();

        /** @var Command $command */
        $command = $dispatcher->getConnectorByClass(
            Command::class,
            [],
            $account
        );

        $dispatcher->process($command);

        return $command->getResponseData();
    }
}
