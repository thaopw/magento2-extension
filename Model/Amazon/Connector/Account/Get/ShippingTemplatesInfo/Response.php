<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Connector\Account\Get\ShippingTemplatesInfo;

class Response
{
    /**
     * @var \Ess\M2ePro\Model\Amazon\Connector\Account\Get\ShippingTemplatesInfo\Template[]
     */
    private array $templates;

    /**
     * @param \Ess\M2ePro\Model\Amazon\Connector\Account\Get\ShippingTemplatesInfo\Template[] $templates
     */
    public function __construct(array $templates)
    {
        $this->templates = $templates;
    }

    public function isEmpty(): bool
    {
        return count($this->templates) === 0;
    }

    /**
     * @return \Ess\M2ePro\Model\Amazon\Connector\Account\Get\ShippingTemplatesInfo\Template[]
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }
}
