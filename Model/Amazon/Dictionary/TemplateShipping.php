<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Dictionary;

use Ess\M2ePro\Model\ResourceModel\Amazon\Dictionary\TemplateShipping as TemplateShippingResource;

class TemplateShipping extends \Ess\M2ePro\Model\ActiveRecord\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(TemplateShippingResource::class);
    }

    public function init(int $accountId, string $templateId, string $title): self
    {
        return $this
            ->setData(TemplateShippingResource::COLUMN_ACCOUNT_ID, $accountId)
            ->setData(TemplateShippingResource::COLUMN_TEMPLATE_ID, $templateId)
            ->setData(TemplateShippingResource::COLUMN_TITLE, $title);
    }

    public function getTemplateId(): string
    {
        return (string)$this->getData(TemplateShippingResource::COLUMN_TEMPLATE_ID);
    }

    public function getTitle(): string
    {
        return (string)$this->getData(TemplateShippingResource::COLUMN_TITLE);
    }
}
