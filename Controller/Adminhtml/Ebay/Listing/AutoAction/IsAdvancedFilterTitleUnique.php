<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction;

class IsAdvancedFilterTitleUnique extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction
{
    private \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\Filter\Repository $ebayAdvancedFilterRepository;

    public function __construct(
        \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\Filter\Repository $ebayAdvancedFilterRepository,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($ebayFactory, $context);
        $this->ebayAdvancedFilterRepository = $ebayAdvancedFilterRepository;
    }

    public function execute()
    {
        $advancedFilterId = $this->getRequest()->getParam('advanced_filter_id');
        if (empty($advancedFilterId)) {
            $advancedFilterId = null;
        }

        $title = $this->getRequest()->getParam('title');
        if ($title == '') {
            $this->setJsonContent(['unique' => false]);

            return $this->getResult();
        }

        $result = $this->ebayAdvancedFilterRepository->isUniqueTitle(
            $title,
            (int)$this->getRequest()->getParam('listing_id'),
            $this->getAdvancedFilterIdFromRequest()
        );

        $this->setJsonContent(['unique' => $result]);

        return $this->getResult();
    }

    private function getAdvancedFilterIdFromRequest(): ?int
    {
        $value = $this->getRequest()->getParam('advanced_filter_id');
        if (empty($value)) {
            return null;
        }

        return (int)$value;
    }
}
