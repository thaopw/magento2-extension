<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\ControlPanel\LogsReader;

class GetFileContent extends \Ess\M2ePro\Controller\Adminhtml\ControlPanel\Main
{
    private \Ess\M2ePro\Model\ControlPanel\LogsReader\FileReader $logFileReader;
    private \Ess\M2ePro\Model\ControlPanel\LogsReader\LogFileFactory $logFileFactory;

    public function __construct(
        \Ess\M2ePro\Model\ControlPanel\LogsReader\FileReader $logFileReader,
        \Ess\M2ePro\Model\ControlPanel\LogsReader\LogFileFactory $logFileFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        $this->logFileReader = $logFileReader;
        $this->logFileFactory = $logFileFactory;
    }

    public function execute()
    {
        $this->getResult()->setHeader('Content-Type', 'application/json');

        try {
            $readerResult = $this->logFileReader->readLines(
                $this->getFilePathFromRequest(),
                $this->getStartLineFromRequest()
            );

            $this->setJsonContent([
                'success' => $readerResult->isSuccess,
                'message' => $readerResult->failMessage,
                'content' => $readerResult->content,
                'total_lines' => $readerResult->totalFileLines,
                'start_line' => $readerResult->startLine,
                'has_more' => $readerResult->hasMore,
            ]);
        } catch (\Throwable $e) {
            $this->setJsonContent([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        return $this->getResult();
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    private function getFilePathFromRequest(): \Ess\M2ePro\Model\ControlPanel\LogsReader\LogFileInterface
    {
        $file = (string)$this->getRequest()->getParam('file');
        if (empty($file)) {
            throw new \Ess\M2ePro\Model\Exception\Logic('File path is required.');
        }

        return $this->logFileFactory->create($file);
    }

    private function getStartLineFromRequest(): ?int
    {
        $startLine = $this->getRequest()->getParam('startLine');
        if ($startLine === null || $startLine === '') {
            return null;
        }

        return (int)$startLine;
    }
}
