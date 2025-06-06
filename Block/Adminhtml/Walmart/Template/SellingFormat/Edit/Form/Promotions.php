<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Walmart\Template\SellingFormat\Edit\Form;

use Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractForm as Form;
use Ess\M2ePro\Model\Walmart\Template\SellingFormat\Promotion;

/**
 * Class \Ess\M2ePro\Block\Adminhtml\Walmart\Template\SellingFormat\Edit\Form\Promotions
 */
class Promotions extends \Ess\M2ePro\Block\Adminhtml\Magento\AbstractBlock
{
    private $elementFactory;
    /** @var \Ess\M2ePro\Helper\Module\Support */
    private $supportHelper;
    protected $_template = 'walmart/template/sellingFormat/form/promotions.phtml';

    private $parentForm;
    private $attributesByInputTypes = [];
    private $renderer;

    //########################################

    public function __construct(
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Ess\M2ePro\Helper\Module\Support $supportHelper,
        array $data = []
    ) {
        $this->elementFactory = $context->getElementFactory();
        $this->supportHelper = $supportHelper;

        parent::__construct($context, $data);
    }

    //########################################

    public function setParentForm($form)
    {
        $this->parentForm = $form;

        return $this;
    }

    public function setAttributesByInputType($type, array $attributes)
    {
        $this->attributesByInputTypes[$type] = $attributes;

        return $this;
    }

    //########################################

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartTemplateDescriptionEditFormPromotions');
        // ---------------------------------------
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------

        $addPromotionPriceButton = $this->getLayout()->createBlock(\Ess\M2ePro\Block\Adminhtml\Magento\Button::class)
                                        ->addData([
                                            'onclick' => 'WalmartTemplateSellingFormatObj.addPromotionsPriceRow();',
                                            'class' => 'add add_promotion_price_button primary',
                                        ]);
        $this->setData('add_promotion_price_button', $addPromotionPriceButton);

        // ---------------------------------------

        $promotionsFromDateCustomAttribute = $this->createElement(
            'hidden',
            [
                'html_id' => 'promotions_from_date_custom_attribute_temp-i',
                'name' => 'promotions[temp-i][from_date][attribute]',
                'disabled' => true,
            ]
        );
        $this->setData('promotions_from_date_custom_attribute', $promotionsFromDateCustomAttribute);

        $promotionsFromDateMode = $this->createElement(
            Form::SELECT,
            [
                'html_id' => 'promotions_from_date_mode_temp-i',
                'name' => 'promotions[temp-i][from_date][mode]',
                'values' => $this->getPromotionsFromDateModeOptions(),
                'value' => '',
                'class' => 'promotions_from_date_mode',
                'disabled' => true,
            ]
        );
        $promotionsFromDateMode->addCustomAttribute('allowed_attribute_types', 'text,date');
        $this->setData('promotions_from_date_mode', $promotionsFromDateMode);

        // ---------------------------------------

        $promotionsFromDateValue = $this->createElement(
            'text',
            [
                'html_id' => 'promotions_from_date_value_temp-i',
                'name' => 'promotions[temp-i][from_date][value]',
                'value' => '',
                'class' => 'M2ePro-required-when-visible M2ePro-input-datetime M2ePro-correct-date-range',
                'disabled' => true,
            ]
        );
        $this->setData('promotions_from_date_value', $promotionsFromDateValue);

        // ---------------------------------------

        $promotionsToDateCustomAttribute = $this->createElement(
            'hidden',
            [
                'html_id' => 'promotions_to_date_custom_attribute_temp-i',
                'name' => 'promotions[temp-i][to_date][attribute]',
                'disabled' => true,
            ]
        );

        $this->setData('promotions_to_date_custom_attribute', $promotionsToDateCustomAttribute);

        $promotionsToDateMode = $this->createElement(
            Form::SELECT,
            [
                'html_id' => 'promotions_to_date_mode_temp-i',
                'name' => 'promotions[temp-i][to_date][mode]',
                'values' => $this->getPromotionsToDateModeOptions(),
                'value' => '',
                'class' => 'promotions_to_date_mode',
                'disabled' => true,
            ]
        );
        $promotionsToDateMode->addCustomAttribute('allowed_attribute_types', 'text,date');
        $this->setData('promotions_to_date_mode', $promotionsToDateMode);

