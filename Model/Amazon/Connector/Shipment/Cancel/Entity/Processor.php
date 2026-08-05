<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Connector\Shipment\Cancel\Entity;

class Processor
{
    private \Ess\M2ePro\Model\Amazon\Connector\Dispatcher $dispatcher;

    public function __construct(\Ess\M2ePro\Model\Amazon\Connector\Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function process(
        \Ess\M2ePro\Model\Account $account,
        string $shipmentId
    ) {
        $connector = $this->dispatcher->getConnectorByClass(
            Command::class,
            ['shipment_id' => $shipmentId],
            $account,
        );

        $this->dispatcher->process($connector);

        return $connector->getResponseData();
    }
}
