<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Template\Shipping;

class Delete extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Template
{
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\Repository $templateShippingRepository;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\IsLocked $isLocked;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Template\Shipping\Repository $templateShippingRepository,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\IsLocked $isLocked,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($amazonFactory, $context);
        $this->templateShippingRepository = $templateShippingRepository;
        $this->isLocked = $isLocked;
    }

    public function execute()
    {
        $idsToDelete = $this->getRequestIds();
        if (count($idsToDelete) == 0) {
            $this
                ->getMessageManager()
                ->addError(__('Please select Item(s) to remove.'));

            return $this->_redirect('*/amazon_template/index');
        }

        $deleted = $locked = 0;

        $objects = $this->templateShippingRepository->getByIds($idsToDelete);
        foreach ($objects as $template) {
            if ($this->isLocked->execute($template->getId())) {
                $locked++;

                continue;
            }

            $this->templateShippingRepository->delete($template);
            $deleted++;
        }

        if ($deleted) {
            $this
                ->getMessageManager()
                ->addSuccess(
                    __(
                        '%deleted_count record(s) were deleted.',
                        ['deleted_count' => $deleted]
                    )
                );
        }

        if ($locked) {
            $this
                ->getMessageManager()
                ->addError(
                    __(
                        '%locked_count record(s) are used in Listing(s). Policy must not be in use to be deleted.',
                        ['locked_count' => $locked]
                    )
                );
        }

        return $this->_redirect('*/amazon_template/index');
    }
}
