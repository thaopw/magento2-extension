<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\Filter;

class Create
{
    private \Ess\M2ePro\Model\Listing\Auto\Advanced\FilterFactory $autoAdvancedFilterFactory;
    private \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\FilterFactory $ebayAdvancedFilterFactory;
    private \Ess\M2ePro\Model\Listing\Auto\Advanced\Filter\AttributesCache $attributesCache;

    public function __construct(
        \Ess\M2ePro\Model\Listing\Auto\Advanced\FilterFactory $autoAdvancedFilterFactory,
        \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\FilterFactory $ebayAdvancedFilterFactory,
        \Ess\M2ePro\Model\Listing\Auto\Advanced\Filter\AttributesCache $attributesCache
    ) {
        $this->autoAdvancedFilterFactory = $autoAdvancedFilterFactory;
        $this->ebayAdvancedFilterFactory = $ebayAdvancedFilterFactory;
        $this->attributesCache = $attributesCache;
    }

    public function execute(
        int $listingId,
        string $title,
        int $addingMode,
        int $addingAddNotVisible,
        int $deletingMode,
        string $condition,
        ?int $addingTemplateCategoryId,
        ?int $addingTemplateCategorySecondaryId,
        ?int $addingTemplateStoreCategoryId,
        ?int $addingTemplateStoreCategorySecondaryId
    ): \Ess\M2ePro\Model\Listing\Auto\Advanced\Filter {
        $parent = $this->autoAdvancedFilterFactory
            ->createWithComponentModeEbay()
            ->setListingId($listingId)
            ->setTitle($title)
            ->setAddingMode($addingMode)
            ->setAddingAddNotVisible($addingAddNotVisible)
            ->setDeletingMode($deletingMode)
            ->setCondition($condition);

        $child = $this->ebayAdvancedFilterFactory->create()
            ->setAddingTemplateCategoryId($addingTemplateCategoryId)
            ->setAddingTemplateCategorySecondaryId($addingTemplateCategorySecondaryId)
            ->setAddingTemplateStoreCategoryId($addingTemplateStoreCategoryId)
            ->setAddingTemplateStoreCategorySecondaryId($addingTemplateStoreCategorySecondaryId);

        $parent->save();
        $child->setListingAutoAdvancedFilterId((int)$parent->getId())->save();

        $parent->setChildObject($child);

        $this->attributesCache->reset();

        return $parent;
    }
}
