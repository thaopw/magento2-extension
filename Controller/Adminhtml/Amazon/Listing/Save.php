<?php

declare(strict_types=1);

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Listing;

use Ess\M2ePro\Block\Adminhtml\Amazon\Listing\Create\Selling\Form as CreateSellingForm;

class Save extends \Ess\M2ePro\Controller\Adminhtml\Amazon\Listing
{
    private \Ess\M2ePro\Model\Amazon\Listing\Repository $listingRepository;
    private \Ess\M2ePro\Model\Amazon\Listing\SnapshotBuilderFactory $snapshotBuilderFactory;
    private \Ess\M2ePro\Model\Amazon\Listing\DiffFactory $diffFactory;
    private \Ess\M2ePro\Model\Amazon\Listing\AffectedListingsProductsFactory $affectedListingsProductsFactory;
    private \Ess\M2ePro\Model\Amazon\Listing\ChangeProcessorFactory $changeProcessorFactory;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\Repository $shippingTemplateRepository;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\SnapshotBuilderFactory $shippingTemplateSnapshotBuilderFactory;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\DiffFactory $shippingTemplateDiffFactory;
    private \Ess\M2ePro\Model\Amazon\Template\Shipping\ChangeProcessorFactory $shippingTemplateChangeProcessorFactory;
    private \Ess\M2ePro\Model\Amazon\Listing\OfferImagesFormService $offerImagesService;
    private \Ess\M2ePro\Helper\Url $urlHelper;

    public function __construct(
        \Ess\M2ePro\Model\Amazon\Listing\Repository $listingRepository,
        \Ess\M2ePro\Model\Amazon\Listing\SnapshotBuilderFactory $snapshotBuilderFactory,
        \Ess\M2ePro\Model\Amazon\Listing\DiffFactory $diffFactory,
        \Ess\M2ePro\Model\Amazon\Listing\AffectedListingsProductsFactory $affectedListingsProductsFactory,
        \Ess\M2ePro\Model\Amazon\Listing\ChangeProcessorFactory $changeProcessorFactory,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\Repository $shippingTemplateRepository,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\SnapshotBuilderFactory $shippingTemplateSnapshotBuilderFactory,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\DiffFactory $shippingTemplateDiffFactory,
        \Ess\M2ePro\Model\Amazon\Template\Shipping\ChangeProcessorFactory $shippingTemplateChangeProcessorFactory,
        \Ess\M2ePro\Model\Amazon\Listing\OfferImagesFormService $offerImagesService,
        \Ess\M2ePro\Helper\Url $urlHelper,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Ess\M2ePro\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($amazonFactory, $context);
        $this->listingRepository = $listingRepository;
        $this->snapshotBuilderFactory = $snapshotBuilderFactory;
        $this->diffFactory = $diffFactory;
        $this->affectedListingsProductsFactory = $affectedListingsProductsFactory;
        $this->changeProcessorFactory = $changeProcessorFactory;
        $this->shippingTemplateRepository = $shippingTemplateRepository;
        $this->shippingTemplateSnapshotBuilderFactory = $shippingTemplateSnapshotBuilderFactory;
        $this->shippingTemplateDiffFactory = $shippingTemplateDiffFactory;
        $this->shippingTemplateChangeProcessorFactory = $shippingTemplateChangeProcessorFactory;
        $this->offerImagesService = $offerImagesService;
        $this->urlHelper = $urlHelper;
    }

