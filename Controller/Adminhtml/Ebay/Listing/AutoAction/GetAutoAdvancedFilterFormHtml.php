<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction;

class GetAutoAdvancedFilterFormHtml extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction
{
    private \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\Filter\Repository $autoAdvancedFilterRepository;

    public function __construct(
        \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\Filter\Repository $autoAdvancedFilterRepository,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($ebayFactory, $context);
        $this->autoAdvancedFilterRepository = $autoAdvancedFilterRepository;
    }

    public function execute()
    {
        $block = $this
            ->getLayout()
            ->createBlock(
                \Ess\M2ePro\Block\Adminhtml\Ebay\Listing\AutoAction\Mode\AdvancedFilter\Form::class,
                '',
                [
                    'advancedFilter' => $this->getAdvancedFilterFromRequest(),
                ]
            );

        $this->setAjaxContent($block);

        return $this->getResult();
    }

    private function getAdvancedFilterFromRequest(): ?\Ess\M2ePro\Model\Listing\Auto\Advanced\Filter
    {
        $advancedFilterId = (int)$this->getRequest()->getParam('advanced_filter_id');

        return $this->autoAdvancedFilterRepository->find($advancedFilterId);
    }
}
