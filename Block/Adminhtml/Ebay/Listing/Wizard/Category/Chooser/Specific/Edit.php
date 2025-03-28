<?php

declare(strict_types=1);

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Listing\Wizard\Category\Chooser\Specific;

use Ess\M2ePro\Block\Adminhtml\Ebay\Template\Category\Chooser\Specific\Form\Element\Dictionary;
use Ess\M2ePro\Model\Ebay\Template\Category\Specific as Specific;
use Ess\M2ePro\Model\Ebay\Template\Category\BuilderFactory;
use Ess\M2ePro\Helper\Component\Ebay\Category\Ebay;
use Ess\M2ePro\Block\Adminhtml\Magento\Context\Widget;
use Ess\M2ePro\Helper\Data;

class Edit extends \Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private BuilderFactory $ebayCategoryBuilderFactory;

    private Ebay $componentEbayCategoryEbay;

    private Data $dataHelper;

    public function __construct(
        BuilderFactory $ebayCategoryBuilderFactory,
        Ebay $componentEbayCategoryEbay,
        Widget $context,
        Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->ebayCategoryBuilderFactory = $ebayCategoryBuilderFactory;
        $this->componentEbayCategoryEbay = $componentEbayCategoryEbay;
        $this->dataHelper = $dataHelper;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('ebayTemplateCategoryChooserSpecificEdit');

        $this->_controller = 'adminhtml_ebay_template_category_chooser_specific';
        $this->_mode = 'edit';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    public function prepareFormData()
    {
        $templateSpecifics = [];
        $dictionarySpecifics = $this->getDictionarySpecifics();

        $selectedSpecs = \Ess\M2ePro\Helper\Json::decode($this->getData('selected_specifics'));

        if ($this->getData('template_id')) {
            /** @var \Ess\M2ePro\Model\Ebay\Template\Category $template */
            $template = $this->activeRecordFactory->getObjectLoaded(
                'Ebay_Template_Category',
                $this->getData('template_id')
            );
            $templateSpecifics = $template->getSpecifics();
        } elseif (!empty($selectedSpecs)) {
            $builder = $this->ebayCategoryBuilderFactory->create();
            foreach ($selectedSpecs as $selectedSp) {
                $templateSpecifics[] = $builder->serializeSpecific($selectedSp);
            }
        } else {
            /** @var \Ess\M2ePro\Model\Ebay\Template\Category $template */
            $template = $this->activeRecordFactory->getObject('Ebay_Template_Category');
            $template->loadByCategoryValue(
                $this->getData('category_value'),
                $this->getData('category_mode'),
                $this->getData('marketplace_id'),
                0
            );

            $template->getId() && $templateSpecifics = $template->getSpecifics();
        }

        foreach ($dictionarySpecifics as &$dictionarySpecific) {
            foreach ($templateSpecifics as $templateSpecific) {
                if ($dictionarySpecific['title'] == $templateSpecific['attribute_title']) {
                    $dictionarySpecific['template_specific'] = $templateSpecific;
                    continue;
                }
            }
        }

        unset($dictionarySpecific);

        $templateCustomSpecifics = [];
        foreach ($templateSpecifics as $templateSpecific) {
            if ($templateSpecific['mode'] == Specific::MODE_CUSTOM_ITEM_SPECIFICS) {
                $templateCustomSpecifics[] = $templateSpecific;
            }
        }

        $this->getChildBlock('form')->setData(
            'form_data',
            [
                'dictionary_specifics' => $dictionarySpecifics,
                'template_custom_specifics' => $templateCustomSpecifics,
            ]
        );
    }

    protected function getDictionarySpecifics()
    {
        if ($this->getData('category_mode') == \Ess\M2ePro\Model\Ebay\Template\Category::CATEGORY_MODE_ATTRIBUTE) {
            return [];
        }

        $specifics = $this->componentEbayCategoryEbay->getSpecifics(
            $this->getData('category_value'),
            $this->getData('marketplace_id')
        );

        return $specifics === null ? [] : $specifics;
    }

    protected function _toHtml()
    {
        $infoBlock = $this->getLayout()->createBlock(
            Info::class,
            '',
            [
                'data' => [
                    'category_mode' => $this->getData('category_mode'),
                    'category_value' => $this->getData('category_value'),
                    'marketplace_id' => $this->getData('marketplace_id'),
                ],
            ]
        );

        $this->jsTranslator->addTranslations(
            [
                'Item Specifics cannot have the same Labels.' => $this->__(
                    'Item Specifics cannot have the same Labels.'
                ),
            ]
        );
        $this->jsPhp->addConstants(
            $this->dataHelper->getClassConstants(\Ess\M2ePro\Model\Ebay\Template\Category\Specific::class)
        );

        $parentHtml = parent::_toHtml();

        $jsCustomChangeSpecifics = '';
        if (Dictionary::$isSetMappedAttribute) {
            $jsCustomChangeSpecifics = 'EbayTemplateCategorySpecificsObj.markAsCustomChanged();';
        }

        $this->js->add(
            <<<JS
    require([
        'M2ePro/Ebay/Template/Category/Specifics'
    ], function(){

        window.EbayTemplateCategorySpecificsObj = new EbayTemplateCategorySpecifics();
        EbayTemplateCategorySpecificsObj.createSpecificsSnapshot();
        $jsCustomChangeSpecifics
    });
JS
        );

        return <<<HTML
<div id="chooser_container_specific">

    <div style="margin-top: 15px;">
        {$infoBlock->_toHtml()}
    </div>

    <div id="ebay-category-chooser-specific" overflow: auto;">
        {$parentHtml}
    </div>

</div>
HTML;
    }
}
