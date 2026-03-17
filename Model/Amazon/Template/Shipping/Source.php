<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Template\Shipping;

class Source extends \Ess\M2ePro\Model\AbstractModel
{
    private \Ess\M2ePro\Model\Magento\Product $magentoProduct;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping $shippingTemplateModel;
    private \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping\Repository $shippingDictionaryRepository;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping\Repository $shippingDictionaryRepository,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        array $data = []
    ) {
        parent::__construct($helperFactory, $modelFactory, $data);
        $this->shippingDictionaryRepository = $shippingDictionaryRepository;
    }

    public function setMagentoProduct(\Ess\M2ePro\Model\Magento\Product $magentoProduct): self
    {
        $this->magentoProduct = $magentoProduct;

        return $this;
    }

    public function getMagentoProduct(): ?\Ess\M2ePro\Model\Magento\Product
    {
        return $this->magentoProduct;
    }

    public function setShippingTemplate(\Ess\M2ePro\Model\Amazon\Template\Shipping $instance): self
    {
        $this->shippingTemplateModel = $instance;

        return $this;
    }

    public function getShippingTemplate(): ?\Ess\M2ePro\Model\Amazon\Template\Shipping
    {
        return $this->shippingTemplateModel;
    }

    // ----------------------------------------

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function getTemplateId(): string
    {
        if ($this->getShippingTemplate()->isModeAmazonTemplate()) {
            return $this->getShippingTemplate()->getTemplateId();
        }

        return $this->getTemplateIdFromMagentoAttribute();
    }

    private function getTemplateIdFromMagentoAttribute(): string
    {
        $attributeCode = $this->getShippingTemplate()->getCustomAttribute();
        if (empty($attributeCode)) {
            throw new CustomAttributeException(
                (string)__('Shipping template was not synchronized to Amazon because the ' .
                    'attribute not set in Shipping Template')
            );
        }

        $this->getMagentoProduct()->clearNotFoundAttributes();
        $attributeValue = $this->getMagentoProduct()->getAttributeValue($attributeCode);
        $notFoundAttributes = $this->getMagentoProduct()->getNotFoundAttributes();

        if ($notFoundAttributes) {
            throw new CustomAttributeException(
                (string)__(
                    'Shipping template was not synchronized to Amazon because the ' .
                    'specified attribute could not be found in the Magento product.'
                )
            );
        }

        if (empty($attributeValue)) {
            throw new CustomAttributeException(
                (string)__(
                    'Shipping template was not synchronized to Amazon because the ' .
                    'specified attribute has no value in the Magento product.'
                )
            );
        }

        $shippingTemplates = $this->shippingDictionaryRepository
            ->getByAccountId($this->getShippingTemplate()->getAccountId());

        foreach ($shippingTemplates as $shippingTemplate) {
            if ($this->isShippingTemplateMatching($shippingTemplate, $attributeValue)) {
                return $shippingTemplate->getTemplateId();
            }
        }

        throw new CustomAttributeException(
            (string)__(
                'Shipping template was not synchronized to Amazon because the specified ' .
                'template name does not match any templates available in your seller account.'
            )
        );
    }

    private function prepareTitle(string $title): string
    {
        return mb_strtolower(trim($title));
    }

    private function isShippingTemplateMatching(
        \Ess\M2ePro\Model\Amazon\Dictionary\TemplateShipping $shippingTemplate,
        string $attributeValue
    ): bool {
        $preparedTemplateTitle = $this->prepareTitle($shippingTemplate->getTitle());
        $preparedTemplateId = $this->prepareTitle($shippingTemplate->getTemplateId());
        $preparedAttributeValue = $this->prepareTitle($attributeValue);

        if (
            $preparedTemplateTitle === $preparedAttributeValue
            || $preparedTemplateId === $preparedAttributeValue
        ) {
            return true;
        }

        return false;
    }
}
