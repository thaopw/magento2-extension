<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Ebay\Template\Shipping;

/**
 * Class \Ess\M2ePro\Model\Ebay\Template\Shipping\Source
 */
class Source extends \Ess\M2ePro\Model\AbstractModel
{
    /**
     * @var \Ess\M2ePro\Model\Magento\Product $magentoProduct
     */
    private $magentoProduct;

    /**
     * @var \Ess\M2ePro\Model\Ebay\Template\Shipping $shippingTemplateModel
     */
    private $shippingTemplateModel;

    //########################################

    /**
     * @param \Ess\M2ePro\Model\Magento\Product $magentoProduct
     *
     * @return $this
     */
    public function setMagentoProduct(\Ess\M2ePro\Model\Magento\Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;

        return $this;
    }

    /**
     * @return \Ess\M2ePro\Model\Magento\Product
     */
    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ---------------------------------------

    /**
     * @param \Ess\M2ePro\Model\Ebay\Template\Shipping $instance
     *
     * @return $this
     */
    public function setShippingTemplate(\Ess\M2ePro\Model\Ebay\Template\Shipping $instance)
    {
        $this->shippingTemplateModel = $instance;

        return $this;
    }

    /**
     * @return \Ess\M2ePro\Model\Ebay\Template\Shipping
     */
    public function getShippingTemplate()
    {
        return $this->shippingTemplateModel;
    }

    //########################################

    /**
     * @return string
     */
    public function getCountry()
    {
        $src = $this->getShippingTemplate()->getCountrySource();

        if ($src['mode'] == \Ess\M2ePro\Model\Ebay\Template\Shipping::COUNTRY_MODE_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getPostalCode()
    {
        $src = $this->getShippingTemplate()->getPostalCodeSource();

        if ($src['mode'] == \Ess\M2ePro\Model\Ebay\Template\Shipping::ADDRESS_MODE_NONE) {
            return '';
        }

        if ($src['mode'] == \Ess\M2ePro\Model\Ebay\Template\Shipping::ADDRESS_MODE_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getAddress()
    {
        $src = $this->getShippingTemplate()->getAddressSource();

        if ($src['mode'] == \Ess\M2ePro\Model\Ebay\Template\Shipping::ADDRESS_MODE_NONE) {
            return '';
        }

        if ($src['mode'] == \Ess\M2ePro\Model\Ebay\Template\Shipping::ADDRESS_MODE_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getDispatchTime()
    {
        $src = $this->getShippingTemplate()->getDispatchTimeSource();

        if ($src['mode'] == \Ess\M2ePro\Model\Ebay\Template\Shipping::DISPATCH_TIME_MODE_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    public function hasAnyShippingServiceAdditionalCost(int $storeId): bool
    {
        $services = $this->getShippingTemplate()
                         ->getServices(true);

        foreach ($services as $service) {
            if (
                $service->getSource($this->getMagentoProduct())
                        ->getCostAdditional($storeId) > 0
            ) {
                return true;
            }
        }

        return false;
    }
}
