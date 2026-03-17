<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Product\Template\Shipping;

class Unassign extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Product\Template\Shipping
{
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\SetForProducts $setShippingTemplateForProducts;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Template\Shipping\SetForProducts $setShippingTemplateForProducts,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($amazonFactory, $context);
        $this->setShippingTemplateForProducts = $setShippingTemplateForProducts;
    }

    public function execute()
    {
        $productsIds = $this->getRequest()->getParam('products_ids');

        if (empty($productsIds)) {
            $this->setAjaxContent('You should provide correct parameters.', false);

            return $this->getResult();
        }

        if (!is_array($productsIds)) {
            $productsIds = explode(',', $productsIds);
        }

        $messages = [];
        $productsIdsLocked = $this->filterLockedProducts($productsIds);

        if (count($productsIdsLocked) < count($productsIds)) {
            $messages[] = [
                'type' => 'warning',
                'text' => '<p>' . $this->__(
                    'Shipping Policy cannot be unassigned from some Products
                         because the Products are in Action'
                ) . '</p>',
            ];
        }

        if (!empty($productsIdsLocked)) {
            $messages[] = [
                'type' => 'success',
                'text' => $this->__('Shipping Policy was unassigned.'),
            ];

            $this->setShippingTemplateForProducts->execute($productsIdsLocked, null);
            $this->runProcessorForParents($productsIdsLocked);
        }

        $this->setJsonContent(['messages' => $messages]);

        return $this->getResult();
    }
}
