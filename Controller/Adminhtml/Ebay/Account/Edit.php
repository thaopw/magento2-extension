<?php

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Account;

class Edit extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Account
{
    /** @var \Ess\M2ePro\Helper\Data */
    private $helperData;

    /** @var \Ess\M2ePro\Helper\Data\GlobalData */
    private $helperDataGlobalData;

    public function __construct(
        \Ess\M2ePro\Helper\Data $helperData,
        \Ess\M2ePro\Helper\Data\GlobalData $helperDataGlobalData,
        \Ess\M2ePro\Model\Ebay\Account\Store\Category\Update $storeCategoryUpdate,
        \Ess\M2ePro\Helper\Component\Ebay\Category\Store $componentEbayCategoryStore,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($storeCategoryUpdate, $componentEbayCategoryStore, $ebayFactory, $context);

        $this->helperData = $helperData;
        $this->helperDataGlobalData = $helperDataGlobalData;
    }

    protected function getLayoutType()
    {
        return self::LAYOUT_TWO_COLUMNS;
    }

    public function execute()
    {
        $account = null;
        if ($id = $this->getRequest()->getParam('id')) {
            $account = $this->ebayFactory->getObjectLoaded('Account', $id);
        }

        if ($account === null && $id) {
            $this->messageManager->addError($this->__('Account does not exist.'));

            return $this->_redirect('*/ebay_account');
        }

        if ($account !== null) {
            $this->addLicenseMessage($account);
        }

        $headerText = $this->__('Edit Account');
        $headerText .= ' "' . $this->helperData->escapeHtml($account->getTitle()) . '"';

        $this->getResultPage()->getConfig()->getTitle()->prepend($headerText);

        $this->addLeft(
            $this->getLayout()->createBlock(
                \Ess\M2ePro\Block\Adminhtml\Ebay\Account\Edit\Tabs::class,
                '',
                [
                    'account' => $account
                ]
            )
        );
        $this->addContent($this->getLayout()->createBlock(\Ess\M2ePro\Block\Adminhtml\Ebay\Account\Edit::class));

        $this->setPageHelpLink('display/eBayMagentoV6X/Accounts');

        return $this->getResultPage();
    }

    private function addLicenseMessage(\Ess\M2ePro\Model\Account $account)
    {
        try {
            /** @var \Ess\M2ePro\Model\M2ePro\Connector\Dispatcher $dispatcherObject */
            $dispatcherObject = $this->modelFactory->getObject('M2ePro\Connector\Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('account', 'get', 'info', [
                'account' => $account->getChildObject()->getServerHash(),
                'channel' => \Ess\M2ePro\Helper\Component\Ebay::NICK,
            ]);

            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (\Exception $e) {
            return;
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
