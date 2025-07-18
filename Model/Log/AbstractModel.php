<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Log;

use Ess\M2ePro\Model\Exception;

/**
 * Class \Ess\M2ePro\Model\Log\AbstractModel
 */
abstract class AbstractModel extends \Ess\M2ePro\Model\ActiveRecord\AbstractModel
{
    /**
     * The order of the values of log types' constants is important.
     * @see \Ess\M2ePro\Block\Adminhtml\Log\Grid\LastActions::$actionsSortOrder
     * @see \Ess\M2ePro\Block\Adminhtml\Log\Listing\Product\View\Grouped\AbstractGrid::_prepareCollection()
     */
    public const TYPE_INFO = 1;
    public const TYPE_SUCCESS = 2;
    public const TYPE_WARNING = 3;
    public const TYPE_ERROR = 4;

    protected $componentMode = null;

    protected $parentFactory;
    protected $resourceConnection;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Factory $parentFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->parentFactory = $parentFactory;
        $this->resourceConnection = $resourceConnection;
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

    public function setComponentMode($mode)
    {
        $mode = strtolower((string)$mode);
        $mode && $this->componentMode = $mode;

        return $this;
    }

    public function getComponentMode()
    {
        return $this->componentMode;
    }

    //########################################

    public function getActionsTitles()
    {
        $className = $this->getHelper('Client')->getClassName($this);

        return $this->getHelper('Module\Log')->getActionsTitlesByClass($className);
    }

    //########################################
}
