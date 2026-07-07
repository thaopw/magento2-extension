<?php

declare(strict_types=1);

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Listing\AutoAction\Mode;

class AdvancedFilter extends \Ess\M2ePro\Block\Adminhtml\Listing\AutoAction\Mode\AbstractAdvancedFilterMode
{
    private \Ess\M2ePro\Model\Magento\Product\RuleFactory $ruleFactory;

    public function __construct(
        \Ess\M2ePro\Model\Magento\Product\RuleFactory $ruleFactory,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Ess\M2ePro\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $dataHelper, $data);
        $this->ruleFactory = $ruleFactory;
    }

    public function _construct()
    {
        parent::_construct();
        $this->setId('ebayListingAutoActionModeAdvancedFilter');
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $selectElementType = \Ess\M2ePro\Block\Adminhtml\Magento\Form\Element\Select::class;

        $form->addField(
            'auto_mode',
            'hidden',
            [
                'name' => 'auto_mode',
                'value' => \Ess\M2ePro\Model\Listing::AUTO_MODE_ADVANCED_FILTER,
            ]
        );

        $fieldSet = $form->addFieldset(
            'auto_advanced_filter_fieldset_container',
            []
        );

        $fieldSet->addField(
            'auto_advanced_filter_adding_mode',
            $selectElementType,
            [
                'name' => 'auto_advanced_filter_adding_mode',
                'label' => __('Products meet the filter conditions'),
                'title' => __('Products meet the filter conditions'),
                'values' => [
                    \Ess\M2ePro\Model\Listing::ADDING_MODE_NONE => __('No Action'),
                    \Ess\M2ePro\Model\Ebay\Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY => __(
                        'Add to the Listing and Assign eBay Category'
                    ),
                ],
                'value' => $this->formData['auto_advanced_filter_adding_mode'],
                'style' => 'width: 350px;',
            ]
        );

        $fieldSet->addField(
            'auto_advanced_filter_adding_add_not_visible',
            $selectElementType,
            [
                'name' => 'auto_advanced_filter_adding_add_not_visible',
                'label' => __('Add not Visible Individually Products'),
                'title' => __('Add not Visible Individually Products'),
                'values' => [
                    [
                        'label' => __('No'),
                        'value' => \Ess\M2ePro\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_NO,
                    ],
                    [
                        'label' => __('Yes'),
                        'value' => \Ess\M2ePro\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
                    ],
                ],
                'value' => $this->formData['auto_advanced_filter_adding_add_not_visible'],
                'field_extra_attributes' => 'id="auto_advanced_filter_adding_add_not_visible_field"',
                'tooltip' => __(
                    'Set to <strong>Yes</strong> if you want the Magento Products with
                    Visibility \'Not visible Individually\' to be added to the Listing
                    Automatically.<br/>
                    If set to <strong>No</strong>, only Variation (i.e.
                    Parent) Magento Products will be added to the Listing Automatically,
                    excluding Child Products.'
                ),
            ]
        );

        $fieldSet->addField(
            'auto_advanced_filter_deleting_mode',
            $selectElementType,
            [
                'name' => 'auto_advanced_filter_deleting_mode',
                'label' => __('Products no longer meet the filter conditions'),
                'title' => __('Products no longer meet the filter conditions'),
                'values' => [
                    [
                        'label' => __('No Action'),
                        'value' => \Ess\M2ePro\Model\Listing::DELETING_MODE_NONE,
                    ],
                    [
                        'label' => __('Stop on Channel'),
                        'value' => \Ess\M2ePro\Model\Listing::DELETING_MODE_STOP,
                    ],
                    [
                        'label' => __('Stop on Channel and Delete from Listing'),
                        'value' => \Ess\M2ePro\Model\Listing::DELETING_MODE_STOP_REMOVE,
                    ],
                ],
                'value' => $this->formData['auto_advanced_filter_deleting_mode'],
                'style' => 'width: 350px;',
            ]
        );

        $ruleModel = $this->ruleFactory->create('ebay_auto_action_advanced_filter');
        if (!empty($this->formData['auto_advanced_filter_condition'])) {
            $ruleModel->loadFromSerialized($this->formData['auto_advanced_filter_condition']);
        }

        /** @var \Ess\M2ePro\Block\Adminhtml\Magento\Product\Rule $ruleBlock */
        $ruleBlock = $this
            ->getLayout()
            ->createBlock(\Ess\M2ePro\Block\Adminhtml\Magento\Product\Rule::class)
            ->setData(['rule_model' => $ruleModel]);

        $fieldSet->addField(
            'auto_advanced_filter_condition',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Conditions'),
                'text' => $ruleBlock->toHtml(),
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
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

        $helpBlockContent = __('<p>These Rules of automatic product adding and removal act based on ' .
            'defined filter conditions. When a Magento Product meets the configured conditions, it will be ' .
            'automatically added to the current M2E Pro Listing if the settings are enabled.</p><br>' .
            '<p>Please note that if a product is already presented in another M2E Pro Listing with the ' .
            'related Channel account and marketplace, the Item won\'t be added to the Listing to prevent ' .
            'listing duplicates on the Channel.</p><br>' .
            '<p>Accordingly, if a Magento Product currently present in the M2E Pro Listing no longer meets ' .
            'the configured conditions, the Item will be removed from the Listing and its sale will ' .
            'be stopped on Channel.');

        $helpBlock = $this->getLayout()->createBlock(\Ess\M2ePro\Block\Adminhtml\HelpBlock::class)->setData(
            [
                'content' => $helpBlockContent,
            ]
        );

        return $helpBlock->toHtml() .
            parent::_toHtml() .
            '<div id="ebay_category_chooser"></div>';
    }
}
