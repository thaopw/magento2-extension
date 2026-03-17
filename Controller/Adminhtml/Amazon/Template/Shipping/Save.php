<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Template\Shipping;

class Save extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Template
{
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\Repository $templateShippingRepository;
    private \Ess\M2ePro\Model\Amazon\Template\ShippingFactory $templateShippingFactory;
    private \Ess\M2ePro\Helper\Url $urlHelper;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\SnapshotBuilderFactory $snapshotBuilderFactory;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\BuilderFactory $builderFactory;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\DiffFactory $diffFactory;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\AffectedListingsProductsFactory $affectedListingsProductsFactory;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\ChangeProcessorFactory $changeProcessorFactory;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Template\Shipping\Repository $templateShippingRepository,
        \Ess\M2ePro\Model\Amazon\Template\ShippingFactory $templateShippingFactory,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\SnapshotBuilderFactory $snapshotBuilderFactory,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\BuilderFactory $builderFactory,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\DiffFactory $diffFactory,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\AffectedListingsProductsFactory $affectedListingsProductsFactory,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\ChangeProcessorFactory $changeProcessorFactory,
        \Ess\M2ePro\Helper\Url $urlHelper,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($amazonFactory, $context);
        $this->templateShippingRepository = $templateShippingRepository;
        $this->templateShippingFactory = $templateShippingFactory;
        $this->urlHelper = $urlHelper;
        $this->snapshotBuilderFactory = $snapshotBuilderFactory;
        $this->builderFactory = $builderFactory;
        $this->diffFactory = $diffFactory;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
        $this->changeProcessorFactory = $changeProcessorFactory;
    }

    public function execute()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return $this->_redirect('*/amazon_template/index');
        }

        $id = $this->getRequest()->getParam('id');

        $model = $this->templateShippingRepository->find((int)$id);

        if ($model === null) {
            $model = $this->templateShippingFactory->create();
        }

        $oldData = [];

        if (!empty($id)) {
            $snapshotBuilder = $this->snapshotBuilderFactory->create();
            $snapshotBuilder->setModel($model);
            $oldData = $snapshotBuilder->getSnapshot();
        }

        $builder = $this->builderFactory->create();
        $builder->build($model, $post->toArray());

        $snapshotBuilder = $this->snapshotBuilderFactory->create();
        $snapshotBuilder->setModel($model);
        $newData = $snapshotBuilder->getSnapshot();

        $diff = $this->diffFactory->create();
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
        $affectedListingsProducts->setModel($model);

        $changeProcessor = $this->changeProcessorFactory->create();
        $changeProcessor->process(
            $diff,
            $affectedListingsProducts
                ->getObjectsData(['id', 'status'], ['only_physical_units' => true])
        );

        $this->getMessageManager()->addSuccess(__('Policy was saved'));

        if ($this->isAjax()) {
            $this->setJsonContent([
                'status' => true,
                'url' =>  $this->urlHelper->getBackUrl('*/amazon_template/index', [], [
                    'edit' => [
                        'id' => $model->getId(),
                    ],
                ])
            ]);

            return $this->getResult();
        }

        return $this->_redirect(
            $this->urlHelper->getBackUrl('*/amazon_template/index', [], [
                'edit' => [
                    'id' => $model->getId(),
                    'close_on_save' => $this->getRequest()->getParam('close_on_save'),
                ],
            ])
        );
    }
}