        $docUrl = $this->supportHelper->getDocumentationArticleUrl('creating-promotions');
        $toDateTooltip = <<<HTML
        <span>The end date of the promotion is within 365 days from the current date and time.
        Promotions beyond 365 days will be returned an error.</span>
<a href="{$docUrl}">{$this->__('How to create promotion?')}</a>
HTML;
        $promotionsToDateValue = $this->createElement(
            'text',
            [
                'html_id' => 'promotions_to_date_value_temp-i',
                'name' => 'promotions[temp-i][to_date][value]',
                'value' => '',
                'class' => 'M2ePro-required-when-visible M2ePro-input-datetime',
                'disabled' => true,
                'tooltip' => $toDateTooltip,
            ]
        );
        $this->setData('promotions_to_date_value', $promotionsToDateValue);

        // ---------------------------------------

        $promotionsPriceCustomAttribute = $this->createElement(
            'hidden',
            [
                'html_id' => 'promotions_price_custom_attribute_temp-i',
                'name' => 'promotions[temp-i][price][attribute]',
                'disabled' => true,
            ]
        );
        $this->setData('promotions_price_custom_attribute', $promotionsPriceCustomAttribute);

        $promotionsPriceMode = $this->createElement(
            Form::SELECT,
            [
                'html_id' => 'promotions_price_mode_temp-i',
                'name' => 'promotions[temp-i][price][mode]',
                'required' => true,
                'values' => $this->getPromotionsPriceModeOptions(),
                'value' => '',
                'class' => 'promotions_price_mode',
                'disabled' => true,
            ]
        );
        $promotionsPriceMode->addCustomAttribute('allowed_attribute_types', 'text,price');
        $this->setData('promotions_price_mode', $promotionsPriceMode);

        // ---------------------------------------

        $promotionsPriceCoefficient = $this->createElement(
            'text',
            [
                'html_id' => 'promotions_price_coefficient_temp-i',
                'name' => 'promotions[temp-i][price][coefficient]',
                'value' => '',
                'class' => 'M2ePro-validate-price-coefficient',
                'disabled' => true,
                'tooltip' => __('Absolute figure (+8,-3), percentage (+15%, -20%) or Currency rate (1.44)'),
            ]
        );
        $this->setData('promotions_price_coefficient', $promotionsPriceCoefficient);

        // ---------------------------------------

        $promotionsComparisonPriceCustomAttribute = $this->createElement(
            'hidden',
            [
                'html_id' => 'promotions_comparison_price_custom_attribute_temp-i',
                'name' => 'promotions[temp-i][comparison_price][attribute]',
                'disabled' => true,
            ]
        );
        $this->setData('promotions_comparison_price_custom_attribute', $promotionsComparisonPriceCustomAttribute);

        $promotionsComparisonPriceMode = $this->createElement(
            Form::SELECT,
            [
                'html_id' => 'promotions_comparison_price_mode_temp-i',
                'name' => 'promotions[temp-i][comparison_price][mode]',
                'required' => true,
                'values' => $this->getPromotionsComparisonPriceModeOptions(),
                'value' => '',
                'class' => 'promotions_comparison_price_mode',
                'disabled' => true,
            ]
        );
        $promotionsComparisonPriceMode->addCustomAttribute('allowed_attribute_types', 'text,price');
        $this->setData('promotions_comparison_price_mode', $promotionsComparisonPriceMode);

        // ---------------------------------------

        $promotionsPriceCoefficient = $this->createElement(
            'text',
            [
                'html_id' => 'promotions_comparison_price_coefficient_temp-i',
                'name' => 'promotions[temp-i][comparison_price][coefficient]',
                'value' => '',
                'class' => 'M2ePro-validate-price-coefficient',
                'disabled' => true,
                'tooltip' => __('Absolute figure (+8,-3), percentage (+15%, -20%) or Currency rate (1.44)'),
            ]
        );
        $this->setData('promotions_comparison_price_coefficient', $promotionsPriceCoefficient);

