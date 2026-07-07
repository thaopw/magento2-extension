<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Observer\Import;

class Bunch extends \Ess\M2ePro\Observer\AbstractModel
{
    private \Ess\M2ePro\PublicServices\Product\SqlChange $publicService;
    private \Magento\Catalog\Model\Product $magentoProduct;
    private \Ess\M2ePro\Model\Listing\Auto\Actions\Mode\Factory $listingAutoActionsModeFactory;

    public function __construct(
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\PublicServices\Product\SqlChange $publicService,
        \Magento\Catalog\Model\Product $magentoProduct,
        \Ess\M2ePro\Model\Listing\Auto\Actions\Mode\Factory $listingAutoActionsModeFactory
    ) {
        parent::__construct($helperFactory, $activeRecordFactory, $modelFactory);
        $this->publicService = $publicService;
        $this->magentoProduct = $magentoProduct;
        $this->listingAutoActionsModeFactory = $listingAutoActionsModeFactory;
    }

    public function process()
    {
        $rowData = $this->getEvent()->getBunch();

        $productIds = [];

        foreach ($rowData as $item) {
            if (!isset($item['sku'])) {
                continue;
            }

            $id = $this->magentoProduct->getIdBySku($item['sku']);
            if ((int)$id > 0) {
                $productIds[] = $id;
            }
        }

        foreach ($productIds as $id) {
            $this->publicService->markProductChanged($id);
            $this->listingAutoActionsModeFactory
                ->createAdvancedFilterMode()
                ->synchByProductId((int)$id);
        }

        $this->publicService->applyChanges();
    }

    //########################################
}
