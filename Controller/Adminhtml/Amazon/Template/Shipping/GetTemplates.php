<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Template\Shipping;

class GetTemplates extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Account
{
    private \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping\Repository $templateShippingRepository;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping\Repository $templateShippingRepository,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($amazonFactory, $context);

        $this->templateShippingRepository = $templateShippingRepository;
    }

    public function execute()
    {
        $templates = $this->templateShippingRepository
            ->getByAccountId((int)$this->getRequest()->getParam('account_id'));

        $response = [];
        foreach ($templates as $template) {
            $response[] = [
                'template_id' => $template->getTemplateId(),
                'title' => $template->getTitle(),
            ];
        }

        $this->setJsonContent($response);

        return $this->getResult();
    }
}
