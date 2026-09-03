<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction;

class Reset extends \Ess\M2ePro\Controller\Adminhtml\Ebay\Listing\AutoAction
{
    private \Ess\M2ePro\Model\Ebay\Listing\Repository $ebayListingRepository;
    private \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\Filter\Repository $autoAdvancedFilterRepository;
    private \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\Filter\Delete $autoAdvancedFilterDelete;

    public function __construct(
        \Ess\M2ePro\Model\Ebay\Listing\Repository $ebayListingRepository,
        \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\Filter\Delete $autoAdvancedFilterDelete,
        \Ess\M2ePro\Model\Ebay\Listing\Auto\Advanced\Filter\Repository $autoAdvancedFilterRepository,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($ebayFactory, $context);
        $this->ebayListingRepository = $ebayListingRepository;
        $this->autoAdvancedFilterRepository = $autoAdvancedFilterRepository;
        $this->autoAdvancedFilterDelete = $autoAdvancedFilterDelete;
    }

    public function execute()
    {
        $listing = $this->ebayListingRepository
            ->get((int)$this->getRequest()->getParam('listing_id'));

        $data = [
            'auto_mode' => \Ess\M2ePro\Model\Listing::AUTO_MODE_NONE,
            'auto_global_adding_mode' => \Ess\M2ePro\Model\Listing::ADDING_MODE_ADD,
            'auto_global_adding_add_not_visible' => \Ess\M2ePro\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_global_adding_template_category_id' => null,
            'auto_global_adding_template_category_secondary_id' => null,
            'auto_global_adding_template_store_category_id' => null,
            'auto_global_adding_template_store_category_secondary_id' => null,

            'auto_website_adding_mode' => \Ess\M2ePro\Model\Listing::ADDING_MODE_NONE,
            'auto_website_adding_add_not_visible' => \Ess\M2ePro\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_website_adding_template_category_id' => null,
            'auto_website_adding_template_category_secondary_id' => null,
            'auto_website_adding_template_store_category_id' => null,
            'auto_website_adding_template_store_category_secondary_id' => null,

            'auto_website_deleting_mode' => \Ess\M2ePro\Model\Listing::DELETING_MODE_NONE,

            'auto_advanced_filter_adding_mode' => \Ess\M2ePro\Model\Listing::ADDING_MODE_NONE,
            'auto_advanced_filter_adding_add_not_visible' => \Ess\M2ePro\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_advanced_filter_deleting_mode' => \Ess\M2ePro\Model\Listing::DELETING_MODE_NONE,
            'auto_advanced_filter_condition' => null,
            'auto_advanced_filter_adding_template_category_id' => null,
            'auto_advanced_filter_adding_template_category_secondary_id' => null,
            'auto_advanced_filter_adding_template_store_category_id' => null,
            'auto_advanced_filter_adding_template_store_category_secondary_id' => null,
        ];

        $listing->addData($data);
        $listing->getChildObject()->addData($data);
        $listing->save();

        foreach ($listing->getAutoCategoriesGroups(true) as $autoCategoryGroup) {
            /**@var \Ess\M2ePro\Model\Listing\Auto\Category\Group $autoCategoryGroup */
            $autoCategoryGroup->delete();
        }

        $advancedFilters = $this->autoAdvancedFilterRepository->getByListingId((int)$listing->getId());
        foreach ($advancedFilters as $filter) {
            $this->autoAdvancedFilterDelete->execute($filter);
        }
    }
}
