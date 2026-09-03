<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Walmart\Listing\Unmanaged;

class Index extends \Ess\M2ePro\Controller\Adminhtml\Walmart\Listing\Unmanaged
{
    private \Ess\M2ePro\Model\Listing\Other\UiRuleManager $uiRuleManager;
    private \Ess\M2ePro\Model\Walmart\Account\Repository $accountRepository;

    public function __construct(
        \Ess\M2ePro\Model\Listing\Other\UiRuleManager $uiRuleManager,
        \Ess\M2ePro\Model\Walmart\Account\Repository $accountRepository,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Walmart\Factory $walmartFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($walmartFactory, $context);
        $this->uiRuleManager = $uiRuleManager;
        $this->accountRepository = $accountRepository;
    }

    public function execute()
    {
        $this->uiRuleManager->setRuleModel(
            \Ess\M2ePro\Model\Walmart\Magento\Product\UnmanagedRule::NICK,
            $this->getStoreId(),
            $this->getRequest()
        );
        if ($this->getRequest()->getQuery('ajax')) {
            $this->setAjaxContent(
                $this->getLayout()->createBlock(\Ess\M2ePro\Block\Adminhtml\Walmart\Listing\Unmanaged\Grid::class)
            );

            return $this->getResult();
        }

        $this->addContent(
            $this->getLayout()->createBlock(\Ess\M2ePro\Block\Adminhtml\Walmart\Listing\Unmanaged::class)
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('All Unmanaged Items'));

        $this->setPageHelpLink('unmanaged-items');

        return $this->getResult();
    }

    private function getStoreId(): int
    {
        $account = $this->accountRepository
            ->find((int)$this->getRequest()->getParam('walmartAccount'));

        if ($account === null) {
            return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        /** @var \Ess\M2ePro\Model\Walmart\Account $walmartAccount */
        $walmartAccount  = $account->getChildObject();

        return $walmartAccount->getRelatedStoreId();
    }
}
