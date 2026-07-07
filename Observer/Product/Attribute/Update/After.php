<?php

declare(strict_types=1);

namespace Ess\M2ePro\Observer\Product\Attribute\Update;

class After extends \Ess\M2ePro\Observer\AbstractModel
{
    private \Ess\M2ePro\Model\Listing\Auto\Actions\Mode\Factory $listingAutoActionsModeFactory;
    private \Ess\M2ePro\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory;

    public function __construct(
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\Listing\Auto\Actions\Mode\Factory $listingAutoActionsModeFactory,
        \Ess\M2ePro\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory
    ) {
        parent::__construct($helperFactory, $activeRecordFactory, $modelFactory);
        $this->listingAutoActionsModeFactory = $listingAutoActionsModeFactory;
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
    }

    public function process()
    {
        $affectedProductIds = $this->getAffectedProductIds();
        if (empty($affectedProductIds)) {
            return;
        }

        foreach ($affectedProductIds as $affectedProductId) {
            $this->processAdvancedFilterActions($affectedProductId);
        }
    }

    private function processAdvancedFilterActions(int $magentoProductId): void
    {
        $autoAction = $this->listingAutoActionsModeFactory->createAdvancedFilterMode();
        $autoAction->synchByProductId($magentoProductId);
    }

    /**
     * @return int[]
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    private function getAffectedProductIds(): array
    {
        $affectedProductIds = $this->getEventObserver()->getData('product_ids');
        if (empty($affectedProductIds)) {
            return [];
        }

        return array_map('intval', $affectedProductIds);
    }
}
