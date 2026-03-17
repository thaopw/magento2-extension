<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Template\Shipping;

class Edit extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Template
{
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\Repository $templateShippingRepository;
    private \Magento\Framework\Escaper $escaper;
    private \Ess\M2ePro\Model\Amazon\Template\ShippingFactory $templateShippingFactory;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Template\Shipping\Repository $templateShippingRepository,
        \Ess\M2ePro\Model\Amazon\Template\ShippingFactory $templateShippingFactory,
        \Magento\Framework\Escaper $escaper,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        $this->templateShippingRepository = $templateShippingRepository;
        parent::__construct($amazonFactory, $context);
        $this->escaper = $escaper;
        $this->templateShippingFactory = $templateShippingFactory;
    }

    public function execute()
    {
        try {
            $shippingTemplate = $this->getShippingTemplate();
        } catch (\Throwable $exception) {
            $this->getMessageManager()->addError($exception->getMessage());

            return $this->_redirect('*/amazon_template/index');
        }

        if ($shippingTemplate->isObjectNew()) {
            $headerText = __('Add Shipping Policy');
        } else {
            $headerText = __('Edit Shipping Policy "%template_title"', [
                'template_title' => $this->escaper->escapeHtml($shippingTemplate->getTitle()),
            ]);
        }

        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Policies'));
        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Shipping Template Policies'));
        $this->getResultPage()->getConfig()->getTitle()->prepend($headerText);

        $this->addContent(
            $this->getLayout()
                 ->createBlock(
                     \Ess\M2ePro\Block\Adminhtml\Amazon\Template\Shipping\Edit::class,
                     '',
                     ['shippingTemplate' => $shippingTemplate]
                 )
        );

        $this->setPageHelpLink('help/m2/amazon-integration/configurations/policies/shipping-template-policies');

        return $this->getResultPage();
    }

    /**
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    private function getShippingTemplate(): \Ess\M2ePro\Model\Amazon\Template\Shipping
    {
        $id = $this->getRequest()->getParam('id');
        if (empty($id)) {
            return $this->templateShippingFactory->create();
        }

        $template = $this->templateShippingRepository->find((int)$id);
        if ($template === null) {
            throw new \Ess\M2ePro\Model\Exception\Logic((string)__('Policy does not exist'));
        }

        return $template;
    }
}
