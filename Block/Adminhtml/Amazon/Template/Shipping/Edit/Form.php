<?php

declare(strict_types=1);

namespace Ess\M2ePro\Block\Adminhtml\Amazon\Template\Shipping\Edit;

class Form extends \Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractForm
{
    protected array $formData;

    private \Ess\M2ePro\Model\Amazon\Template\Shipping $shippingTemplate;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\BuilderFactory $templateShippingBuilderFactory;
    private \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping\Repository $templateShippingRepository;
    private \Ess\M2ePro\Model\Amazon\Account\Repository $amazonAccountRepository;
    private \Ess\M2ePro\Helper\Magento\Attribute $magentoAttributeHelper;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Template\Shipping $shippingTemplate,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\BuilderFactory $templateShippingBuilderFactory,
        \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping\Repository $templateShippingRepository,
        \Ess\M2ePro\Model\Amazon\Account\Repository $amazonAccountRepository,
        \Ess\M2ePro\Helper\Magento\Attribute $magentoAttributeHelper,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->shippingTemplate = $shippingTemplate;
        $this->templateShippingBuilderFactory = $templateShippingBuilderFactory;
        $this->templateShippingRepository = $templateShippingRepository;
        $this->amazonAccountRepository = $amazonAccountRepository;
        $this->magentoAttributeHelper = $magentoAttributeHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return void
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('amazonTemplateShippingEditForm');
    }

    protected function _prepareForm(): self
    {
        $formData = $this->getFormData();

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'method' => 'post',
                    'action' => $this->getUrl('*/*/save'),
                    'enctype' => 'multipart/form-data',
                    'class' => 'admin__scope-old',
                ],
            ]
        );

        $form->addField(
            'help',
            self::HELP_BLOCK,
            [
                'content' => __('<p style="margin: 20px 0 !important">Use this policy to configure what shipping settings ' .
                    'and how are applied to your Amazon listings. You can either <strong>select a specific ' .
                    'shipping template from your Amazon Seller Central account</strong> or <strong>map a ' .
                    'Magento product attribute that contains the template name</strong>, ' .
                    'allowing different products to use different Amazon shipping templates automatically.<p>'),
            ]
        );

        $generalFieldset = $form->addFieldset(
            'magento_block_amazon_template_shipping_general',
            [
                'legend' => __('General'),
                'collapsable' => false,
            ]
        );

        $this->addTitleField($generalFieldset, $formData);
        $this->addAccountField($generalFieldset, $formData);

        $channelFieldset = $form->addFieldset(
            'magento_block_amazon_template_shipping_channel',
            [
                'legend' => __('Channel'),
                'collapsable' => false,
            ]
        );

        $this->addModeField($channelFieldset, $formData);
        $this->addAmazonTemplatesField($channelFieldset, $formData);
        $this->addMagentoAttributesField($channelFieldset, $formData);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareLayout()
    {
        $this->jsUrl->addUrls(
            [
                'formSubmit' => $this->getUrl(
                    '*/amazon_template_shipping/save',
                    [
                        '_current' => $this->getRequest()->getParam('id'),
                        'close_on_save' => $this->getRequest()->getParam('close_on_save'),
                    ]
                ),
                'formSubmitNew' => $this->getUrl('*/amazon_template_shipping/save'),
                'deleteAction' => $this->getUrl(
                    '*/amazon_template_shipping/delete',
                    [
                        'id' => $this->getRequest()->getParam('id'),
                        'close_on_save' => $this->getRequest()->getParam('close_on_save'),
                    ]
                ),
                'amazon_template_shipping/refresh' =>
                    $this->getUrl('*/amazon_template_shipping/refresh/'),
                'amazon_template_shipping/getTemplates' =>
                    $this->getUrl('*/amazon_template_shipping/getTemplates/'),
            ]
        );

        $this->js->add(
            <<<JS
require(['M2ePro/Amazon/Template/Shipping'], function() {
    window.AmazonTemplateShippingObj = new AmazonTemplateShipping("{$this->getRequest()->getParam('id', '')}");
});
JS
        );

        return parent::_prepareLayout();
    }

    private function getFormData(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->formData)) {
            $tmpFormData = array_merge(
                $this->templateShippingBuilderFactory->create()->getDefaultData(),
                $this->shippingTemplate->toArray()
            );

            $accountId = $this->getRequest()->getParam('account_id');
            if (!empty($accountId)) {
                $tmpFormData['account_id'] = (int)$accountId;
            }

            $this->formData = $tmpFormData;
        }

        return $this->formData;
    }

    private function addTitleField(\Magento\Framework\Data\Form\Element\Fieldset $fieldset, array $formData): void
    {
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'value' => $formData['title'],
                'class' => 'M2ePro-shipping-tpl-title',
                'tooltip' => __('Short meaningful Policy Title for your internal use.'),
                'required' => true,
            ]
        );
    }

    private function addAccountField(\Magento\Framework\Data\Form\Element\Fieldset $fieldset, array $formData): void
    {
        $fieldset->addField(
            'account_id',
            'hidden',
            [
                'name' => 'account_id',
                'value' => $formData['account_id'],
            ]
        );

        $accountOptions = [
            ['value' => '', 'label' => '', 'attrs' => ['style' => 'display: none;']],
        ];
        foreach ($this->amazonAccountRepository->getAll() as $account) {
            /** @var \Ess\M2ePro\Model\Account $parent */
            $parent = $account->getParentObject();

            $accountOptions[] = [
                'value' => $parent->getId(),
                'label' => $parent->getTitle(),
            ];
        }

        $fieldset->addField(
            'account_select',
            self::SELECT,
            [
                'label' => __('Account'),
                'title' => __('Account'),
                'values' => $accountOptions,
                'value' => $formData['account_id'],
                'required' => true,
                'disabled' => !empty($formData['account_id']),
            ]
        );
    }

    private function addModeField(\Magento\Framework\Data\Form\Element\Fieldset $fieldset, array $formData): void
    {
        $modeOptions = [
            [
                'label' => __('Amazon Template'),
                'value' => \Ess\M2ePro\Model\Amazon\Template\Shipping::MODE_AMAZON_TEMPLATE,
            ],
            [
                'label' => __('Magento Attribute'),
                'value' => \Ess\M2ePro\Model\Amazon\Template\Shipping::MODE_MAGENTO_ATTRIBUTE,
            ],
        ];

        $fieldset->addField(
            'template_mode',
            self::SELECT,
            [
                'label' => __('Mode / Source'),
                'name' => 'mode',
                'values' => $modeOptions,
                'value' => $formData['mode'],
                'required' => true,
                'tooltip' => __('Choose where shipping settings come from.'),
            ]
        );
    }

    private function addMagentoAttributesField(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldset,
        array $formData
    ): void {
        $allAttributes = $this->magentoAttributeHelper->getAll();
        $attributes = $this->magentoAttributeHelper
            ->filterByInputTypes($allAttributes, ['text']);

        $preparedOptions = [];
        foreach ($attributes as $attribute) {
            $preparedOptions[] = [
                'value' => $attribute['code'],
                'label' => $this->_escaper->escapeHtml($attribute['label']),
            ];
        }

        $fieldset->addField(
            'magento_attributes',
            self::SELECT,
            [
                'name' => 'custom_attribute',
                'container_id' => 'magento_attribute_tr',
                'label' => __('Magento Attribute'),
                'values' => $preparedOptions,
                'value' => $formData['custom_attribute'],
                'required' => true,
                'create_magento_attribute' => true,
            ]
        )->addCustomAttribute('allowed_attribute_types', 'text');
    }

    private function addAmazonTemplatesField(
        \Magento\Framework\Data\Form\Element\Fieldset $channelFieldset,
        array $formData
    ): void {
        $resetButton = $this
            ->getLayout()
            ->createBlock(\Ess\M2ePro\Block\Adminhtml\Magento\Button::class)
            ->addData(
                [
                    'id' => 'refresh_templates',
                    'label' => __('Refresh Templates'),
                    'class' => 'action-primary',
                    'style' => 'margin-left: 20px;',
                ]
            );

        $templates = $this->templateShippingRepository
            ->getByAccountId((int)$formData['account_id']);

        $templateOptions = [
            ['value' => '', 'label' => '', 'attrs' => ['style' => 'display: none;']],
        ];

        foreach ($templates as $template) {
            $templateOptions[] = [
                'value' => $template->getTemplateId(),
                'label' => $template->getTitle(),
            ];
        }

        $channelFieldset->addField(
            'template_id',
            self::SELECT,
            [
                'container_id' => 'amazon_template_tr',
                'name' => 'template_id',
                'label' => __('Template'),
                'value' => $formData['template_id'],
                'values' => $templateOptions,
                'required' => true,
                'tooltip' => __('Select the Amazon shipping template to apply to listings.'),
                'after_element_html' => $resetButton->toHtml(),
            ]
        );
    }
}
