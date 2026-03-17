<?php

declare(strict_types=1);

namespace Ess\M2ePro\Block\Adminhtml\ControlPanel\Tabs;

use Ess\M2ePro\Block\Adminhtml\Magento\AbstractBlock;

class LogsReader extends AbstractBlock
{
    protected $_template = 'control_panel/tabs/logs_reader.phtml';

    public function _construct()
    {
        parent::_construct();

        $this->setId('controlPanelLogsReader');
    }

    public function getFileContentUrl(): string
    {
        return $this->getUrl('*/controlPanel_logsReader/getFileContent');
    }

    public function getDownloadUrl(): string
    {
        return $this->getUrl('*/controlPanel_logsReader/download');
    }

    public function getDefaultLogFile(): string
    {
        return \Ess\M2ePro\Model\ControlPanel\LogsReader\AvailableLogFiles\ExceptionLog::NICK;
    }
}
