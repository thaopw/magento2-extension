<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Template\Shipping;

class Refresh extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Template
{
    private \Ess\M2ePro\Model\Amazon\Account\Repository $amazonAccountRepository;
    private \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping\Update $templateShippingUpdate;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Account\Repository $amazonAccountRepository,
        \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping\Update $templateShippingUpdate,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($amazonFactory, $context);
        $this->amazonAccountRepository = $amazonAccountRepository;
        $this->templateShippingUpdate = $templateShippingUpdate;
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function execute()
    {
        $amazonAccount = $this->amazonAccountRepository
            ->get((int)$this->getRequest()->getParam('account_id'));

        /** @var \Ess\M2ePro\Model\Account $account */
        $account = $amazonAccount->getParentObject();

        $this->templateShippingUpdate->process($account);

        return $this->getResult();
    }
}
