<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Template;

use Ess\M2ePro\Model\ResourceModel\Amazon\Template\Shipping as TemplateShippingResource;

class Shipping extends \Ess\M2ePro\Model\ActiveRecord\Component\AbstractModel
{
    public const MODE_AMAZON_TEMPLATE = 1;
    public const MODE_MAGENTO_ATTRIBUTE = 2;

    private array $shippingTemplateSourceModels = [];

    private \Ess\M2ePro\Model\Amazon\Template\Shipping\SourceFactory $sourceFactory;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Template\Shipping\SourceFactory $sourceFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->sourceFactory = $sourceFactory;

        parent::__construct(
            $modelFactory,
            $activeRecordFactory,
            $helperFactory,
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(TemplateShippingResource::class);
    }

    /**
     * @param \Ess\M2ePro\Model\Magento\Product $magentoProduct
     *
     * @return \Ess\M2ePro\Model\Amazon\Template\Shipping\Source
     */
    public function getSource(\Ess\M2ePro\Model\Magento\Product $magentoProduct): Shipping\Source
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->shippingTemplateSourceModels[$productId])) {
            return $this->shippingTemplateSourceModels[$productId];
        }

        $sourceModel = $this->sourceFactory->create();
        $sourceModel->setMagentoProduct($magentoProduct);
        $sourceModel->setShippingTemplate($this);

        $this->shippingTemplateSourceModels[$productId] = $sourceModel;

        return $this->shippingTemplateSourceModels[$productId];
    }

    public function getId(): int
    {
        return (int)parent::getId();
    }

    public function getTitle(): string
    {
        return (string)$this->getData(TemplateShippingResource::COLUMN_TITLE);
    }

    public function getAccountId(): int
    {
        return (int)$this->getData(TemplateShippingResource::COLUMN_ACCOUNT_ID);
    }

    public function isModeAmazonTemplate(): bool
    {
        return (int)$this->getData(TemplateShippingResource::COLUMN_MODE) === self::MODE_AMAZON_TEMPLATE;
    }

    public function isModeMagentoAttribute(): bool
    {
        return (int)$this->getData(TemplateShippingResource::COLUMN_MODE) === self::MODE_MAGENTO_ATTRIBUTE;
    }

    public function getCustomAttribute(): string
    {
        return (string)$this->getData(TemplateShippingResource::COLUMN_CUSTOM_ATTRIBUTE);
    }

    public function getTemplateId(): string
    {
        return (string)$this->getData(TemplateShippingResource::COLUMN_TEMPLATE_ID);
    }

    public function create(
        string $title,
        int $accountId,
        int $marketplaceId,
        int $mode,
        string $templateId,
        string $customAttribute
    ): self {
        $this
            ->setData(TemplateShippingResource::COLUMN_TITLE, $title)
            ->setData(TemplateShippingResource::COLUMN_ACCOUNT_ID, $accountId)
            ->setData(TemplateShippingResource::COLUMN_MARKETPLACE_ID, $marketplaceId)
            ->setData(TemplateShippingResource::COLUMN_MODE, $mode)
            ->setData(TemplateShippingResource::COLUMN_TEMPLATE_ID, $templateId)
            ->setData(TemplateShippingResource::COLUMN_CUSTOM_ATTRIBUTE, $customAttribute)
            ->setData(TemplateShippingResource::COLUMN_CREATE_DATE, \Ess\M2ePro\Helper\Date::createCurrentGmt());

        return $this;
    }

    public function update(string $title, int $mode, string $templateId, string $customAttribute): self
    {
        $this
            ->setData(TemplateShippingResource::COLUMN_TITLE, $title)
            ->setData(TemplateShippingResource::COLUMN_MODE, $mode)
            ->setData(TemplateShippingResource::COLUMN_TEMPLATE_ID, $templateId)
            ->setData(TemplateShippingResource::COLUMN_CUSTOM_ATTRIBUTE, $customAttribute);

        return $this;
    }
}
