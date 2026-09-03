<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction;

class DeleteAdvancedFilter extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction
{
    private \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\Filter\Repository $ebayAdvancedFilterRepository;
    private \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\Filter\Delete $ebayAdvancedFilterDelete;

    public function __construct(
        \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\Filter\Repository $ebayAdvancedFilterRepository,
        \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\Filter\Delete $ebayAdvancedFilterDelete,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($ebayFactory, $context);
        $this->ebayAdvancedFilterRepository = $ebayAdvancedFilterRepository;
        $this->ebayAdvancedFilterDelete = $ebayAdvancedFilterDelete;
    }

    public function execute()
    {
        $advancedFilterId = (int)$this->getRequest()->getParam('advanced_filter_id');
        $advancedFilter = $this->ebayAdvancedFilterRepository->find($advancedFilterId);
        if ($advancedFilter !== null) {
            $this->ebayAdvancedFilterDelete->execute($advancedFilter);
        }
    }
}
