<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Connector\Account\Get\ShippingTemplatesInfo;

class Template
{
    public string $id;
    public string $name;

    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}
