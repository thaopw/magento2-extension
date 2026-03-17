<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Template\Shipping;

class NewAction extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Template
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
