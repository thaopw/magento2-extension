<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Api\MerchantFulfillment\GetShipmentServices;

class Response implements \Ess\M2ePro\Api\Amazon\MerchantFulfillment\GetShipmentServices\ResponseInterface
{
    private array $rawResponse;

    public function __construct(array $rawResponse)
    {
        $this->rawResponse = $rawResponse;
    }

    public function getAvailableItems(): array
    {
        $availableItems = [];
        foreach ($this->rawResponse['items']['available'] as $rawItem) {
            $availableItems[] = new \Ess\M2ePro\Model\Amazon\Api\MerchantFulfillment\GetShipmentServices\Data\AvailableItem(
                (string)$rawItem['id'],
                (string)$rawItem['name'],
                (string)$rawItem['date']['ship'],
                (string)$rawItem['date']['estimated_delivery']['latest'],
                (float)$rawItem['rate']['amount'],
                (string)$rawItem['rate']['currency_code']
            );
        }

        return $availableItems;
    }

    public function getUnavailable(): array
    {
        return $this->rawResponse['items']['unavailable'];
    }

    public function getNotAcceptedItems(): array
    {
        return $this->rawResponse['not_accepted'];
    }
}
