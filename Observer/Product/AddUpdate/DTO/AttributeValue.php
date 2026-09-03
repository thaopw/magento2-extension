<?php

declare(strict_types=1);

namespace Ess\M2ePro\Observer\Product\AddUpdate\DTO;

class AttributeValue
{
    private string $attributeCode;
    private string $attributeValue;

    public function __construct(
        string $attributeCode,
        string $attributeValue
    ) {
        $this->attributeCode = $attributeCode;
        $this->attributeValue = $attributeValue;
    }

    public function getAttributeCode(): string
    {
        return $this->attributeCode;
    }

    public function getAttributeValue(): string
    {
        return $this->attributeValue;
    }

    public function isEqual(self $attributeValue): bool
    {
        return $this->getAttributeCode() === $attributeValue->getAttributeCode()
            && $this->getAttributeValue() === $attributeValue->getAttributeValue();
    }
}
