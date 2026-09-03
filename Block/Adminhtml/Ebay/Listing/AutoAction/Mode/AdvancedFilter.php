<?php

declare(strict_types=1);

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Listing\AutoAction\Mode;

class AdvancedFilter extends \Ess\M2ePro\Block\Adminhtml\Listing\AutoAction\Mode\AbstractAdvancedFilterMode
{
    public function _construct()
    {
        parent::_construct();
        $this->setId('ebayListingAutoActionModeAdvancedFilter');
    }

    protected function prepareGrid(): void
    {
        $grid = $this
            ->getLayout()
            ->createBlock(
                \Ess\M2ePro\Block\Adminhtml\Ebay\Listing\AutoAction\Mode\AdvancedFilter\Grid::class,
                '',
                ['listing' => $this->getListing()]
            );
        $grid->prepareGrid();
        $this->setChild(self::GRID_BLOCK_ALIAS, $grid);
    }

    protected function _afterToHtml($html)
    {
        $this->jsPhp->addConstants(
            $this->dataHelper->getClassConstants(\Ess\M2ePro\Model\Ebay\Listing::class)
        );

        return parent::_afterToHtml($html);
    }

    protected function _toHtml()
    {
        $this->css->add(
            <<<CSS
#ebay_auto_action_advanced_filter ul.rule-param-children {
    margin-top: 1em;
}

#ebay_auto_action_advanced_filter .rule-param .label {
    font-size: 14px;
    font-weight: 600;
}
CSS
        );

        $helpBlockContent = __(
            '<p>These Rules of automatic product adding and removal act based on ' .
            'defined filter conditions. When a Magento Product meets the configured conditions, it will be ' .
            'automatically added to the current M2E Pro Listing if the settings are enabled.</p><br>' .
            '<p>Please note that if a product is already presented in another M2E Pro Listing with the ' .
            'related Channel account and marketplace, the Item won\'t be added to the Listing to prevent ' .
            'listing duplicates on the Channel.</p><br>' .
            '<p>Accordingly, if a Magento Product currently present in the M2E Pro Listing no longer meets ' .
            'the configured conditions, the Item will be removed from the Listing and its sale will ' .
            'be stopped on Channel.'
        );

        $helpBlock = $this->getLayout()->createBlock(\Ess\M2ePro\Block\Adminhtml\HelpBlock::class)->setData(
            [
                'content' => $helpBlockContent,
            ]
        );

        return $helpBlock->toHtml() . parent::_toHtml();
    }
}
