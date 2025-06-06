<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Ebay\ComplianceDocuments\ProductDocumentUrlFinder;

class Result
{
    private const STATUS_SUCCESS = 'success';
    private const STATUS_FAIL = 'fail';

    private string $status;
    private string $type;
    private string $url;
    private string $failMessage;
    private array $languages;

    private function __construct(
        string $status,
        string $type,
        array $languages,
        string $url,
        string $failMessage
    ) {
        $this->status = $status;
        $this->type = $type;
        $this->url = $url;
        $this->failMessage = $failMessage;
        $this->languages = $languages;
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isFail(): bool
    {
        return $this->status === self::STATUS_FAIL;
    }

    public function getFailMessage(): string
    {
        return $this->failMessage;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getLanguages(): array
    {
        return $this->languages;
    }

    // ----------------------------------------

    public static function createSuccess(string $type, array $languages, string $url): self
    {
        return new self(self::STATUS_SUCCESS, $type, $languages, $url, '');
    }

    public static function createFail(string $type, array $languages, string $failMessage): self
    {
        return new self(self::STATUS_FAIL, $type, $languages, '', $failMessage);
    }
}
