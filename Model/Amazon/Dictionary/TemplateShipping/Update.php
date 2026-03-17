<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping;

class Update
{
    private \Ess\M2ePro\Model\Amazon\Connector\Account\Get\ShippingTemplatesInfo\Processor $shippingTemplatesInfoProcessor;
    private Repository $templateShippingRepository;
    private \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShippingFactory $templateShippingFactory;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Connector\Account\Get\ShippingTemplatesInfo\Processor $shippingTemplatesInfoProcessor,
        Repository $templateShippingRepository,
        \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShippingFactory $templateShippingFactory
    ) {
        $this->shippingTemplatesInfoProcessor = $shippingTemplatesInfoProcessor;
        $this->templateShippingRepository = $templateShippingRepository;
        $this->templateShippingFactory = $templateShippingFactory;
    }

    public function process(\Ess\M2ePro\Model\Account $account): void
    {
        $response = $this->shippingTemplatesInfoProcessor->process($account);
        if ($response->isEmpty()) {
            return;
        }

        $accountId = (int)$account->getId();
        $this->templateShippingRepository->deleteAllByAccountId($accountId);
        foreach ($response->getTemplates() as $template) {
            $templateShipping = $this->templateShippingFactory
                ->create()
                ->init($accountId, $template->id, $template->name);
            $this->templateShippingRepository->create($templateShipping);
        }
    }
}
