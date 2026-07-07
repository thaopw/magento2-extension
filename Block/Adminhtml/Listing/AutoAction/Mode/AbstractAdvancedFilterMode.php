<?php

declare(strict_types=1);

namespace Ess\M2ePro\Block\Adminhtml\Listing\AutoAction\Mode;

abstract class AbstractAdvancedFilterMode extends \Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractForm
{
    /** @var mixed */
    protected $listing;

    public array $formData = [];
    protected \Ess\M2ePro\Helper\Data $dataHelper;

    public function __construct(
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Ess\M2ePro\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('listingAutoActionModeAdvancedFilter');
        $this->formData = $this->getFormData();
    }

    public function hasFormData()
    {
        return $this->getListing()->getData('auto_mode') == \Ess\M2ePro\Model\Listing::AUTO_MODE_ADVANCED_FILTER;
    }

    public function getFormData()
    {
        $formData = $this->getListing()->getData();
        $formData = array_merge($formData, $this->getListing()->getChildObject()->getData());
        $default = $this->getDefault();

        return array_merge($default, $formData);
    }

    public function getDefault()
    {
        return [
            'auto_advanced_filter_adding_mode' => \Ess\M2ePro\Model\Listing::ADDING_MODE_ADD,
            'auto_advanced_filter_adding_add_not_visible' => \Ess\M2ePro\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_advanced_filter_deleting_mode' => \Ess\M2ePro\Model\Listing::DELETING_MODE_STOP_REMOVE,
        ];
    }

    /**
     * @return \Ess\M2ePro\Model\Listing
     * @throws \Exception
     */
    public function getListing()
    {
        if ($this->listing === null) {
            $this->listing = $this->activeRecordFactory->getCachedObjectLoaded(
                'Listing',
                $this->getRequest()->getParam('listing_id')
            );
        }

        return $this->listing;
    }

    protected function _afterToHtml($html)
    {
        $this->jsPhp->addConstants(
            $this->dataHelper->getClassConstants(\Ess\M2ePro\Model\Listing::class)
        );

        $hasFormData = $this->hasFormData() ? 'true' : 'false';

        $this->js->add(
            <<<JS
        $('auto_advanced_filter_adding_mode')
            .observe('change', ListingAutoActionObj.addingModeChange)
            .simulate('change');

        if ({$hasFormData}) {
            $('advanced_filter_reset_button').show();
        }
JS
        );

        return parent::_afterToHtml($html);
    }

    protected function _toHtml()
    {
        return '<div id="additional_autoaction_title_text" style="display: none">' . $this->getBlockTitle() . '</div>'
            . '<div id="block-content-wrapper"><div id="data_container">' . parent::_toHtml() . '</div></div>';
    }

    // ---------------------------------------

    protected function getBlockTitle(): string
    {
        return (string)__('Advanced filter');
    }
}
