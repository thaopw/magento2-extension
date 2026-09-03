<?php

declare(strict_types=1);

namespace Ess\M2ePro\Block\Adminhtml\Listing\AutoAction\Mode\AdvancedFilter;

class AbstractForm extends \Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractForm
{
    private array $formData = [];

    private ?\Ess\M2ePro\Model\Listing\Auto\Advanced\Filter $advancedFilter;
    private \Ess\M2ePro\Model\Magento\Product\RuleFactory $ruleFactory;

    public function __construct(
        ?\Ess\M2ePro\Model\Listing\Auto\Advanced\Filter $advancedFilter,
        \Ess\M2ePro\Model\Magento\Product\RuleFactory $ruleFactory,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->advancedFilter = $advancedFilter;
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('listingAutoActionModeAdvancedFilterForm');
        $this->formData = $this->getFormData();
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

        $form->addField(
            'advanced_filter_id',
            'hidden',
            [
                'name' => 'advanced_filter_id',
                'value' => $this->getAdvancedFilter()
                    ? $this->getAdvancedFilter()->getId()
                    : null,
            ]
        );

        $fieldSet = $form->addFieldset(
            'fieldset_container',
            []
        );

        $fieldSet->addField(
            'title',
            'text',
            [
                'label' => __('Title'),
                'class' => 'M2ePro-validate-advanced-filter-title',
                'required' => true,
                'style' => 'width: 350px;',
                'value' => $this->getAdvancedFilter()
                    ? $this->getAdvancedFilter()->getTitle()
                    : '',
            ]
        );

        $fieldSet->addField(
            'adding_mode',
            $selectElementType,
            [
                'name' => 'adding_mode',
                'label' => __('Products meet the filter conditions'),
                'title' => __('Products meet the filter conditions'),
                'style' => 'width: 350px;',
                'values' => [
                    \Ess\M2ePro\Model\Listing::ADDING_MODE_NONE => __('No Action'),
                    \Ess\M2ePro\Model\Ebay\Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY => __(
                        'Add to the Listing and Assign eBay Category'
                    ),
                ],
                'value' => $this->getAdvancedFilter()
                    ? $this->getAdvancedFilter()->getAddingMode()
                    : \Ess\M2ePro\Model\Listing::ADDING_MODE_NONE,
            ]
        );

        $fieldSet->addField(
            'adding_add_not_visible',
            $selectElementType,
            [
                'name' => 'adding_add_not_visible',
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
                'value' => $this->getAdvancedFilter()
                    ? $this->getAdvancedFilter()->getAddingAddNotVisible()
                    : \Ess\M2ePro\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
                'field_extra_attributes' => 'id="adding_add_not_visible_field"',
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
            'deleting_mode',
            $selectElementType,
            [
                'name' => 'deleting_mode',
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
                'value' => $this->getAdvancedFilter()
                    ? $this->getAdvancedFilter()->getDeletingMode()
                    : \Ess\M2ePro\Model\Listing::DELETING_MODE_NONE,
                'style' => 'width: 350px;',
            ]
        );

        $ruleModel = $this->ruleFactory->create(\Ess\M2ePro\Model\Listing\Auto\Advanced\Filter::RULE_MODEL_PREFIX);
        if ($this->getAdvancedFilter() && !empty($this->getAdvancedFilter()->getCondition())) {
            $ruleModel->loadFromSerialized($this->getAdvancedFilter()->getCondition());
        }

        /** @var \Ess\M2ePro\Block\Adminhtml\Magento\Product\Rule $ruleBlock */
        $ruleBlock = $this
            ->getLayout()
            ->createBlock(\Ess\M2ePro\Block\Adminhtml\Magento\Product\Rule::class)
            ->setData(['rule_model' => $ruleModel]);

        $fieldSet->addField(
            'condition',
            self::CUSTOM_CONTAINER,
            [
                'container_class' => 'M2ePro-validate-advanced-filter-condition',
                'label' => __('Conditions'),
                'text' => $ruleBlock->toHtml(),
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getAdvancedFilter(): ?\Ess\M2ePro\Model\Listing\Auto\Advanced\Filter
    {
        return $this->advancedFilter;
    }

    private function getFormData(): array
    {
        $formData = [];
        if ($this->getAdvancedFilter() !== null) {
            $formData = $this->getAdvancedFilter()->getData();
        }

        $default = $this->getDefault();

        return array_merge($default, $formData);
    }

    private function getDefault(): array
    {
        return [
            'title' => '',
            'adding_mode' => \Ess\M2ePro\Model\Listing::ADDING_MODE_ADD,
            'adding_add_not_visible' => \Ess\M2ePro\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'deleting_mode' => \Ess\M2ePro\Model\Listing::DELETING_MODE_NONE,
        ];
    }

    protected function _toHtml()
    {
        return '<div id="advanced_filter_child_data_container">' . parent::_toHtml() . '</div>';
    }
}