    // ----------------------------------------

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ess_M2ePro::amazon_listings_m2epro');
    }

    // ----------------------------------------

    public function execute()
    {
        if (!$post = $this->getRequest()->getPost()) {
            $this->_redirect('*/amazon_listing/index');
        }

        $id = $this->getRequest()->getParam('id');
        $listing = $this->listingRepository->find((int)$id);

        if ($listing === null && $id) {
            $this->getMessageManager()
                 ->addError(__('Listing does not exist.'));

            return $this->_redirect('*/amazon_listing/index');
        }

        $snapshotBuilder = $this->snapshotBuilderFactory->create();
        $snapshotBuilder->setModel($listing);

        $oldData = $snapshotBuilder->getSnapshot();

        // Base prepare
        // ---------------------------------------
        $data = [];
        // ---------------------------------------

        // tab: settings
        // ---------------------------------------
        $keys = [
            'template_selling_format_id',
            'template_synchronization_id',
            'template_shipping_id',
        ];
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $data[$key] = (!empty($post[$key])) ? $post[$key] : null;
            }
        }
        // ---------------------------------------

        $listing->addData($data);
        $listing->getChildObject()->addData($data);
        $listing->save();

        $templateData = [];

        // tab: channel settings
        // ---------------------------------------
        $keys = [
            'account_id',
            'marketplace_id',

            'sku_mode',
            'sku_custom_attribute',
            'sku_modification_mode',
            'sku_modification_custom_value',
            'generate_sku_mode',

            'condition_mode',
            'condition_value',
            'condition_custom_attribute',

            'condition_note_mode',
            'condition_note_value',

            'gift_wrap_mode',
            'gift_wrap_attribute',

            'gift_message_mode',
            'gift_message_attribute',

            'handling_time_mode',
            'handling_time_value',
            'handling_time_custom_attribute',

            'restock_date_mode',
            'restock_date_value',
            'restock_date_custom_attribute',

            CreateSellingForm::FIELD_NAME_GENERAL_ID_ATTRIBUTE,
            CreateSellingForm::FIELD_NAME_WORLDWIDE_ID_ATTRIBUTE,
        ];
        foreach ($keys as $key) {
            if (isset($post[$key])) {
                $templateData[$key] = $post[$key];
            }
        }

        if ($templateData['restock_date_value'] === '') {
            $templateData['restock_date_value'] = \Ess\M2ePro\Helper\Date::createCurrentGmt()->format('Y-m-d H:i:s');
        } else {
            $timestamp = \Ess\M2ePro\Helper\Date::parseDateFromLocalFormat(
                $templateData['restock_date_value'],
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::SHORT
            );
            $templateData['restock_date_value'] = gmdate('Y-m-d H:i:s', $timestamp);
        }

        // Product identifiers
        // ---------------------------------------

        $templateData[\Ess\M2ePro\Model\ResourceModel\Amazon\Listing::COLUMN_GENERAL_ID_ATTRIBUTE] =
            !empty($templateData[CreateSellingForm::FIELD_NAME_GENERAL_ID_ATTRIBUTE])
                ? $templateData[CreateSellingForm::FIELD_NAME_GENERAL_ID_ATTRIBUTE]
                : null;

        $templateData[\Ess\M2ePro\Model\ResourceModel\Amazon\Listing::COLUMN_WORLDWIDE_ID_ATTRIBUTE] =
            !empty($templateData[CreateSellingForm::FIELD_NAME_WORLDWIDE_ID_ATTRIBUTE])
                ? $templateData[CreateSellingForm::FIELD_NAME_WORLDWIDE_ID_ATTRIBUTE]
                : null;

        $offerImages = [];
        if ($post['condition_value'] != \Ess\M2ePro\Model\Amazon\Listing::CONDITION_NEW) {
            $offerImages = $this->offerImagesService->prepareOfferImagesData($post['offer_images']);
        }

        $listing->getChildObject()->setOfferImages($offerImages);

        // ---------------------------------------

        $listing->addData($templateData);
        $listing->getChildObject()->addData($templateData);
        $listing->save();

        $snapshotBuilder = $this->snapshotBuilderFactory->create();
        $snapshotBuilder->setModel($listing);

        $newData = $snapshotBuilder->getSnapshot();

        $diff = $this->diffFactory->create();
        $diff->setNewSnapshot($newData);
        $diff->setOldSnapshot($oldData);

        $affectedListingsProducts = $this->affectedListingsProductsFactory->create();
        $affectedListingsProducts->setModel($listing);

        $affectedListingsProductsData = $affectedListingsProducts->getObjectsData(
            ['id', 'status'],
            ['only_physical_units' => true]
        );

        $changeProcessor = $this->changeProcessorFactory->create();
        $changeProcessor->process($diff, $affectedListingsProductsData);

        $this->processSellingFormatTemplateChange($oldData, $newData, $affectedListingsProductsData);
        $this->processSynchronizationTemplateChange($oldData, $newData, $affectedListingsProductsData);

        $affectedListingsProductsData = $affectedListingsProducts->getObjectsData(
            ['id', 'status'],
            ['only_physical_units' => true, 'template_shipping_id' => true]
        );
        $this->processShippingTemplateChange($oldData, $newData, $affectedListingsProductsData);

        $this->getMessageManager()->addSuccess(__('The Listing was saved.'));

        return $this->_redirect($this->urlHelper->getBackUrl('list', [], ['edit' => ['id' => $id]]));
    }

    // ----------------------------------------

    protected function processSellingFormatTemplateChange(
        array $oldData,
        array $newData,
        array $affectedListingsProductsData
    ) {
        if (
            empty($affectedListingsProductsData) ||
            empty($oldData['template_selling_format_id']) || empty($newData['template_selling_format_id'])
        ) {
            return;
        }

        $oldTemplate = $this->amazonFactory->getObjectLoaded(
            'Template_SellingFormat',
            $oldData['template_selling_format_id'],
            null,
            false
        );

        /** @var \Ess\M2ePro\Model\Amazon\Template\SellingFormat\SnapshotBuilder $snapshotBuilder */
        $snapshotBuilder = $this->modelFactory->getObject('Amazon_Template_SellingFormat_SnapshotBuilder');
        $snapshotBuilder->setModel($oldTemplate);
        $oldSnapshot = $snapshotBuilder->getSnapshot();

        $newTemplate = $this->amazonFactory->getObjectLoaded(
            'Template_SellingFormat',
            $newData['template_selling_format_id'],
            null,
            false
        );

        $snapshotBuilder = $this->modelFactory->getObject('Amazon_Template_SellingFormat_SnapshotBuilder');
        $snapshotBuilder->setModel($newTemplate);
        $newSnapshot = $snapshotBuilder->getSnapshot();

        /** @var \Ess\M2ePro\Model\Amazon\Template\SellingFormat\Diff $diff */
        $diff = $this->modelFactory->getObject('Amazon_Template_SellingFormat_Diff');
        $diff->setNewSnapshot($newSnapshot);
        $diff->setOldSnapshot($oldSnapshot);

        /** @var \Ess\M2ePro\Model\Amazon\Template\SellingFormat\ChangeProcessor $changeProcessor */
        $changeProcessor = $this->modelFactory->getObject('Amazon_Template_SellingFormat_ChangeProcessor');
        $changeProcessor->process($diff, $affectedListingsProductsData);
    }

    protected function processSynchronizationTemplateChange(
        array $oldData,
        array $newData,
        array $affectedListingsProductsData
    ) {
        if (
            empty($affectedListingsProductsData) ||
            empty($oldData['template_synchronization_id']) || empty($newData['template_synchronization_id'])
        ) {
            return;
        }

        $oldTemplate = $this->amazonFactory->getObjectLoaded(
            'Template_Synchronization',
            $oldData['template_synchronization_id'],
            null,
            false
        );

        /** @var \Ess\M2ePro\Model\Amazon\Template\Synchronization\SnapshotBuilder $snapshotBuilder */
        $snapshotBuilder = $this->modelFactory->getObject('Amazon_Template_Synchronization_SnapshotBuilder');
        $snapshotBuilder->setModel($oldTemplate);
        $oldSnapshot = $snapshotBuilder->getSnapshot();

        $newTemplate = $this->amazonFactory->getObjectLoaded(
            'Template_Synchronization',
            $newData['template_synchronization_id'],
            null,
            false
        );

        $snapshotBuilder = $this->modelFactory->getObject('Amazon_Template_Synchronization_SnapshotBuilder');
        $snapshotBuilder->setModel($newTemplate);
        $newSnapshot = $snapshotBuilder->getSnapshot();

        /** @var \Ess\M2ePro\Model\Amazon\Template\Synchronization\Diff $diff */
        $diff = $this->modelFactory->getObject('Amazon_Template_Synchronization_Diff');
        $diff->setNewSnapshot($newSnapshot);
        $diff->setOldSnapshot($oldSnapshot);

        /** @var \Ess\M2ePro\Model\Amazon\Template\Synchronization\ChangeProcessor $changeProcessor */
        $changeProcessor = $this->modelFactory->getObject('Amazon_Template_Synchronization_ChangeProcessor');
        $changeProcessor->process($diff, $affectedListingsProductsData);
    }

    protected function processShippingTemplateChange(
        array $oldData,
        array $newData,
        array $affectedListingsProductsData
    ) {
        if (
            empty($affectedListingsProductsData) ||
            empty($oldData['template_shipping_id']) &&
            empty($newData['template_shipping_id'])
        ) {
            return;
        }

        $oldTemplate = $this->shippingTemplateRepository->find((int)$oldData['template_shipping_id']);
        $oldSnapshot = [];
        if ($oldTemplate !== null) {
            $snapshotBuilder = $this->shippingTemplateSnapshotBuilderFactory->create();
            $snapshotBuilder->setModel($oldTemplate);
            $oldSnapshot = $snapshotBuilder->getSnapshot();
        }

        $newTemplate = $this->shippingTemplateRepository->find((int)$newData['template_shipping_id']);
        $newSnapshot = [];
        if ($newTemplate !== null) {
            $snapshotBuilder = $this->shippingTemplateSnapshotBuilderFactory->create();
            $snapshotBuilder->setModel($newTemplate);
            $newSnapshot = $snapshotBuilder->getSnapshot();
        }

        $diff = $this->shippingTemplateDiffFactory->create();
        $diff->setNewSnapshot($newSnapshot);
        $diff->setOldSnapshot($oldSnapshot);

        $changeProcessor = $this->shippingTemplateChangeProcessorFactory->create();
        $changeProcessor->process($diff, $affectedListingsProductsData);
    }
}
