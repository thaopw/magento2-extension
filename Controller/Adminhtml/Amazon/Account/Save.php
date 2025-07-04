<?php

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Account;

use Ess\M2ePro\Block\Adminhtml\Amazon\Account\Edit\Tabs\FbaInventory as FbaInventoryForm;

class Save extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Account
{
    private \Ess\M2ePro\Model\Amazon\Account\MagentoOrderCreateService $magentoOrderCreateService;
    private \Ess\M2ePro\Helper\Magento $magentoHelper;
    private \Ess\M2ePro\Model\Amazon\Account\Builder $accountBuilder;
    private \Ess\M2ePro\Helper\Module\Wizard $helperWizard;
    private \Ess\M2ePro\Helper\Url $urlHelper;
    private \Ess\M2ePro\Helper\Module\Exception $exceptionHelper;
    private \Ess\M2ePro\Helper\Module\Support $supportHelper;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Account\MagentoOrderCreateService $magentoOrderCreateService,
        \Ess\M2ePro\Helper\Magento $magentoHelper,
        \Ess\M2ePro\Model\Amazon\Account\Builder $accountBuilder,
        \Ess\M2ePro\Helper\Module\Wizard $helperWizard,
        \Ess\M2ePro\Helper\Url $urlHelper,
        \Ess\M2ePro\Helper\Module\Exception $exceptionHelper,
        \Ess\M2ePro\Helper\Module\Support $supportHelper,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($amazonFactory, $context);

        $this->magentoOrderCreateService = $magentoOrderCreateService;
        $this->magentoHelper = $magentoHelper;
        $this->accountBuilder = $accountBuilder;
        $this->helperWizard = $helperWizard;
        $this->urlHelper = $urlHelper;
        $this->exceptionHelper = $exceptionHelper;
        $this->supportHelper = $supportHelper;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost();

        if (!$post->count()) {
            $this->_forward('index');
        }

        $id = (int)$this->getRequest()->getParam('id');
        $formData = $post->toArray();

        /** @var \Ess\M2ePro\Model\Account $account */
        $account = $this->amazonFactory->getObjectLoaded('Account', $id);
        /** @var \Ess\M2ePro\Model\Amazon\Account $amazonAccount */
        $amazonAccount = $account->getChildObject();

        if (empty($id) || !$account->getId()) {
            $this->messageManager->addErrorMessage(__('Account does not exists.'));

            return $this->_redirect('*/*/index');
        }

        $previousMagentoOrdersSettings = $this->getPreviousMagentoOrdersSettings($amazonAccount);

        try {
            $this->saveAccount($account, $formData);
        } catch (\Throwable $e) {
            $this->exceptionHelper->process($e);
            $this->messageManager->addErrorMessage(
                __(
                    'Unable to save configuration changes. If the issue persists,'
                    . ' please contact our support team at %supportEmail for further assistance.',
                    ['supportEmail' => $this->supportHelper->getContactEmail()]
                )
            );

            return $this->_redirect('*/*/index');
        }

        try {
            $this->createMagentoOrders($amazonAccount, $previousMagentoOrdersSettings);
        } catch (\Throwable $e) {
            $this->exceptionHelper->process($e);
        }

        if ($this->isAjax()) {
            $this->setJsonContent([
                'success' => true,
            ]);

            return $this->getResult();
        }

        $this->messageManager->addSuccessMessage(__('Account was saved'));

        $routerParams = ['id' => $account->getId(), '_current' => true];
        if (
            $this->helperWizard->isActive(\Ess\M2ePro\Helper\View\Amazon::WIZARD_INSTALLATION_NICK) &&
            $this->helperWizard->getStep(\Ess\M2ePro\Helper\View\Amazon::WIZARD_INSTALLATION_NICK) === 'account'
        ) {
            $routerParams['wizard'] = true;
        }

