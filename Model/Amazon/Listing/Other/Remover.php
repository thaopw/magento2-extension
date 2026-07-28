<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Listing\Other;

class Remover
{
    private const API_REQUEST_CHUNK_SIZE = 5000;

    private \Ess\M2ePro\Model\Amazon\Listing\Other\Repository $unmanagedProductRepository;
    private \Ess\M2ePro\Model\Amazon\Connector\Product\Delete\Realtime\Processor $realtimeProductDeleteProcessor;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Listing\Other\Repository $unmanagedProductRepository,
        \Ess\M2ePro\Model\Amazon\Connector\Product\Delete\Realtime\Processor $realtimeProductDeleteProcessor
    ) {
        $this->unmanagedProductRepository = $unmanagedProductRepository;
        $this->realtimeProductDeleteProcessor = $realtimeProductDeleteProcessor;
    }

    /**
     * @param int[] $productIds
     *
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function execute(array $productIds): void
    {
        $unmanagedProducts = $this->unmanagedProductRepository->getByIds($productIds);

        $this->removeProductsFromAmazon($unmanagedProducts);
        $this->removeProductsFromUnmanaged($unmanagedProducts);
    }

    /**
     * @param \Ess\M2ePro\Model\Listing\Other[] $unmanagedProducts
     *
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    private function removeProductsFromAmazon(array $unmanagedProducts): void
    {
        $requestsByAccount = [];
        foreach ($unmanagedProducts as $unmanagedProduct) {
            /** @var \Ess\M2ePro\Model\Amazon\Listing\Other $amazonUnmanagedProduct */
            $amazonUnmanagedProduct = $unmanagedProduct->getChildObject();
            if (!isset($requestsByAccount[$unmanagedProduct->getAccountId()])) {
                $requestsByAccount[$unmanagedProduct->getAccountId()] = [
                    'account' => $amazonUnmanagedProduct->getAccount(),
                    'items' => []
                ];
            }

            $requestsByAccount[$unmanagedProduct->getAccountId()]['items'][$unmanagedProduct->getId()] = [
                'id' => $unmanagedProduct->getId(),
                'sku' => $amazonUnmanagedProduct->getSku(),
            ];
        }

        foreach ($requestsByAccount as $requestData) {
            $account = $requestData['account'];
            $itemChunks = array_chunk($requestData['items'], self::API_REQUEST_CHUNK_SIZE);
            foreach ($itemChunks as $itemChunk) {
                $this->realtimeProductDeleteProcessor->process($account, $itemChunk);
            }
        }
    }

    /**
     * @param \Ess\M2ePro\Model\Listing\Other[] $unmanagedProducts
     *
     * @return void
     */
    private function removeProductsFromUnmanaged(array $unmanagedProducts): void
    {
        foreach ($unmanagedProducts as $unmanagedProduct) {
            $this->unmanagedProductRepository->delete($unmanagedProduct);
        }
    }
}
