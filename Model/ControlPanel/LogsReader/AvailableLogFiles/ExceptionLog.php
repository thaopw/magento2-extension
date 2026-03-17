<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\ControlPanel\LogsReader\AvailableLogFiles;

class ExceptionLog implements \Ess\M2ePro\Model\ControlPanel\LogsReader\LogFileInterface
{
    public const NICK = 'exception.log';
    private const PATH = 'exception.log';

    private \Magento\Framework\Filesystem\Directory\ReadInterface $logDirectoryRead;

    public function __construct(\Magento\Framework\Filesystem $filesystem)
    {
        $this->logDirectoryRead = $filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::LOG);
    }

    public function isExist(): bool
    {
        return $this->logDirectoryRead->isExist(self::PATH);
    }

    public function isReadable(): bool
    {
        return $this->logDirectoryRead->isReadable(self::PATH)
            && !$this->logDirectoryRead->isDirectory(self::PATH);
    }

    public function getAbsolutePath(): string
    {
        return $this->logDirectoryRead->getAbsolutePath(self::PATH);
    }
}
