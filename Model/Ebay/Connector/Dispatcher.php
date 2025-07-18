<?php

namespace Ess\M2ePro\Model\Ebay\Connector;

class Dispatcher extends \Ess\M2ePro\Model\AbstractModel
{
    private \Magento\Framework\Code\NameBuilder $nameBuilder;
    private \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory;
    /** @var \Ess\M2ePro\Model\Ebay\Connector\Protocol */
    private Protocol $protocol;
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        \Ess\M2ePro\Model\Ebay\Connector\Protocol $protocol,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Ebay\Factory $ebayFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    ) {
        parent::__construct($helperFactory, $modelFactory);
        $this->nameBuilder = $nameBuilder;
        $this->ebayFactory = $ebayFactory;
        $this->protocol = $protocol;
        $this->objectManager = $objectManager;
    }

    // ----------------------------------------

    /**
     * @deprecated
     * @see self::getConnectorByClass()
     */
    public function getConnector($entity, $type, $name, array $params = [], $marketplace = null, $account = null)
    {
        $classParts = ['Ebay\Connector'];

        !empty($entity) && $classParts[] = $entity;
        !empty($type) && $classParts[] = $type;
        !empty($name) && $classParts[] = $name;

        $className = $this->nameBuilder->buildClassName($classParts);

        if (is_int($marketplace) || is_string($marketplace)) {
            $marketplace = $this->ebayFactory->getCachedObjectLoaded(
                'Marketplace',
                (int)$marketplace
            );
        }

        if (is_int($account) || is_string($account)) {
            $account = $this->ebayFactory->getCachedObjectLoaded(
                'Account',
                (int)$account
            );
        }

        /** @var \Ess\M2ePro\Model\Connector\Command\AbstractModel $connectorObject */
        $connectorObject = $this->modelFactory->getObject($className, [
            'params' => $params,
            'marketplace' => $marketplace,
            'account' => $account,
        ]);
        $connectorObject->setProtocol($this->protocol);

        return $connectorObject;
    }

    /**
     * @deprecated
     * @see self::getConnectorByClass()
     */
    public function getCustomConnector($modelName, array $params = [], $marketplace = null, $account = null)
    {
        if (is_int($marketplace) || is_string($marketplace)) {
            $marketplace = $this->ebayFactory->getCachedObjectLoaded('Marketplace', (int)$marketplace);
        }

        if (is_int($account) || is_string($account)) {
            $account = $this->ebayFactory->getCachedObjectLoaded('Account', (int)$account);
        }

        /** @var \Ess\M2ePro\Model\Connector\Command\AbstractModel $connectorObject */
        $connectorObject = $this->modelFactory->getObject($modelName, [
            'params' => $params,
            'marketplace' => $marketplace,
            'account' => $account,
        ]);
        $connectorObject->setProtocol($this->protocol);

        return $connectorObject;
    }

    /**
     * @psalm-template ConnectorClass
     *
     * @param class-string<ConnectorClass> $className
     * @param array $params
     * @param int|string|null $marketplace
     * @param int|string|null $account
     *
     * @return ConnectorClass
     */
    public function getConnectorByClass(
        string $className,
        array $params = [],
        $marketplace = null,
        $account = null
    ): \Ess\M2ePro\Model\Connector\Command\AbstractModel {
        if (is_int($marketplace) || is_string($marketplace)) {
            $marketplace = $this->ebayFactory->getCachedObjectLoaded(
                'Marketplace',
                (int)$marketplace
            );
        }

        if (is_int($account) || is_string($account)) {
            $account = $this->ebayFactory->getCachedObjectLoaded(
                'Account',
                (int)$account
            );
        }

        /** @var \Ess\M2ePro\Model\Connector\Command\AbstractModel $connectorObject */
        $connectorObject = $this->objectManager->create(
            $className,
            [
                'params' => $params,
                'marketplace' => $marketplace,
                'account' => $account,
            ]
        );
        $connectorObject->setProtocol($this->protocol);

        return $connectorObject;
    }

    public function getVirtualConnector(
        $entity,
        $type,
        $name,
        array $requestData = [],
        $responseDataKey = null,
        $marketplace = null,
        $account = null,
        $requestTimeOut = null
    ) {
        return $this->getCustomVirtualConnector(
            'Connector_Command_RealTime_Virtual',
            $entity,
            $type,
            $name,
            $requestData,
            $responseDataKey,
            $marketplace,
            $account,
            $requestTimeOut
        );
    }

    public function getCustomVirtualConnector(
        $modelName,
        $entity,
        $type,
        $name,
        array $requestData = [],
        $responseDataKey = null,
        $marketplace = null,
        $account = null,
        $requestTimeOut = null
    ) {
        /** @var \Ess\M2ePro\Model\Connector\Command\RealTime\Virtual $virtualConnector */
        $virtualConnector = $this->modelFactory->getObject($modelName);
        $virtualConnector->setProtocol($this->protocol);
        $virtualConnector->setCommand([$entity, $type, $name]);
        $virtualConnector->setResponseDataKey($responseDataKey);
        $requestTimeOut !== null && $virtualConnector->setRequestTimeOut($requestTimeOut);

        if (is_int($marketplace) || is_string($marketplace)) {
            $marketplace = $this->ebayFactory->getCachedObjectLoaded(
                'Marketplace',
                (int)$marketplace
            );
        }

        if (is_int($account) || is_string($account)) {
            $account = $this->ebayFactory->getCachedObjectLoaded(
                'Account',
                (int)$account
            );
        }

        if ($marketplace instanceof \Ess\M2ePro\Model\Marketplace) {
            $requestData['marketplace'] = $marketplace->getNativeId();
        }

        if ($account instanceof \Ess\M2ePro\Model\Account) {
            $requestData['account'] = $account->getChildObject()->getServerHash();
        }

        $virtualConnector->setRequestData($requestData);

        return $virtualConnector;
    }

    // ----------------------------------------

    public function process(\Ess\M2ePro\Model\Connector\Command\AbstractModel $connector)
    {
        $connector->process();
    }
}
