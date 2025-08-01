<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Connector\Orders\Refund;

abstract class ItemsRequester extends \Ess\M2ePro\Model\Amazon\Connector\Command\Pending\Requester
{
    /** @var \Ess\M2ePro\Model\ActiveRecord\Factory  */
    protected $activeRecordFactory;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ActiveRecord\Factory $activeRecordFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory,
        ?\Ess\M2ePro\Model\Account $account = null,
        array $params = []
    ) {
        $this->activeRecordFactory = $activeRecordFactory;
        parent::__construct(
            $helperFactory,
            $modelFactory,
            $account,
            $params
        );
    }

    //########################################

    public function getCommand()
    {
        return ['orders', 'refund', 'entities'];
    }

    //########################################

    public function process()
    {
        $this->eventBeforeExecuting();
        $this->getProcessingRunner()->start();
    }

    //########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Amazon_Connector_Orders_ProcessingRunner';
    }

    protected function getProcessingParams()
    {
        return array_merge(
            parent::getProcessingParams(),
            [
                'request_data' => $this->getRequestData(),
                'order_id' => $this->params['order']['order_id'],
                'change_id' => $this->params['order']['change_id'],
                'action_type' => \Ess\M2ePro\Model\Amazon\Order\Action\Processing::ACTION_TYPE_REFUND,
                'lock_name' => 'refund_order',
                'start_date' => $this->getHelper('Data')->getCurrentGmtDate(),
            ]
        );
    }

    //########################################

    public function getRequestData()
    {
        return [
            'order_id' => $this->params['order']['amazon_order_id'],
            'currency' => $this->params['order']['currency'],
            'type' => 'Refund',
            'adjustment_fee' => $this->params['order']['adjustment_fee'] ?? 0,
            'adjustment_refund' =>  $this->params['order']['adjustment_refund'] ?? 0,
            'shipping_refund' =>  $this->params['order']['shipping_refund'] ?? 0,
            'shipping_tax_refund' =>  $this->params['order']['shipping_tax_refund'] ?? 0,
            'items' => $this->params['order']['items'],
        ];
    }

    //########################################
}