        // ---------------------------------------

        $promotionsType = $this->createElement(
            Form::SELECT,
            [
                'html_id' => 'promotions_type_temp-i',
                'name' => 'promotions[temp-i][type]',
                'values' => [
                    [
                        'value' => Promotion::TYPE_REDUCED,
                        'label' => __('Reduced'),
                    ],
                    [
                        'value' => Promotion::TYPE_CLEARANCE,
                        'label' => __('Clearance'),
                    ],
                ],
                'value' => '',
                'disabled' => true,
            ]
        );
        $this->setData('promotions_type', $promotionsType);

        // ---------------------------------------

        $removePromotionPriceButton = $this->getLayout()->createBlock(\Ess\M2ePro\Block\Adminhtml\Magento\Button::class)
                                           ->addData([
                                               'label' => __('Remove'),
                                               'class' => 'delete icon-btn remove_promotion_price_button',
                                           ]);
        $this->setData('remove_promotion_price_button', $removePromotionPriceButton);

        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    public function getPromotionsFromDateModeOptions()
    {
        $optionsResult = [
            [
                'value' => Promotion::START_DATE_MODE_VALUE,
                'label' => __('Custom Value'),
            ],
        ];

        return array_merge(
            $optionsResult,
            $this->getMagentoAttributesOptions(
                'text_date',
                Promotion::START_DATE_MODE_ATTRIBUTE
            )
        );
    }

    public function getPromotionsToDateModeOptions()
    {
        $optionsResult = [
            [
                'value' => Promotion::END_DATE_MODE_VALUE,
                'label' => __('Custom Value'),
            ],
        ];

        return array_merge(
            $optionsResult,
            $this->getMagentoAttributesOptions(
                'text_date',
                Promotion::END_DATE_MODE_ATTRIBUTE
            )
        );
    }

    public function getPromotionsPriceModeOptions()
    {
        $optionsResult = [
            [
                'value' => Promotion::PRICE_MODE_PRODUCT,
                'label' => __('Product Price'),
            ],
            [
                'value' => Promotion::PRICE_MODE_SPECIAL,
                'label' => __('Special Price'),
            ],
        ];

        return array_merge(
            $optionsResult,
            $this->getMagentoAttributesOptions(
                'text_price',
                Promotion::PRICE_MODE_ATTRIBUTE
            )
        );
    }

    public function getPromotionsComparisonPriceModeOptions()
    {
        $optionsResult = [
            [
                'value' => Promotion::COMPARISON_PRICE_MODE_PRODUCT,
                'label' => __('Product Price'),
            ],
            [
                'value' => Promotion::COMPARISON_PRICE_MODE_SPECIAL,
                'label' => __('Special Price'),
            ],
        ];

        return array_merge(
            $optionsResult,
            $this->getMagentoAttributesOptions(
                'text_price',
                Promotion::COMPARISON_PRICE_MODE_ATTRIBUTE
            )
        );
    }

    //########################################

    public function getMagentoAttributesOptions($type, $value)
    {
        if (!isset($this->attributesByInputTypes[$type])) {
            return [];
        }

        $optionsResult = [];

        foreach ($this->attributesByInputTypes[$type] as $attribute) {
            $optionsResult[] = [
                'value' => $value,
                'label' => $this->escapeHtml($attribute['label']),
                'attrs' => ['attribute_code' => $attribute['code']],
            ];
        }

        return [
            [
                'value' => $optionsResult,
                'label' => 'Magento Attribute',
                'attrs' => ['is_magento_attribute' => true],
            ],
        ];
    }

    //########################################

    private function createElement($type, array $data)
    {
        $element = $this->elementFactory->create(
            $type,
            [
                'data' => $data,
            ]
        );
        $element->setForm($this->parentForm);

        if ($this->renderer === null) {
            $this->renderer = $this->getLayout()
                                   ->createBlock(\Ess\M2ePro\Block\Adminhtml\Magento\Form\Renderer\Element::class);
        }

        $element->setRenderer($this->renderer);

        return $element;
    }

    //########################################
}
