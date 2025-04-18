<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Template\Category\Chooser\Specific\Form\Element;

use Ess\M2ePro\Model\Ebay\Template\Category\Specific as Specific;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

/**
 * Class Ess\M2ePro\Block\Adminhtml\Ebay\Template\Category\Chooser\Specific\Form\Element\Custom
 */
class Custom extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    public $helperFactory;

    public $layout;

    /** @var \Ess\M2ePro\Helper\Module\Translation */
    private $translationHelper;

    public function __construct(
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        \Ess\M2ePro\Helper\Module\Translation $translationHelper,
        $data = []
    ) {
        $this->helperFactory = $context->getHelperFactory();
        $this->layout = $context->getLayout();
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('specifics');
        $this->translationHelper = $translationHelper;
    }

    //########################################

    public function getElementHtml()
    {
        return '';
    }

    //########################################

    public function getSpecifics()
    {
        return array_merge(
            [
                [
                    '__template__' => true,
                    'attribute_title' => '',
                    'value_mode' => '',
                    'value_custom_value' => '',
                    'value_custom_attribute' => '',
                ],
            ],
            $this->getData('specifics')
        );
    }

    //########################################

    public function getModeHtml($index, $specific)
    {
        $element = $this->_factoryElement->create('hidden', [
            'data' => [
                'name' => 'specific[custom_' . $index . '][mode]',
                'class' => 'specific_mode',
                'value' => Specific::MODE_CUSTOM_ITEM_SPECIFICS,
                'disabled' => isset($specific['__template__']),
            ],
        ]);

        $element->setForm($this->getForm());
        $element->setId('specific_custom_mode_' . $index);

        return $element->getElementHtml();
    }

    public function getAttributeTitleLabelHtml($index, $specific)
    {
        $display = 'display: none;';
        if (
            isset($specific['value_mode']) &&
            ($specific['value_mode'] == Specific::VALUE_MODE_CUSTOM_ATTRIBUTE)
        ) {
            $display = '';
        }

        $labelText = $this->translationHelper->__('From Attribute label');

        return <<<HTML
<span id="specific_custom_attribute_title_label_{$index}" style="{$display}">
    <strong>{$labelText}</strong>
</span>
HTML;
    }

    public function getAttributeTitleInputHtml($index, $specific)
    {
        $display = 'display: none;';
        if (
            isset($specific['value_mode']) &&
            ($specific['value_mode'] == Specific::VALUE_MODE_CUSTOM_VALUE ||
                $specific['value_mode'] == Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE)
        ) {
            $display = '';
        }

        /** @var \Magento\Framework\Data\Form\Element\Text $element */
        $element = $this->_factoryElement->create('text', [
            'data' => [
                'name' => 'specific[custom_' . $index . '][attribute_title]',
                'class' => 'M2ePro-required-when-visible M2ePro-custom-specific-attribute-title custom-item-specific',
                'value' => isset($specific['attribute_title']) ? $specific['attribute_title'] : '',
                'disabled' => isset($specific['__template__']),
            ],
        ]);

        $element->addCustomAttribute('style', 'width: 100%;' . $display);
        $element->setNoSpan(true);
        $element->setForm($this->getForm());
        $element->setId('specific_custom_attribute_title_input_' . $index);

        return $element->getHtml();
    }

    public function getValueModeSelectHtml($index, $specific)
    {
        $values = [
            [
                'label' => '',
                'value' => '',
                'style' => 'display: none',
            ],
            [
                'value' => Specific::VALUE_MODE_CUSTOM_ATTRIBUTE,
                'label' => $this->translationHelper->__('Custom Attribute'),
            ],
            [
                'value' => Specific::VALUE_MODE_CUSTOM_VALUE,
                'label' => $this->translationHelper->__('Custom Value'),
            ],
            [
                'value' => Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE,
                'label' => $this->translationHelper->__('Custom Label / Attribute'),
            ],
        ];

        /** @var \Magento\Framework\Data\Form\Element\Select $element */
        $element = $this->_factoryElement->create('select', [
            'data' => [
                'name' => 'specific[custom_' . $index . '][value_mode]',
                'class' => 'M2ePro-required-when-visible specific-value-mode',
                'value' => isset($specific['value_mode']) ? $specific['value_mode'] : '',
                'values' => $values,
                'disabled' => isset($specific['__template__']),
            ],
        ]);

        $element->addCustomAttribute('onchange', 'EbayTemplateCategorySpecificsObj.customSpecificModeChange(this);');
        $element->addCustomAttribute('style', 'width: 100%');
        $element->setNoSpan(true);
        $element->setForm($this->getForm());
        $element->setId('specific_custom_value_mode_' . $index);

        return $element->getHtml();
    }

    public function getValueCustomAttributeHtml($index, $specific)
    {
        $attributes = $this->helperFactory->getObject('Magento_Attribute')->getAll();

        $attributesValues = [
            [
                'label' => '',
                'value' => '',
            ],
        ];
        foreach ($attributes as $attribute) {
            $attributesValues[] = [
                'label' => $attribute['label'],
                'value' => $attribute['code'],
            ];
        }

        $display = 'display: none;';
        if (
            isset($specific['value_mode']) &&
            ($specific['value_mode'] == Specific::VALUE_MODE_CUSTOM_ATTRIBUTE ||
                $specific['value_mode'] == Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE)
        ) {
            $display = '';
        }

        /** @var \Magento\Framework\Data\Form\Element\Select $element */
        $element = $this->_factoryElement->create('select', [
            'data' => [
                'name' => 'specific[custom_' . $index . '][value_custom_attribute]',
                'class' => 'M2ePro-required-when-visible M2ePro-custom-attribute-can-be-created',
                'value' => isset($specific['value_custom_attribute']) ? $specific['value_custom_attribute'] : '',
                'values' => $attributesValues,
                'disabled' => isset($specific['__template__']),
                'apply_to_all_attribute_sets' => 0,
            ],
        ]);

        $element->addCustomAttribute('style', 'width: 100%;' . $display);
        $element->setNoSpan(true);
        $element->setForm($this->getForm());
        $element->setId('specific_custom_value_custom_attribute_' . $index);

        return $element->getHtml();
    }

    public function getValueCustomValueHtml($index, $specific)
    {
        $customValue = '';
        if (!empty($specific['value_custom_value'])) {
            $customValue = \Ess\M2ePro\Helper\Json::decode($specific['value_custom_value'])[0] ?? '';
        }

        $display = 'display: none;';
        if (
            isset($specific['value_mode']) &&
            $specific['value_mode'] == Specific::VALUE_MODE_CUSTOM_VALUE
        ) {
            $display = '';
        }

        /** @var \Magento\Framework\Data\Form\Element\Text $element */
        $element = $this->_factoryElement->create('text', [
            'data' => [
                'name' => 'specific[custom_' . $index . '][value_custom_value][]',
                'class' => 'M2ePro-required-when-visible item-specific',
                'value' => $customValue,
                'disabled' => isset($specific['__template__']),
            ],
        ]);

        $element->addCustomAttribute('style', 'width: 100%;' . $display);
        $element->setNoSpan(true);
        $element->setForm($this->getForm());
        $element->setId('specific_custom_value_custom_value_' . $index);

        return $element->toHtml();
    }
}
