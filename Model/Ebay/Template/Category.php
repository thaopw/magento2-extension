<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Ebay\Template;

/**
 * @method \Ess\M2ePro\Model\ResourceModel\Ebay\Template\Category getResource()
 */
class Category extends \Ess\M2ePro\Model\ActiveRecord\Component\AbstractModel implements CategoryInterface
{
    public const CATEGORY_MODE_NONE = 0;
    public const CATEGORY_MODE_EBAY = 1;
    public const CATEGORY_MODE_ATTRIBUTE = 2;

    protected $ebayFactory;

    /** @var \Ess\M2ePro\Model\Marketplace */
    private $marketplaceModel = null;
    /** @var \Ess\M2ePro\Model\Ebay\Template\Category\Source[] */
    private $categorySourceModels = [];
    /** @var \Ess\M2ePro\Helper\Component\Ebay\Category */
    private $componentEbayCategory;

    public function __construct(
        \Ess\M2ePro\Helper\Component\Ebay\Category $componentEbayCategory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
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

        $this->ebayFactory = $ebayFactory;
        $this->componentEbayCategory = $componentEbayCategory;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Ess\M2ePro\Model\ResourceModel\Ebay\Template\Category::class);
    }

    //########################################

    public function loadByCategoryValue($value, $mode, $marketplaceId, $isCustomTemplate = null)
    {
        return $this->getResource()->loadByCategoryValue($this, $value, $mode, $marketplaceId, $isCustomTemplate);
    }

    //########################################

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        /** @var \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\AbstractModel $collection */
        $collection = $this->ebayFactory->getObject('Listing_Product')->getCollection();
        $collection->getSelect()->where(
            'template_category_id = ? OR template_category_secondary_id = ?',
            $this->getId()
        );

        if ((bool)$collection->getSize()) {
            return true;
        }

        /** @var \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\AbstractModel $collection */
        $collection = $this->ebayFactory->getObject('Listing')->getCollection();
        $collection->getSelect()->where(
            'auto_global_adding_template_category_id = ? OR
             auto_global_adding_template_category_secondary_id = ? OR
             auto_website_adding_template_category_id = ? OR
             auto_website_adding_template_category_secondary_id = ?',
            $this->getId()
        );

        if ((bool)$collection->getSize()) {
            return true;
        }

        /** @var \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\AbstractModel $collection */
        $collection = $this->activeRecordFactory->getObject('Ebay_Listing_Auto_Category_Group')->getCollection();
        $collection->getSelect()->where(
            'adding_template_category_id = ? OR adding_template_category_secondary_id = ?',
            $this->getId()
        );

        if ((bool)$collection->getSize()) {
            return true;
        }

        return false;
    }

    public function save()
    {
        $this->getHelper('Data_Cache_Permanent')->removeTagValues('ebay_template_category');

        return parent::save();
    }

    //########################################

    public function delete()
    {
        if ($this->isLocked()) {
            return false;
        }

        $specifics = $this->getSpecifics(true);
        foreach ($specifics as $specific) {
            $specific->delete();
        }

        $this->marketplaceModel = null;
        $this->categorySourceModels = [];

        $this->getHelper('Data_Cache_Permanent')->removeTagValues('ebay_template_category');

        return parent::delete();
    }

    //########################################

    /**
     * @return \Ess\M2ePro\Model\Marketplace
     */
    public function getMarketplace()
    {
        if ($this->marketplaceModel === null) {
            $this->marketplaceModel = $this->ebayFactory->getCachedObjectLoaded(
                'Marketplace',
                $this->getMarketplaceId()
            );
        }

        return $this->marketplaceModel;
    }

    /**
     * @param \Ess\M2ePro\Model\Marketplace $instance
     */
    public function setMarketplace(\Ess\M2ePro\Model\Marketplace $instance)
    {
        $this->marketplaceModel = $instance;
    }

    // ---------------------------------------

    /**
     * @param \Ess\M2ePro\Model\Magento\Product $magentoProduct
     *
     * @return \Ess\M2ePro\Model\Ebay\Template\Category\Source
     */
    public function getSource(\Ess\M2ePro\Model\Magento\Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->categorySourceModels[$productId])) {
            return $this->categorySourceModels[$productId];
        }

        $this->categorySourceModels[$productId] = $this->modelFactory->getObject('Ebay_Template_Category_Source');
        $this->categorySourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->categorySourceModels[$productId]->setCategoryTemplate($this);

        return $this->categorySourceModels[$productId];
    }

    //########################################

    /**
     * @param bool $asObjects
     * @param array $filters
     *
     * @return array|\Ess\M2ePro\Model\Ebay\Template\Category\Specific[]
     */
    public function getSpecifics($asObjects = false, array $filters = [])
    {
        $specifics = $this->getRelatedSimpleItems(
            'Ebay_Template_Category_Specific',
            'template_category_id',
            $asObjects,
            $filters
        );

        if ($asObjects) {
            /** @var \Ess\M2ePro\Model\Ebay\Template\Category\Specific $specific */
            foreach ($specifics as $specific) {
                $specific->setCategoryTemplate($this);
            }
        }

        return $specifics;
    }

    //########################################

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return (int)$this->getData('category_id');
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
    }

    public function getIsCustomTemplate(): int
    {
        return (int)$this->getData('is_custom_template');
    }

    public function getCategoryPath(): string
    {
        return $this->getData(\Ess\M2ePro\Model\ResourceModel\Ebay\Template\Category::COLUMN_CATEGORY_PATH);
    }

    //---------------------------------------

    public function getCategoryMode(): int
    {
        return (int)$this->getData('category_mode');
    }

    public function isCategoryModeNone()
    {
        return $this->getCategoryMode() === self::CATEGORY_MODE_NONE;
    }

    public function isCategoryModeEbay()
    {
        return $this->getCategoryMode() === self::CATEGORY_MODE_EBAY;
    }

    public function isCategoryModeAttribute()
    {
        return $this->getCategoryMode() === self::CATEGORY_MODE_ATTRIBUTE;
    }

    //---------------------------------------

    /**
     * @return string|null
     */
    public function getCategoryAttribute()
    {
        return $this->getData('category_attribute');
    }

    // ---------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    //########################################

    /**
     * @return int|string
     */
    public function getCategoryValue()
    {
        return $this->isCategoryModeEbay() ? $this->getCategoryId() : $this->getCategoryAttribute();
    }

    /**
     * @return array
     */
    public function getCategorySource()
    {
        return [
            'mode' => $this->getData('category_mode'),
            'value' => $this->getData('category_id'),
            'path' => $this->getData('category_path'),
            'attribute' => $this->getData('category_attribute'),
        ];
    }

    /**
     * @return array
     */
    public function getCategoryAttributes()
    {
        $usedAttributes = [];

        $categoryMainSrc = $this->getCategorySource();

        if ($categoryMainSrc['mode'] == self::CATEGORY_MODE_ATTRIBUTE) {
            $usedAttributes[] = $categoryMainSrc['attribute'];
        }

        foreach ($this->getSpecifics(true) as $specificModel) {
            $usedAttributes = array_merge($usedAttributes, $specificModel->getValueAttributes());
        }

        return array_values(array_unique($usedAttributes));
    }

    //########################################

    public function getCacheGroupTags()
    {
        return array_merge(parent::getCacheGroupTags(), ['template']);
    }

    //########################################

    public function isCacheEnabled()
    {
        return true;
    }

    //########################################
}