        return $this->_redirect($this->urlHelper->getBackUrl('list', [], ['edit' => $routerParams]));
    }

    private function saveAccount(\Ess\M2ePro\Model\Account $account, array $data): void
    {
        if (!empty($data['magento_orders_settings']['listing']['create_from_date'])) {
            $data['magento_orders_settings']['listing']['create_from_date'] =
                \Ess\M2ePro\Helper\Date::createDateInCurrentZone(
                    $data['magento_orders_settings']['listing']['create_from_date']
                );
        }

        if (!empty($data['magento_orders_settings']['listing_other']['create_from_date'])) {
            $data['magento_orders_settings']['listing_other']['create_from_date'] =
                \Ess\M2ePro\Helper\Date::createDateInCurrentZone(
                    $data['magento_orders_settings']['listing_other']['create_from_date']
                );
        }

        $isMsiSupported = $this->magentoHelper->isMSISupportingVersion();
        $inventorySourceName = $data[FbaInventoryForm::FORM_KEY_FBA_INVENTORY_SOURCE_NAME] ?? null;
        $data[FbaInventoryForm::FORM_KEY_FBA_INVENTORY_MODE] = ($isMsiSupported && $inventorySourceName !== null)
            ? $data[FbaInventoryForm::FORM_KEY_FBA_INVENTORY_MODE]
            : 0;

        $this->accountBuilder->build($account, $data);
    }

    private function getPreviousMagentoOrdersSettings(\Ess\M2ePro\Model\Amazon\Account $amazonAccount): array
    {
        return [
            'listing' => [
                'is_enabled' => $amazonAccount->isMagentoOrdersListingsModeEnabled(),
                'create_from_date' => $amazonAccount->getMagentoOrdersListingsCreateFromDate(),
            ],
            'listing_other' => [
                'is_enabled' => $amazonAccount->isMagentoOrdersListingsOtherModeEnabled(),
                'create_from_date' => $amazonAccount->getMagentoOrdersListingsOtherCreateFromDate(),
            ],
        ];
    }

    private function createMagentoOrders(
        \Ess\M2ePro\Model\Amazon\Account $amazonAccount,
        array $previousMagentoOrdersSettings
    ): void {
        if ($this->isNeedCreateMagentoOrdersListing($amazonAccount, $previousMagentoOrdersSettings)) {
            $this->magentoOrderCreateService->createMagentoOrdersListingsByFromDate(
                (int)$amazonAccount->getId(),
                $amazonAccount->getMagentoOrdersListingsCreateFromDate()
            );
        }

        if ($this->isNeedCreateMagentoOrdersListingOther($amazonAccount, $previousMagentoOrdersSettings)) {
            $this->magentoOrderCreateService->createMagentoOrdersListingsOtherByFromDate(
                (int)$amazonAccount->getId(),
                $amazonAccount->getMagentoOrdersListingsOtherCreateFromDate()
            );
        }
    }

    private function isNeedCreateMagentoOrdersListing(
        \Ess\M2ePro\Model\Amazon\Account $amazonAccount,
        array $previousMagentoOrdersSettings
    ): bool {
        if (!$amazonAccount->isMagentoOrdersListingsModeEnabled()) {
            return false;
        }

        if (!$amazonAccount->getMagentoOrdersListingsCreateFromDate()) {
            return false;
        }

        if (
            $previousMagentoOrdersSettings['listing']['is_enabled'] === false
            || $previousMagentoOrdersSettings['listing']['create_from_date'] === null
        ) {
            return true;
        }

        return $amazonAccount->getMagentoOrdersListingsCreateFromDate()->format('Y-m-d H:i')
            !== $previousMagentoOrdersSettings['listing']['create_from_date']->format('Y-m-d H:i');
    }

    private function isNeedCreateMagentoOrdersListingOther(
        \Ess\M2ePro\Model\Amazon\Account $amazonAccount,
        array $previousMagentoOrdersSettings
    ): bool {
        if (!$amazonAccount->isMagentoOrdersListingsOtherModeEnabled()) {
            return false;
        }

        if (!$amazonAccount->getMagentoOrdersListingsOtherCreateFromDate()) {
            return false;
        }

        if (
            $previousMagentoOrdersSettings['listing_other']['is_enabled'] === false
            || $previousMagentoOrdersSettings['listing_other']['create_from_date'] === null
        ) {
            return true;
        }

        return $amazonAccount->getMagentoOrdersListingsOtherCreateFromDate()->format('Y-m-d H:i')
            !== $previousMagentoOrdersSettings['listing_other']['create_from_date']->format('Y-m-d H:i');
    }
}
