<?php

declare(strict_types=1);

namespace Ess\M2ePro\Model\Amazon\Listing\Product\Action\DataBuilder;

class Condition extends AbstractModel
{
    public function getBuilderData(): array
    {
        $condition = [];
        $listingSource = $this->getAmazonListingProduct()->getListingSource();

        $this->searchNotFoundAttributes();
        $condition['condition'] = $listingSource->getCondition();
        $condition['condition_note'] = $listingSource->getConditionNote();
        $this->processNotFoundAttributes('Condition / Condition Note');

        return $condition;
    }
}
