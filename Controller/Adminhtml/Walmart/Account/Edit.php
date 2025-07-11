<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Walmart\Account;

use Ess\M2ePro\Controller\Adminhtml\Walmart\Account;

class Edit extends Account
{
    /** @var \Ess\M2ePro\Helper\Component\Walmart */
    private $walmartHelper;

    /** @var \Ess\M2ePro\Helper\Data\GlobalData */
    private $globalData;

    /** @var \Ess\M2ePro\Helper\Data */
    private $dataHelper;
    private \Ess\M2ePro\Model\Walmart\Account\Repository $accountRepository;

    public function __construct(
        \Ess\M2ePro\Model\Walmart\Account\Repository $accountRepository,
        \Ess\M2ePro\Helper\Component\Walmart $walmartHelper,
        \Ess\M2ePro\Helper\Data\GlobalData $globalData,
        \Ess\M2ePro\Helper\Data $dataHelper,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Walmart\Factory $walmartFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($walmartFactory, $context);
        $this->accountRepository = $accountRepository;
        $this->walmartHelper = $walmartHelper;
        $this->globalData = $globalData;
        $this->dataHelper = $dataHelper;
    }

    protected function getLayoutType()
    {
        return self::LAYOUT_TWO_COLUMNS;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $account = $this->accountRepository->find($id);

        if ($account === null) {
            $this->messageManager->addError($this->__('Account does not exist.'));

            return $this->_redirect('*/walmart_account');
        }

        $marketplaces = $this->walmartHelper->getMarketplacesAvailableForApiCreation();
        if ($marketplaces->getSize() <= 0) {
            $message = 'You should select and update at least one Walmart marketplace.';
            $this->messageManager->addError($this->__($message));

            return $this->_redirect('*/walmart_account');
        }

        $this->addLicenseMessage($account);

        $this->globalData->setValue('edit_account', $account);

        // Set header text
        // ---------------------------------------

        $headerTextEdit = $this->__('Edit Account');

        $headerText = $headerTextEdit;
        $headerText .= ' "' . $this->dataHelper->escapeHtml($account->getTitle()) . '"';

        $this->getResultPage()->getConfig()->getTitle()->prepend($headerText);

        // ---------------------------------------

        $this->addLeft($this->getLayout()->createBlock(\Ess\M2ePro\Block\Adminhtml\Walmart\Account\Edit\Tabs::class));
        $this->addContent($this->getLayout()->createBlock(\Ess\M2ePro\Block\Adminhtml\Walmart\Account\Edit::class));
        $this->setPageHelpLink('help/m2/walmart-integration/account-configurations');

        return $this->getResultPage();
    }

    private function addLicenseMessage(\Ess\M2ePro\Model\Account $account)
    {
        try {
            /** @var \Ess\M2ePro\Model\M2ePro\Connector\Dispatcher $dispatcherObject */
            $dispatcherObject = $this->modelFactory->getObject('M2ePro\Connector\Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('account', 'get', 'info', [
                'account' => $account->getChildObject()->getServerHash(),
                'channel' => \Ess\M2ePro\Helper\Component\Walmart::NICK,
            ]);

            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (\Exception $e) {
            return '';
        }

        if (!isset($response['info']['status']) || empty($response['info']['note'])) {
            return;
        }

        $status = (bool)$response['info']['status'];
        $note = $response['info']['note'];

        if ($status) {
            $this->addExtendedNoticeMessage($note);

            return;
        }

        $errorMessage = $this->__(
            'Work with this Account is currently unavailable for the following reason: <br/> %error_message%',
            ['error_message' => $note]
        );

        $this->addExtendedErrorMessage($errorMessage);
    }
}
