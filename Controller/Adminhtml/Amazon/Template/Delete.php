<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Template;

class Delete extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Template
{
    private \Magento\Framework\Controller\Result\ForwardFactory $forwardFactory;

    public function __construct(
        \Magento\Framework\Controller\Result\ForwardFactory $forwardFactory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($amazonFactory, $context);
        $this->forwardFactory = $forwardFactory;
    }

    //########################################

    public function execute()
    {
        $type = $this->getRequest()->getParam('type');

        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->forwardFactory->create();
        $forward = $resultForward->setParams($this->getRequest()->getParams());

        if ($type === \Ess\M2ePro\Block\Adminhtml\Amazon\Template\Grid::TEMPLATE_SHIPPING) {
            return $forward
                ->setController('amazon_template_shipping')
                ->forward('delete');
        }

        if ($type === \Ess\M2ePro\Block\Adminhtml\Amazon\Template\Grid::TEMPLATE_PRODUCT_TAX_CODE) {
            return $forward
                ->setController('amazon_template_productTaxCode')
                ->forward('delete');
        }

        if ($type === \Ess\M2ePro\Block\Adminhtml\Amazon\Template\Grid::TEMPLATE_SELLING_FORMAT) {
            return $forward
                ->setController('amazon_template_sellingFormat')
                ->forward('delete');
        }

        if ($type === \Ess\M2ePro\Block\Adminhtml\Amazon\Template\Grid::TEMPLATE_SYNCHRONIZATION) {
            return $forward
                ->setController('amazon_template_synchronization')
                ->forward('delete');
        }

        return $this->getResult();
    }
}
