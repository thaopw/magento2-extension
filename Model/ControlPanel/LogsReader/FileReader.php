<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\ControlPanel\LogsReader;

class FileReader
{
    public const READ_LINES_LIMIT = 2000;

    public function readLines(
        LogFileInterface $file,
        ?int $startLine = null
    ): ReadResult {
        if (!$file->isExist()) {
            return ReadResult::createFail("Not found file: {$file->getAbsolutePath()}");
        }

        if (!$file->isReadable()) {
            return ReadResult::createFail("File is not readable: {$file->getAbsolutePath()}");
        }

        try {
            $fileObj = new \SplFileObject($file->getAbsolutePath(), 'r');

            if ($fileObj->getSize() === 0) {
                return ReadResult::createSuccess('File is empty.', 0, 0, false);
            }

            $totalLines = $this->getFileTotalLines($fileObj);

            if ($startLine === null) {
                $startLine = max(0, $totalLines - self::READ_LINES_LIMIT);
            }

            $iterator = new \LimitIterator(
                $fileObj,
                max(0, $startLine),
                self::READ_LINES_LIMIT
            );

            $content = '';
            foreach ($iterator as $line) {
                $content .= $line;
            }

            return ReadResult::createSuccess(
                $content,
                $totalLines,
                $startLine,
                $startLine > 0
            );
        } catch (\Throwable $e) {
            return ReadResult::createFail("Error reading file: " . $e->getMessage());
        }
    }

    private function getFileTotalLines(\SplFileObject $fileObj): int
    {
        $fileObj->seek(PHP_INT_MAX);
        $totalLines = $fileObj->key();

        $fileObj->rewind();

        return $totalLines;
    }
}
