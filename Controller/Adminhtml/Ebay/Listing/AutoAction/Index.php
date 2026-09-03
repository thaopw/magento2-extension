<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction;

class Index extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction
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
        $listing = $this->listingRepository->get((int)$this->getRequest()->getParam('listing_id'));
        $this->getHelper('Data\GlobalData')->setValue('listing', $listing);

        $autoMode = $this->getRequest()->getParam('auto_mode');
        if (empty($autoMode)) {
            $autoMode = $listing->getAutoMode();
        }

        switch ($autoMode) {
            case \Ess\M2ePro\Model\Listing::AUTO_MODE_GLOBAL:
                $blockName = \Ess\M2ePro\Block\Adminhtml\Ebay\Listing\AutoAction\Mode\GlobalMode::class;
                break;
            case \Ess\M2ePro\Model\Listing::AUTO_MODE_WEBSITE:
                $blockName = \Ess\M2ePro\Block\Adminhtml\Ebay\Listing\AutoAction\Mode\Website::class;
                break;
            case \Ess\M2ePro\Model\Listing::AUTO_MODE_CATEGORY:
                $blockName = \Ess\M2ePro\Block\Adminhtml\Ebay\Listing\AutoAction\Mode\Category::class;
                break;
            case \Ess\M2ePro\Model\Listing::AUTO_MODE_ADVANCED_FILTER:
                $blockName = \Ess\M2ePro\Block\Adminhtml\Ebay\Listing\AutoAction\Mode\AdvancedFilter::class;
                break;
            default:
                $blockName = \Ess\M2ePro\Block\Adminhtml\Ebay\Listing\AutoAction\Mode::class;
                break;
        }

        $this->setJsonContent([
            'mode' => $autoMode,
            'html' => $this->getLayout()->createBlock($blockName)->toHtml(),
        ]);

        return $this->getResult();
    }
}
