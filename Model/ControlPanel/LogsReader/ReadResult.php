<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\ControlPanel\LogsReader;

class ReadResult
{
    public bool $isSuccess;
    public string $failMessage;
    public string $content;
    public int $totalFileLines;
    public int $startLine;
    public bool $hasMore;

    private function __construct(
        bool $isSuccess,
        string $failMessage,
        string $content,
        int $totalFileLines,
        int $startLine,
        bool $hasMore
    ) {
        $this->isSuccess = $isSuccess;
        $this->failMessage = $failMessage;
        $this->content = $content;
        $this->totalFileLines = $totalFileLines;
        $this->startLine = $startLine;
        $this->hasMore = $hasMore;
    }

    public static function createSuccess(
        string $content,
        int $totalLines,
        int $startLine,
        bool $hasMore
    ): self {
        return new self(true, '', $content, $totalLines, $startLine, $hasMore);
    }

    public static function createFail(string $failMessage): self
    {
        return new self(false, $failMessage, '', 0, 0, false);
    }
}
