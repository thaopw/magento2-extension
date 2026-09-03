<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced;

use Ess\M2ePro\Model\ResourceModel\Ebay\Listing\Auto\Advanced\Filter as FilterResource;

class Filter extends \Ess\M2ePro\Model\ActiveRecord\Component\Child\Ebay\AbstractModel
{
    private ?\Ess\M2ePro\Model\Ebay\Template\Category $addingTemplateCategoryModel = null;
    private ?\Ess\M2ePro\Model\Ebay\Template\Category $addingTemplateCategorySecondaryModel = null;
    private ?\Ess\M2ePro\Model\Ebay\Template\StoreCategory $addingTemplateStoreCategoryModel = null;
    private ?\Ess\M2ePro\Model\Ebay\Template\StoreCategory $addingTemplateStoreCategorySecondaryModel = null;

    public function _construct()
    {
        $this->_init(FilterResource::class);
    }

    public function setListingAutoAdvancedFilterId(int $value): self
    {
        $this->setData(FilterResource::COLUMN_LISTING_AUTO_ADVANCED_FILTER_ID, $value);

        return $this;
    }

    public function setAddingTemplateCategoryId(?int $value): self
    {
        $this->setData(FilterResource::COLUMN_ADDING_TEMPLATE_CATEGORY_ID, $value);

        return $this;
    }

    public function getAddingTemplateCategoryId(): ?int
    {
        $value = $this->getData(FilterResource::COLUMN_ADDING_TEMPLATE_CATEGORY_ID);
        if (empty($value)) {
            return null;
        }

        return (int)$value;
    }

    public function setAddingTemplateCategorySecondaryId(?int $value): self
    {
        $this->setData(FilterResource::COLUMN_ADDING_TEMPLATE_CATEGORY_SECONDARY_ID, $value);

        return $this;
    }

    public function getAddingTemplateCategorySecondaryId(): ?int
    {
        $value = $this->getData(FilterResource::COLUMN_ADDING_TEMPLATE_CATEGORY_SECONDARY_ID);
        if (empty($value)) {
            return null;
        }

        return (int)$value;
    }

    public function setAddingTemplateStoreCategoryId(?int $value): self
    {
        $this->setData(FilterResource::COLUMN_ADDING_TEMPLATE_STORE_CATEGORY_ID, $value);

        return $this;
    }

    public function getAddingTemplateStoreCategoryId(): ?int
    {
        $value = $this->getData(FilterResource::COLUMN_ADDING_TEMPLATE_STORE_CATEGORY_ID);
        if (empty($value)) {
            return null;
        }

        return (int)$value;
    }

    public function setAddingTemplateStoreCategorySecondaryId(?int $value): self
    {
        $this->setData(FilterResource::COLUMN_ADDING_TEMPLATE_STORE_CATEGORY_SECONDARY_ID, $value);

        return $this;
    }

    public function getAddingTemplateStoreCategorySecondaryId(): ?int
    {
        $value = $this->getData(FilterResource::COLUMN_ADDING_TEMPLATE_STORE_CATEGORY_SECONDARY_ID);
        if (empty($value)) {
            return null;
        }

        return (int)$value;
    }

    public function getAddingTemplateCategory(): ?\Ess\M2ePro\Model\Ebay\Template\Category
    {
        if ($this->addingTemplateCategoryModel === null) {
            try {
                /** @var \Ess\M2ePro\Model\Ebay\Template\Category $model */
                $model = $this->activeRecordFactory->getCachedObjectLoaded(
                    'Ebay_Template_Category',
                    (int)$this->getAddingTemplateCategoryId()
                );

                $this->addingTemplateCategoryModel = $model;
            } catch (\Exception $exception) {
                return $this->addingTemplateCategoryModel;
            }
        }

        return $this->addingTemplateCategoryModel;
    }

    public function getAddingTemplateCategorySecondary(): ?\Ess\M2ePro\Model\Ebay\Template\Category
    {
        if ($this->addingTemplateCategorySecondaryModel === null) {
            try {
                $this->addingTemplateCategorySecondaryModel =
                    $this->activeRecordFactory->getCachedObjectLoaded(
                        'Ebay_Template_Category',
                        (int)$this->getAddingTemplateCategorySecondaryId()
                    );
            } catch (\Exception $exception) {
                return $this->addingTemplateCategorySecondaryModel;
            }
        }

        return $this->addingTemplateCategorySecondaryModel;
    }

    public function getAddingTemplateStoreCategory(): ?\Ess\M2ePro\Model\Ebay\Template\StoreCategory
    {
        if ($this->addingTemplateStoreCategoryModel === null) {
            try {
                $this->addingTemplateStoreCategoryModel = $this->activeRecordFactory->getCachedObjectLoaded(
                    'Ebay_Template_StoreCategory',
                    (int)$this->getAddingTemplateStoreCategoryId()
                );
            } catch (\Exception $exception) {
                return $this->addingTemplateStoreCategoryModel;
            }
        }

        return $this->addingTemplateStoreCategoryModel;
    }

    public function getAddingTemplateStoreCategorySecondary(): ?\Ess\M2ePro\Model\Ebay\Template\StoreCategory
    {
        if ($this->addingTemplateStoreCategorySecondaryModel === null) {
            try {
                $this->addingTemplateStoreCategorySecondaryModel =
                    $this->activeRecordFactory->getCachedObjectLoaded(
                        'Ebay_Template_StoreCategory',
                        (int)$this->getAddingTemplateStoreCategorySecondaryId()
                    );
            } catch (\Exception $exception) {
                return $this->addingTemplateStoreCategorySecondaryModel;
            }
        }

        return $this->addingTemplateStoreCategorySecondaryModel;
    }
}
