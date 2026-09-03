<?php

declare(strict_types=1);

namespace Ess\M2ePro\Block\Adminhtml\Listing\AutoAction\Mode\AdvancedFilter;

class AbstractGrid extends \Ess\M2ePro\Block\Adminhtml\Magento\Grid\AbstractGrid
{
    private bool $isGridPrepared = false;

    private \Ess\M2ePro\Model\Listing $listing;

    public function __construct(
        \Ess\M2ePro\Model\Listing $listing,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->listing = $listing;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('listingAutoActionModeAdvancedFilterGrid');

        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareGrid()
    {
        if (!$this->isGridPrepared) {
            parent::_prepareGrid();
            $this->isGridPrepared = true;
        }

        return $this;
    }

    public function prepareGrid()
    {
        return $this->_prepareGrid();
    }

    public function getRowUrl($item)
    {
        return false;
    }

    public function getGridUrl()
    {
        return false;
    }

    protected function getListing(): \Ess\M2ePro\Model\Listing
    {
        return $this->listing;
    }
}
