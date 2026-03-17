<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping;

class Repository
{
    private \Ess\M2ePro\Model\ResourceModel\Amazon\Dictionary\TemplateShipping\CollectionFactory $collectionFactory;
    private \Ess\M2ePro\Model\ResourceModel\Amazon\Dictionary\TemplateShipping $resource;

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Amazon\Dictionary\TemplateShipping\CollectionFactory $collectionFactory,
        \Ess\M2ePro\Model\ResourceModel\Amazon\Dictionary\TemplateShipping $resource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
    }

    /**
     * @return \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping[]
     */
    public function getByAccountId(int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Dictionary\TemplateShipping::COLUMN_ACCOUNT_ID,
            ['eq' => $accountId]
        );

        return array_values($collection->getItems());
    }

    public function deleteAllByAccountId(int $accountId): void
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Dictionary\TemplateShipping::COLUMN_ACCOUNT_ID,
            ['eq' => $accountId]
        );

        foreach ($collection->getItems() as $item) {
            $this->delete($item);
        }
    }

    public function create(\Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping $templateShipping): void
    {
        $this->resource->save($templateShipping);
    }

    public function delete(\Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping $templateShipping): void
    {
        $this->resource->delete($templateShipping);
    }
}
