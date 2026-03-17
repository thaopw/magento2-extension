<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Connector\Account\Get\ShippingTemplatesInfo;

class Command extends \Ess\M2ePro\Model\Amazon\Connector\Command\RealTime
{
    protected function getRequestData(): array
    {
        return [];
    }

    protected function getCommand(): array
    {
        return ['account', 'get', 'shippingTemplatesInfo'];
    }

    protected function validateResponse(): bool
    {
        $responseData = $this->getResponse()->getResponseData();
        if (!isset($responseData['templates']) || !is_array($responseData['templates'])) {
            return false;
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    protected function prepareResponseData(): void
    {
        $rawResponse = $this->getResponse()->getResponseData();

        $templates = [];
        foreach ($rawResponse['templates'] as $template) {
            $templates[] = new Template(
                (string)$template['id'],
                (string)$template['name']
            );
        }

        $this->responseData = new Response($templates);
    }

    public function getResponseData(): Response
    {
        return $this->responseData;
    }
}
