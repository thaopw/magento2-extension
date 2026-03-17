<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Template\Shipping;

class Index extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Template
{
    public function execute()
    {
        return $this->_redirect('*/amazon_template/index');
    }
}
