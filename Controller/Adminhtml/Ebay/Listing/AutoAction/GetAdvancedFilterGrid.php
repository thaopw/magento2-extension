<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction;

class GetAdvancedFilterGrid extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction
{
    private \Ess\M2ePro\Model\Ebay\Listing\Repository $listingRepository;

    public function __construct(
        \Ess\M2ePro\Model\Ebay\Listing\Repository $listingRepository,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($ebayFactory, $context);
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $grid = $this
            ->getLayout()
            ->createBlock(
                \Ess\M2ePro\Block\Adminhtml\Ebay\Listing\AutoAction\Mode\AdvancedFilter\Grid::class,
                '',
                [
                    'listing' => $this->listingRepository->get((int)$this->getRequest()->getParam('listing_id')),
                ]
            );
        $this->setAjaxContent($grid);

        return $this->getResult();
    }
}
