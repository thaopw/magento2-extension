<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\ControlPanel\LogsReader;

class LogFileFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function create(string $nick): LogFileInterface
    {
        return $this->objectManager->create($this->getClassByNick($nick));
    }

    private function getClassByNick(string $nick): string
    {
        $classMap = [
            \Ess\M2ePro\Model\ControlPanel\LogsReader\AvailableLogFiles\ExceptionLog::NICK =>
                \Ess\M2ePro\Model\ControlPanel\LogsReader\AvailableLogFiles\ExceptionLog::class,
        ];

        if (!isset($classMap[$nick])) {
            throw new \Ess\M2ePro\Model\Exception\Logic("Not found file by nick '$nick'");
        }

        return $classMap[$nick];
    }
}
