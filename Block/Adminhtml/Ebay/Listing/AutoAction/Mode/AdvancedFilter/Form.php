<?php

declare(strict_types=1);

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Listing\AutoAction\Mode\AdvancedFilter;

class Form extends \Ess\M2ePro\Block\Adminhtml\Listing\AutoAction\Mode\AdvancedFilter\AbstractForm
{
    private \Ess\M2ePro\Helper\Data $dataHelper;

    public function __construct(
        \Ess\M2ePro\Helper\Data $dataHelper,
        ?\Ess\M2ePro\Model\Listing\Auto\Advanced\Filter $advancedFilter,
        \Ess\M2ePro\Model\Magento\Product\RuleFactory $ruleFactory,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct(
            $advancedFilter,
            $ruleFactory,
            $context,
            $registry,
            $formFactory,
            $data
        );
        $this->dataHelper = $dataHelper;
    }

    protected function _afterToHtml($html)
    {
        $this->jsPhp->addConstants(
            $this->dataHelper->getClassConstants(\Ess\M2ePro\Model\Ebay\Listing::class)
        );

        $this->js->add(
            <<<JS
            $('adding_mode')
                .observe('change', ListingAutoActionObj.advancedFilterAddingMode)
                .simulate('change');
JS
        );

        return parent::_afterToHtml($html);
    }

    protected function _toHtml()
    {
        return parent::_toHtml() . '<div id="ebay_category_chooser"></div>';
    }
}
