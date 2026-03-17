<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Template\Shipping;

class SetForProducts
{
    private array $cachedShippingTemplates = [];

    private \Ess\M2ePro\Model\ResourceModel\Listing\Product\CollectionFactory $listingProductCollectionFactory;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\Repository $shippingTemplateRepository;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\SnapshotBuilderFactory $snapshotBuilderFactory;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\DiffFactory $diffFactory;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\ChangeProcessorFactory $changeProcessorFactory;
    private \Magento\Framework\DB\TransactionFactory $transactionFactory;

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Listing\Product\CollectionFactory $listingProductCollectionFactory,
        Repository $shippingTemplateRepository,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\SnapshotBuilderFactory $snapshotBuilderFactory,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\DiffFactory $diffFactory,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\ChangeProcessorFactory $changeProcessorFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory
    ) {
        $this->listingProductCollectionFactory = $listingProductCollectionFactory;
        $this->shippingTemplateRepository = $shippingTemplateRepository;
        $this->snapshotBuilderFactory = $snapshotBuilderFactory;
        $this->diffFactory = $diffFactory;
        $this->changeProcessorFactory = $changeProcessorFactory;
        $this->transactionFactory = $transactionFactory;
    }

    public function execute(array $productsIds, ?int $templateId): void
    {
        if (empty($productsIds)) {
            return;
        }

        $listingProducts = $this->getListingProducts($productsIds);

        /** @var \Magento\Framework\DB\Transaction $transaction */
        $transaction = $this->transactionFactory->create();
        $oldTemplateIds = [];

        try {
            foreach ($listingProducts as $listingProduct) {
                /** @var \Ess\M2ePro\Model\Amazon\Listing\Product $amazonListingProduct */
                $amazonListingProduct = $listingProduct->getChildObject();
                $oldTemplateIds[$listingProduct->getId()] = $amazonListingProduct->getTemplateShippingId();
                $amazonListingProduct->setTemplateShippingId($templateId);

                $transaction->addObject($listingProduct);
            }

            $transaction->save();
        } catch (\Exception $e) {
            $oldTemplateIds = false;
        }

        if (empty($oldTemplateIds)) {
            return;
        }

        $newTemplate = $this->getTemplate($templateId);
        $newSnapshot = $this->createSnapshot($newTemplate);

        foreach ($listingProducts as $listingProduct) {
            $oldTemplate = $this->getTemplate($oldTemplateIds[$listingProduct->getId()]);
            $oldSnapshot = $this->createSnapshot($oldTemplate);

            if (empty($newSnapshot) && empty($oldSnapshot)) {
                continue;
            }

            $diff = $this->diffFactory->create();
            $diff->setOldSnapshot($oldSnapshot);
            $diff->setNewSnapshot($newSnapshot);

            $changeProcessor = $this->changeProcessorFactory->create();
            $changeProcessor->process(
                $diff,
                [
                    [
                        'id' => $listingProduct->getId(),
                        'status' => $listingProduct->getStatus(),
                    ],
                ]
            );
        }
    }

    /**
     * @return \Ess\M2ePro\Model\Listing\Product[]
     */
    private function getListingProducts(array $productsIds): array
    {
        $collection = $this->listingProductCollectionFactory->createWithAmazonChildMode();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Listing\Product::COLUMN_ID,
            ['in' => $productsIds]
        );

        return array_values($collection->getItems());
    }

    private function getTemplate(?int $templateId): ?\Ess\M2ePro\Model\Amazon\Template\Shipping
    {
        if (empty($templateId)) {
            return null;
        }

        if (!isset($this->cachedShippingTemplates[$templateId])) {
            $this->cachedShippingTemplates[$templateId] = $this->shippingTemplateRepository->find($templateId);
        }

        return $this->cachedShippingTemplates[$templateId];
    }

    private function createSnapshot(?\Ess\M2ePro\Model\Amazon\Template\Shipping $shippingTemplate): array
    {
        if (empty($shippingTemplate)) {
            return [];
        }

        $snapshotBuilder = $this->snapshotBuilderFactory->create();
        $snapshotBuilder->setModel($shippingTemplate);

        return $snapshotBuilder->getSnapshot();
    }
}
