<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Template\Shipping;

class Repository
{
    private \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping\CollectionFactory $collectionFactory;
    private \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping $shippingResource;

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping\CollectionFactory $collectionFactory,
        \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping $shippingResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->shippingResource = $shippingResource;
    }

    public function find(int $id): ?\Ess\M2ePro\Model\Amazon\Template\Shipping
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping::COLUMN_ID,
            ['eq' => $id]
        );

        $template = $collection->getFirstItem();
        if ($template->isObjectNew()) {
            return null;
        }

        return $template;
    }

    public function get(int $id): \Ess\M2ePro\Model\Amazon\Template\Shipping
    {
        $template = $this->find($id);
        if ($template === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic('Shipping Template does not exist.');
        }

        return $template;
    }

    /**
     * @return \Ess\M2ePro\Model\Amazon\Template\Shipping[]
     */
    public function getByIds(array $idsToDelete): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping::COLUMN_ID,
            ['in' => $idsToDelete]
        );

        return array_values($collection->getItems());
    }

    /**
     * @return \Ess\M2ePro\Model\Amazon\Template\Shipping[]
     */
    public function getByAccountId(int $accountId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping::COLUMN_ACCOUNT_ID,
            ['eq' => $accountId]
        );

        return array_values($collection->getItems());
    }

    public function deleteAllByAccountId(int $accountId): void
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            \Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping::COLUMN_ACCOUNT_ID,
            ['eq' => $accountId]
        );

        foreach ($collection->getItems() as $item) {
            $this->delete($item);
        }
    }

    public function delete(\Ess\M2ePro\Model\Amazon\Template\Shipping $template): void
    {
        $this->shippingResource->delete($template);
    }

    public function create(\Ess\M2ePro\Model\Amazon\Template\Shipping $model)
    {
        $model->isObjectCreatingState(true);
        $this->shippingResource->save($model);
    }

    public function update(\Ess\M2ePro\Model\Amazon\Template\Shipping $model)
    {
        $this->shippingResource->save($model);
    }
}
