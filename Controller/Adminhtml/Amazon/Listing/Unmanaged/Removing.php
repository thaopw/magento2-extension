<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Listing\Unmanaged;

class Removing extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Listing
{
    private \Ess\M2ePro\Model\Amazon\Listing\Other\Remover $listingOtherProductRemover;
    private \Ess\M2ePro\Helper\Module\Exception $exceptionHelper;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Listing\Other\Remover $listingOtherProductRemover,
        \Ess\M2ePro\Helper\Module\Exception $exceptionHelper,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($amazonFactory, $context);
        $this->listingOtherProductRemover = $listingOtherProductRemover;
        $this->exceptionHelper = $exceptionHelper;
    }

    public function execute()
    {
        $productIds = $this->getProductIdsFromRequest();

        if (empty($productIds)) {
            $this->setAjaxContent('0', false);

            return $this->getResult();
        }

        try {
            $this->listingOtherProductRemover->execute($productIds);
        } catch (\Throwable $exception) {
            $this->exceptionHelper->process($exception);
            $this->setAjaxContent('removing_error', false);

            return $this->getResult();
        }

        $this->setAjaxContent('1', false);

        return $this->getResult();
    }

    /**
     * @return int[]
     */
    public function getProductIdsFromRequest(): array
    {
        $productIds = $this->getRequest()->getParam('product_ids');
        if (empty($productIds)) {
            return [];
        }

        return array_map('intval', explode(',', $productIds));
    }
}
