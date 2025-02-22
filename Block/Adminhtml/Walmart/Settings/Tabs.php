<?php

namespace Ess\M2ePro\Block\Adminhtml\Walmart\Settings;

class Tabs extends \Ess\M2ePro\Block\Adminhtml\Settings\Tabs
{
    public const TAB_ID_GENERAL = 'general';
    public const TAB_ID_ATTRIBUTE_MAPPING = 'mapping';

    //########################################

    protected function _prepareLayout()
    {
        // ---------------------------------------

        $tab = [
            'label' => __('General'),
            'title' => __('General'),
            'content' => $this->getLayout()
                              ->createBlock(\Ess\M2ePro\Block\Adminhtml\Walmart\Settings\Tabs\General::class)
                              ->toHtml(),
        ];

        $this->addTab(self::TAB_ID_GENERAL, $tab);

        // ---------------------------------------

        // ---------------------------------------

        $tab = [
            'label' => __('Synchronization'),
            'title' => __('Synchronization'),
            'content' => $this->getLayout()
                              ->createBlock(\Ess\M2ePro\Block\Adminhtml\Walmart\Settings\Tabs\Synchronization::class)
                              ->toHtml(),
        ];

        $this->addTab(self::TAB_ID_SYNCHRONIZATION, $tab);

        // ---------------------------------------

        $attributeMappingTabContent = $this
            ->getLayout()
            ->createBlock(\Ess\M2ePro\Block\Adminhtml\Walmart\Settings\Tabs\AttributeMapping::class)
            ->toHtml();

        $this->addTab(self::TAB_ID_ATTRIBUTE_MAPPING, [
            'label' => __('Attribute Mapping'),
            'title' => __('Attribute Mapping'),
            'content' => $attributeMappingTabContent,
        ]);

        // ----------------------------------------

        return parent::_prepareLayout();
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->jsUrl->add($this->getUrl('*/walmart/getGlobalMessages'), 'getGlobalMessages');

        return parent::_beforeToHtml();
    }

    //########################################
}
