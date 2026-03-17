<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Controller\Adminhtml\ControlPanel;

abstract class Command extends \Ess\M2ePro\Controller\Adminhtml\Base
{
    /** @var \Ess\M2ePro\Helper\View\ControlPanel */
    protected $controlPanelHelper;

    public function __construct(
        \Ess\M2ePro\Helper\View\ControlPanel $controlPanelHelper,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->controlPanelHelper = $controlPanelHelper;
    }

    public function execute()
    {
        if (!($action = $this->getRequest()->getParam('action'))) {
            return $this->_redirect($this->controlPanelHelper->getPageInspectionTabUrl());
        }

        $methodName = $action . 'Action';

        if (!method_exists($this, $methodName)) {
            return $this->_redirect($this->controlPanelHelper->getPageInspectionTabUrl());
        }

        $actionResult = $this->$methodName();

        if (is_string($actionResult)) {
            $this->getRawResult()->setContents($actionResult);

            return $this->getRawResult();
        }

        return $actionResult;
    }

    protected function _validateSecretKey()
    {
        return true;
    }

    //########################################

    /**
     * It will allow to use control panel features even if extension is disabled, etc.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return bool
     */
    protected function preDispatch(\Magento\Framework\App\RequestInterface $request)
    {
        return true;
    }

    //########################################

    protected function jsonEncode($data)
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function getStyleHtml(): string
    {
        return <<<HTML
<style>
    * {
        font-size: 1rem;
        line-height: 1.5;
        box-sizing: border-box;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e2e8f0;
    }

    .table caption {
        font-size: 1.25rem;
        text-align: left;
        font-weight: 700;
        margin: .25rem 0;
    }

    .table td {
        padding: .75rem;
        vertical-align: top;
        border-bottom: 1px solid #e2e8f0;
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    .table td.label {
        width: 200px;
        background-color: #f8fafc;
        font-weight: 700;
        color: #475569;
        border-right: 1px solid #e2e8f0;
    }

    table.grid {
        border-color: black;
        border-style: solid;
        border-width: 1px 0 0 1px;
    }

    table.grid th {
        padding: 5px 20px;
        border-color: black;
        border-style: solid;
        border-width: 0 1px 1px 0;
        background-color: silver;
        color: white;
        font-weight: bold;
    }

    table.grid td {
        padding: 3px 10px;
        border-color: black;
        border-style: solid;
        border-width: 0 1px 1px 0;
    }

    pre {
        margin: 0;
        padding: 1.25rem;
        background-color: #ffffff;
        color: #24292e;
        border: 1px solid #e1e4e8;
        border-radius: 8px;
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        line-height: 1.6;
        overflow-x: auto;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    input, select  {
        outline: 0;
        padding: .25rem .375rem;
        background-color: #fff;
        border: 1px solid #ced4da;
        color: #212529;
        border-radius: .25rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }

    input:focus, select:focus {
        color: #212529;
        background-color: #fff;
        border-color: #86b7fe;
        box-shadow: 0 0 0 .15rem rgba(13, 110, 253, .25);
    }

    .row {
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .row:not(:last-child) {
        margin-bottom: 10px;
    }

    .required:after {
        content: ' *';
        color: #d63745;
    }

    .button {
        display: inline-block;
        padding: .375rem .75rem;
        color: #fff;
        text-align: center;
        text-decoration: none;
        vertical-align: middle;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
        background-color: #0d6efd;
        border: 1px solid #0d6efd;
        border-radius: .25rem;
        transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }

    .button:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }

    .card {
        border-radius: .25rem;
        position: relative;
        padding: 1rem;
        margin: 1rem 0;
        border: 1px solid #dee2e6;
    }

    .red {
        color: #842029;
        background-color: #f8d7da;
        border-color: #f5c2c7;
    }

    .gray {
        color: #41464b;
        background-color: #e2e3e5;
        border-color: #d3d6d8;
    }

    .dark-gray {
        color: #141619;
        background-color: #d3d3d4;
        border-color: #bcbebf;
    }

</style>
HTML;
    }

    //########################################
}
