<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\ControlPanel\LogsReader;

interface LogFileInterface
{
    public function isExist(): bool;
    public function isReadable(): bool;
    public function getAbsolutePath(): string;
}
