<?php

declare(strict_types=1);

namespace Ess\M2ePro\Block\Adminhtml\Listing\Product\AdvancedFilter;

abstract class AbstractRenderer extends \Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractForm
{
    abstract public function renderJs(
        \Ess\M2ePro\Block\Adminhtml\Magento\Renderer\JsRenderer $js,
        \Ess\M2ePro\Block\Adminhtml\Magento\Renderer\JsUrlRenderer $jsUrl,
        \Ess\M2ePro\Block\Adminhtml\Magento\Renderer\JsTranslatorRenderer $jsTranslator
    ): void;

    abstract public function renderHtml(string $searchBtnHtml, string $resetBtnHtml): string;

    public function addCss(\Ess\M2ePro\Block\Adminhtml\Magento\Renderer\CssRenderer $css): void
    {
        $css->add(
            <<<CSS
        .advanced-filter-btn-wrap > button {
            font-size: 11px;
        }

        .advanced-filter-select {
            margin-bottom: 15px;
        }
CSS
        );
    }

    protected function wrapFilterHtmlBtn(string $htmlBtn): string
    {
        return sprintf('<div class="advanced-filter-btn-wrap">%s</div>', $htmlBtn);
    }
}
