<?php

declare(strict_types=1);

namespace Ess\M2ePro\Block\Adminhtml\Listing\AutoAction\Mode;

abstract class AbstractAdvancedFilterMode extends \Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractForm
{
    protected const GRID_BLOCK_ALIAS = 'advanced_filter_grid';

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
    }

    protected function _prepareForm()
    {
        $this->prepareGrid();

        $form = $this->_formFactory->create();

        $containerHtml = $this->getChildHtml(self::GRID_BLOCK_ALIAS);

        $form->addField(
            'custom_listing_auto_action_mode_advanced_filter',
            \Ess\M2ePro\Block\Adminhtml\Magento\Form\Element\CustomContainer::class,
            [
                'text' => $containerHtml,
                'field_extra_attributes' => 'style="width: 100%"',
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    abstract protected function prepareGrid(): void;

    public function getListing(): \Ess\M2ePro\Model\Listing
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
