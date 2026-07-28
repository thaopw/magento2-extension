<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Cron\Task\Amazon\Listing\Product\AutoSearchAsin;

class Processor
{
    private const DEFAULT_SEARCH_PRODUCT_LIMIT = 50;
    private const MAX_SEARCH_ASIN_ATTEMPT = 5;

    private const SEARCH_PRODUCT_LIMIT_REGISTRY_KEY = '/cron/task/listing/product/auto_search_asin/product_limit/';

    private \Ess\M2ePro\Model\Amazon\Listing\Product\Repository $amazonListingProductRepository;
    private \Ess\M2ePro\Model\Amazon\Search\SettingsFactory $settingsSearchFactory;
    private \Ess\M2ePro\Model\Registry\Manager $registryManager;
    private \Ess\M2ePro\Helper\Module\Exception $moduleException;
    private \Ess\M2ePro\Model\ResourceModel\Listing\Product\Instruction $instructionResource;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Listing\Product\Repository $amazonListingProductRepository,
        \Ess\M2ePro\Model\Amazon\Search\SettingsFactory $settingsSearchFactory,
        \Ess\M2ePro\Model\Registry\Manager $registryManager,
        \Ess\M2ePro\Helper\Module\Exception $moduleException,
        \Ess\M2ePro\Model\ResourceModel\Listing\Product\Instruction $instructionResource
    ) {
        $this->amazonListingProductRepository = $amazonListingProductRepository;
        $this->settingsSearchFactory = $settingsSearchFactory;
        $this->registryManager = $registryManager;
        $this->moduleException = $moduleException;
        $this->instructionResource = $instructionResource;
    }

    public function process()
    {
        $limit = $this->getSearchLimit();
        $listingProductsToSearch = $this->amazonListingProductRepository
            ->getProductsForSearchAsin($limit, self::MAX_SEARCH_ASIN_ATTEMPT);
        if (empty($listingProductsToSearch)) {
            return;
        }

        $instructions = [];
        foreach ($listingProductsToSearch as $listingProduct) {
            $this->incrementAutoSearchAsinAttemptData($listingProduct);
            $asinFounded = $this->trySearchProductAsin($listingProduct);
            if ($asinFounded) {
                $instructions[] = [
                    'listing_product_id' => (int)$listingProduct->getId(),
                    'component' => \Ess\M2ePro\Helper\Component\Amazon::NICK,
                    'type' => \Ess\M2ePro\Model\Listing::INSTRUCTION_TYPE_PRODUCT_ADDED,
                    'initiator' => \Ess\M2ePro\Model\Listing::INSTRUCTION_INITIATOR_ADDING_PRODUCT,
                    'priority' => 70,
                ];
            }
        }

        $this->instructionResource->add($instructions);
    }

    private function incrementAutoSearchAsinAttemptData(\Ess\M2ePro\Model\Listing\Product $product): void
    {
        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product $amazonListingProduct */
        $amazonListingProduct = $product->getChildObject();
        $currentAttempt = $amazonListingProduct->getAutoSearchAsinAttempt();

        $amazonListingProduct->setAutoSearchAsinAttempt($currentAttempt + 1);
        $amazonListingProduct->setAutoSearchAsinLastAttemptDate(\Ess\M2ePro\Helper\Date::createCurrentGmt());

        $this->amazonListingProductRepository->save($product);
    }

    private function trySearchProductAsin(\Ess\M2ePro\Model\Listing\Product $listingProduct): bool
    {
        try {
            $settingsSearch = $this->settingsSearchFactory->create();
            $settingsSearch->setListingProduct($listingProduct);
            $settingsSearch->resetStep();
            if (!$settingsSearch->checkIdentifierValidity()) {
                return false;
            }
            $searchResult = $settingsSearch->process();
        } catch (\Throwable $exception) {
            $this->moduleException->process($exception);

            return false;
        }

        if (\count($searchResult) === 0) {
            return false;
        }

        $reloadedProduct = $this->amazonListingProductRepository->get((int)$listingProduct->getId());
        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product $reloadedAmazonListingProduct */
        $reloadedAmazonListingProduct = $reloadedProduct->getChildObject();

        return !empty($reloadedAmazonListingProduct->getGeneralId());
    }

    private function getSearchLimit(): int
    {
        $limit = $this->registryManager->getValue(self::SEARCH_PRODUCT_LIMIT_REGISTRY_KEY);
        if (!empty($limit)) {
            return (int)$limit;
        }

        return self::DEFAULT_SEARCH_PRODUCT_LIMIT;
    }
}
