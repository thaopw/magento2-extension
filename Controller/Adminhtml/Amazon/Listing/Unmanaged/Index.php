<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Unmanaged;

class Index extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Listing
{
    private \Ess\M2ePro\Model\Listing\Other\UiRuleManager $uiRuleManager;
    private \Ess\M2ePro\Model\Amazon\Account\Repository $amazonAccountRepository;

    public function __construct(
        \Ess\M2ePro\Model\Listing\Other\UiRuleManager $uiRuleManager,
        \Ess\M2ePro\Model\Amazon\Account\Repository $amazonAccountRepository,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($amazonFactory, $context);
        $this->uiRuleManager = $uiRuleManager;
        $this->amazonAccountRepository = $amazonAccountRepository;
    }

    public function execute()
    {
        $this->uiRuleManager->setRuleModel(
            \Ess\M2ePro\Model\Amazon\Magento\Product\UnmanagedRule::NICK,
            $this->getStoreId(),
            $this->getRequest()
        );

        if ($this->isAjax()) {
            $this->setAjaxContent(
                $this->getLayout()->createBlock(\Ess\M2ePro\Block\Adminhtml\Amazon\Listing\Unmanaged\Grid::class)
            );

            return $this->getResult();
        }

        $this->addContent(
            $this->getLayout()->createBlock(\Ess\M2ePro\Block\Adminhtml\Amazon\Listing\Unmanaged::class)
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('All Unmanaged Items'));

        $this->setPageHelpLink('unmanaged-listings');

        return $this->getResult();
    }

    private function getStoreId(): int
    {
        $account = $this->amazonAccountRepository
            ->find((int)$this->getRequest()->getParam('amazonAccount'));
        if ($account === null) {
            return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        return $account->getRelatedStoreId();
    }
}
