<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Listing\Other;

class UiRuleManager
{
    private \Ess\M2ePro\Helper\Data\GlobalData $globalDataHelper;
    private \Ess\M2ePro\Block\Adminhtml\Magento\Product\Rule\ViewStateFactory $viewStateFactory;
    private \Ess\M2ePro\Block\Adminhtml\Magento\Product\Rule\ViewState\Manager $viewStateManager;
    private \Ess\M2ePro\Model\Listing\Product\AdvancedFilter\Manager $advancedFilterManager;
    private \Ess\M2ePro\Helper\Data\Session $sessionHelper;
    private \Ess\M2ePro\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;

    public function __construct(
        \Ess\M2ePro\Helper\Data\GlobalData $globalDataHelper,
        \Ess\M2ePro\Block\Adminhtml\Magento\Product\Rule\ViewStateFactory $viewStateFactory,
        \Ess\M2ePro\Block\Adminhtml\Magento\Product\Rule\ViewState\Manager $viewStateManager,
        \Ess\M2ePro\Model\Listing\Product\AdvancedFilter\Manager $advancedFilterManager,
        \Ess\M2ePro\Helper\Data\Session $sessionHelper,
        \Ess\M2ePro\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory
    ) {
        $this->globalDataHelper = $globalDataHelper;
        $this->viewStateFactory = $viewStateFactory;
        $this->viewStateManager = $viewStateManager;
        $this->advancedFilterManager = $advancedFilterManager;
        $this->sessionHelper = $sessionHelper;
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
    }

    public function setRuleModel(
        string $ruleNick,
        int $storeId,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $viewKey = $ruleNick . '_view_key';

        $getRuleBySessionData = function () use ($ruleNick, $storeId, $request) {
            return $this->createRuleBySessionData($ruleNick, $storeId, $request);
        };

        $ruleModel = $this->viewStateManager->getRuleWithViewState(
            $this->viewStateFactory->create($viewKey),
            $ruleNick,
            $getRuleBySessionData,
            $storeId
        );

        $this->globalDataHelper->setValue('rule_model', $ruleModel);
    }

    public function appendAdvancedFilterToCollection(
        \Ess\M2ePro\Model\ResourceModel\Listing\Other\Collection $collection
    ): void {
        $ruleModel = $this->getRuleModel();

        if ($ruleModel === null) {
            return;
        }

        if (count($ruleModel->getConditions()->getData($ruleModel->getPrefix())) <= 0) {
            return;
        }

        $clone = clone $collection;
        $clone
            ->getSelect()
            ->distinct()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('main_table.product_id')
            ->where('main_table.product_id IS NOT NULL');

        $magentoProductCollection = $this->magentoProductCollectionFactory
            ->create()
            ->setStoreId($ruleModel->getStoreId());
        $magentoProductCollection
            ->getSelect()
            ->join(
                ['unmanaged' => $clone->getSelect()],
                'unmanaged.product_id = e.entity_id',
                []
            )
            ->where('unmanaged.product_id IS NOT NULL')
            ->distinct()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('e.entity_id');

        $ruleModel->setAttributesFilterToCollection($magentoProductCollection);

        $collection
            ->getSelect()
            ->joinLeft(
                ['magento_products' => $magentoProductCollection->getSelect()],
                'magento_products.entity_id = main_table.product_id',
                []
            )
            ->where('magento_products.entity_id IS NOT NULL');
    }

    private function getRuleModel(): ?\Ess\M2ePro\Model\Magento\Product\Rule
    {
        $value = $this->globalDataHelper->getValue('rule_model');
        if ($value instanceof \Ess\M2ePro\Model\Magento\Product\Rule) {
            return $value;
        }

        return null;
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception
     */
    private function createRuleBySessionData(
        string $ruleNick,
        int $storeId,
        \Magento\Framework\App\RequestInterface $request
    ): \Ess\M2ePro\Model\Magento\Product\Rule {
        $rulePrefix = $ruleNick . '_prefix';

        $this->globalDataHelper->setValue('rule_prefix', $rulePrefix);

        $ruleModel = $this->advancedFilterManager->getRuleModelByNick($ruleNick, $storeId);

        /** @psalm-suppress UndefinedInterfaceMethod */
        $ruleParam = $request->getPost('rule');
        if (!empty($ruleParam)) {
            /** @psalm-suppress UndefinedInterfaceMethod */
            $postValues = $request->getPostValue();
            $this->sessionHelper->setValue(
                $rulePrefix,
                $ruleModel->getSerializedFromPost($postValues)
            );
        } elseif ($ruleParam !== null) {
            $this->sessionHelper->setValue($rulePrefix, []);
        }

        $sessionRuleData = $this->sessionHelper->getValue($rulePrefix);
        if (!empty($sessionRuleData)) {
            $ruleModel->loadFromSerialized($sessionRuleData);
        }

        return $ruleModel;
    }
}
