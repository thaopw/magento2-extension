<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method \Ess\M2ePro\Model\ResourceModel\Ebay\Template\ReturnPolicy getResource()
 */

namespace Ess\M2ePro\Model\Ebay\Template;

use Ess\M2ePro\Model\ActiveRecord\Factory;

/**
 * Class \Ess\M2ePro\Model\Ebay\Template\ReturnPolicy
 */
class ReturnPolicy extends \Ess\M2ePro\Model\ActiveRecord\Component\AbstractModel
{
    public const RETURNS_ACCEPTED = 'ReturnsAccepted';
    public const RETURNS_NOT_ACCEPTED = 'ReturnsNotAccepted';

    /**
     * @var \Ess\M2ePro\Model\Marketplace
     */
    private $marketplaceModel = null;

    protected $ebayFactory;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ebayFactory = $ebayFactory;

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

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Ess\M2ePro\Model\ResourceModel\Ebay\Template\ReturnPolicy::class);
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return \Ess\M2ePro\Model\Ebay\Template\Manager::TEMPLATE_RETURN_POLICY;
    }

    //########################################

    /**
     * @return bool
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)$this->activeRecordFactory->getObject('Ebay\Listing')
                                               ->getCollection()
                                               ->addFieldToFilter('template_return_policy_id', $this->getId())
                                               ->getSize() ||
            (bool)$this->activeRecordFactory->getObject('Ebay_Listing_Product')
                                            ->getCollection()
                                            ->addFieldToFilter(
                                                'template_return_policy_mode',
                                                \Ess\M2ePro\Model\Ebay\Template\Manager::MODE_TEMPLATE
                                            )
                                            ->addFieldToFilter('template_return_policy_id', $this->getId())
                                            ->getSize();
    }

    //########################################

    public function save()
    {
        $this->getHelper('Data_Cache_Permanent')->removeTagValues('ebay_template_returnpolicy');

        return parent::save();
    }

    //########################################

    public function delete()
    {
        $temp = parent::delete();
        $temp && $this->marketplaceModel = null;

        $this->getHelper('Data_Cache_Permanent')->removeTagValues('ebay_template_returnpolicy');

        return $temp;
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

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * @return bool
     */
    public function isCustomTemplate()
    {
        return (bool)$this->getData('is_custom_template');
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData('marketplace_id');
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

    public function getAccepted()
    {
        return $this->getData('accepted');
    }

    public function getOption()
    {
        return $this->getData('option');
    }

    public function getWithin()
    {
        return $this->getData('within');
    }

    public function getShippingCost()
    {
        return $this->getData('shipping_cost');
    }

    // ---------------------------------------

    public function getInternationalAccepted()
    {
        return $this->getData('international_accepted');
    }

    public function getInternationalOption()
    {
        return $this->getData('international_option');
    }

    public function getInternationalWithin()
    {
        return $this->getData('international_within');
    }

    public function getInternationalShippingCost()
    {
        return $this->getData('international_shipping_cost');
    }

    // ---------------------------------------

    public function getDescription()
    {
        return $this->getData('description');
    }

    //########################################

    public function isCacheEnabled()
    {
        return true;
    }

    //########################################
}
