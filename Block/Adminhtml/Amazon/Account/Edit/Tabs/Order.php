<?php

namespace Ess\M2ePro\Block\Adminhtml\Amazon\Account\Edit\Tabs;

use Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractForm;
use Ess\M2ePro\Model\Amazon\Account;
use Magento\Framework\Data\Form\Element\Fieldset;

class Order extends AbstractForm
{
    /** @var \Magento\Sales\Model\Order\Config */
    private $orderConfig;
    /** @var \Magento\Customer\Model\Group */
    private $customerGroup;
    /** @var \Magento\Tax\Model\ClassModel */
    private $taxClass;
    /** @var \Ess\M2ePro\Helper\Module\Support */
    private $supportHelper;
    /** @var \Ess\M2ePro\Helper\Data\GlobalData */
    private $globalDataHelper;
    /** @var \Ess\M2ePro\Helper\Magento\Store\Website */
    private $storeWebsiteHelper;
    /** @var \Ess\M2ePro\Model\Amazon\Account\Builder */
    private $accountBuilder;
    /** @var \Ess\M2ePro\Helper\Magento\Store */
    private $storeHelper;

    public function __construct(
        \Ess\M2ePro\Helper\Magento\Store $storeHelper,
        \Ess\M2ePro\Helper\Module\Support $supportHelper,
        \Ess\M2ePro\Helper\Data\GlobalData $globalDataHelper,
        \Ess\M2ePro\Helper\Magento\Store\Website $storeWebsiteHelper,
        \Magento\Tax\Model\ClassModel $taxClass,
        \Magento\Customer\Model\Group $customerGroup,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Ess\M2ePro\Model\Amazon\Account\Builder $accountBuilder,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->orderConfig = $orderConfig;
        $this->customerGroup = $customerGroup;
        $this->taxClass = $taxClass;
        $this->supportHelper = $supportHelper;
        $this->globalDataHelper = $globalDataHelper;
        $this->storeWebsiteHelper = $storeWebsiteHelper;
        $this->accountBuilder = $accountBuilder;
        $this->storeHelper = $storeHelper;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    private function addImportTaxRegistrationNumber(
        array $formData,
        Fieldset $fieldset,
        ?\Ess\M2ePro\Model\Account $account
    ): void {
        $type = 'hidden';
        $value = 0;
        $tooltip = '';

        if (isset($account) && $this->isMarketplaceCollectTaxes($account)) {
            $type = 'select';
            $value = $formData['magento_orders_settings']['tax']['import_tax_id_in_magento_order'];
            $tooltip = $this->getTooltipHtml(
                __(
                    'Once enabled, find the Tax Registration Number displayed as VAT
in the Shipping Address of your Magento Order.'
                )
            );
        }

        $fieldset->addField(
            'magento_orders_tax_import_tax_id_in_magento_order',
            $type,
            [
                'name' => 'magento_orders_settings[tax][import_tax_id_in_magento_order]',
                'label' => __('Import Tax Registration Number to Magento Order'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $value,
                'after_element_html' => $tooltip,
            ]
        );
    }

    private function isMarketplaceCollectTaxes(\Ess\M2ePro\Model\Account $account): bool
    {
        $marketplaceId = $account->getChildObject()->getMarketplaceId();

        if (
            in_array(
                $marketplaceId,
                \Ess\M2ePro\Helper\Component\Amazon::MARKETPLACES_WITH_COLLECT_TAXES
            )
        ) {
            return true;
        }

        return false;
    }

    protected function _prepareForm()
    {
        /** @var \Ess\M2ePro\Model\Account|null $account */
        $account = $this->globalDataHelper->getValue('edit_account');
        /** @var \Ess\M2ePro\Model\Amazon\Account|null $amazonAccount */
        $amazonAccount = $account !== null ? $account->getChildObject() : null;

        // ---------------------------------------
        $websites = $this->storeWebsiteHelper->getWebsites(true);
        // ---------------------------------------

        // ---------------------------------------
        $temp = $this->customerGroup->getCollection()->toArray();
        $groups = $temp['items'];
        // ---------------------------------------

        // ---------------------------------------
        $productTaxClasses = $this->taxClass->getCollection()
                                            ->addFieldToFilter(
                                                'class_type',
                                                \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT
                                            )
                                            ->toOptionArray();
        $none = ['value' => \Ess\M2ePro\Model\Magento\Product::TAX_CLASS_ID_NONE, 'label' => __('None')];
        array_unshift($productTaxClasses, $none);

        $formData = $account !== null ? array_merge($account->getData(), $amazonAccount->getData()) : [];
        $formData['magento_orders_settings'] = !empty($formData['magento_orders_settings'])
            ? \Ess\M2ePro\Helper\Json::decode($formData['magento_orders_settings']) : [];

        $defaults = $this->accountBuilder->getDefaultData();

        if (isset($formData['magento_orders_settings']['tax']['excluded_states'])) {
            unset($defaults['magento_orders_settings']['tax']['excluded_states']);
        }

        if (isset($formData['magento_orders_settings']['tax']['excluded_countries'])) {
            unset($defaults['magento_orders_settings']['tax']['excluded_countries']);
        }

        $isEdit = !empty($this->getRequest()->getParam('id'));

        if ($isEdit) {
            $defaults['magento_orders_settings']['refund_and_cancellation']['refund_mode'] = 0;
        }

        $formData = array_replace_recursive($defaults, $formData);

        $formData['magento_orders_settings']['listing']['create_from_date'] = $this->convertGmtToLocal(
            $formData['magento_orders_settings']['listing']['create_from_date'] ?? null
        );

        $formData['magento_orders_settings']['listing_other']['create_from_date'] = $this->convertGmtToLocal(
            $formData['magento_orders_settings']['listing_other']['create_from_date'] ?? null
        );

        if (is_array($formData['magento_orders_settings']['tax']['excluded_states'])) {
            $formData['magento_orders_settings']['tax']['excluded_states'] = implode(
                ',',
                $formData['magento_orders_settings']['tax']['excluded_states']
            );
        }

        if (is_array($formData['magento_orders_settings']['tax']['excluded_countries'])) {
            $formData['magento_orders_settings']['tax']['excluded_countries'] = implode(
                ',',
                $formData['magento_orders_settings']['tax']['excluded_countries']
            );
        }

        $form = $this->_formFactory->create();

        $form->addField(
            'amazon_accounts_orders',
            self::HELP_BLOCK,
            [
                'content' => $this->__(
                    <<<HTML
<p>Specify how M2E Pro should manage the Orders imported from Amazon.</p><br/>
<p>You are able to configure the different rules of <strong>Magento Order Creation</strong> considering whether the
Item was listed via M2E Pro or by some other software.</p><br/>
<p>The <strong>Reserve Quantity</strong> feature will automatically work for imported Amazon Orders with Pending Status
to hold the Stock until Magento Order is created or the reservation term is expired.</p><br/>
<p>Moreover, you can provide the settings for <strong>Orders fulfilled by Amazon</strong>. Specify whether the
corresponding Magento Order has to be created and or not. Additionally, you are able to reduce Magento Stock taking
into account the FBA Orders.</p><br/>
<p>Besides, you can set your preferences for the <strong>Refund & Cancellation, Tax, Customer, Order Number</strong>
and <strong>Order Status Mapping</strong> Settings as well as specify the automatic creation of invoices and
shipment notifications.</p><br/>
<p>More detailed information you can find <a href="%url%" target="_blank" class="external-link">here</a>.</p>
HTML
                    ,
                    $this->supportHelper->getDocumentationArticleUrl('orders')
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'listed_by_m2e',
            [
                'legend' => __('Product Is Listed By M2E Pro'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_mode',
            'select',
            [
                'name' => 'magento_orders_settings[listing][mode]',
                'label' => __('Create Order in Magento'),
                'values' => [
                    1 => __('Yes'),
                    0 => __('No'),
                ],
                'value' => $formData['magento_orders_settings']['listing']['mode'],
                'tooltip' => __(
                    'Whether an Order has to be created in Magento if a sold Product belongs to M2E Pro Listings.'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_create_from_date',
            'text',
            [
                'container_id' => 'magento_orders_listings_create_from_date_container',
                'name' => 'magento_orders_settings[listing][create_from_date]',
                'label' => __('Create From Date'),
                'tooltip' => __(
                    'Select the start date for channel orders to be created in Magento.'
                    . ' Orders purchased before this date will not be imported into Magento.'
                ),
                'value' => $formData['magento_orders_settings']['listing']['create_from_date'],
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_store_mode',
            'select',
            [
                'container_id' => 'magento_orders_listings_store_mode_container',
                'name' => 'magento_orders_settings[listing][store_mode]',
                'label' => __('Magento Store View Source'),
                'values' => [
                    Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT => __('Use Store View from Listing'),
                    Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM => __('Choose Store View Manually'),
                ],
                'value' => $formData['magento_orders_settings']['listing']['store_mode'],
                'tooltip' => __(
                    'If Store View must be automatically taken from the Listing
                    or manually chosen from available Store View values.'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_store_id',
            self::STORE_SWITCHER,
            [
                'container_id' => 'magento_orders_listings_store_id_container',
                'name' => 'magento_orders_settings[listing][store_id]',
                'label' => __('Magento Store View'),
                'required' => true,
                'value' => !empty($formData['magento_orders_settings']['listing']['store_id'])
                    ? $formData['magento_orders_settings']['listing']['store_id'] : '',
                'has_empty_option' => true,
                'has_default_option' => false,
                'tooltip' => __('The Magento Store View that Orders will be placed in.'),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_amazon_accounts_magento_orders_listings_other',
            [
                'legend' => __('Product Is Listed By Any Other Software'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_other_mode',
            'select',
            [
                'name' => 'magento_orders_settings[listing_other][mode]',
                'label' => __('Create Order in Magento'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $formData['magento_orders_settings']['listing_other']['mode'],
                'tooltip' => __(
                    'Choose whether a Magento Order should be created if an Amazon Order is received for an item that
                    does <b>not</b> belong to the M2E Pro Listing.'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_other_create_from_date',
            'text',
            [
                'container_id' => 'magento_orders_listings_other_create_from_date_container',
                'name' => 'magento_orders_settings[listing_other][create_from_date]',
                'label' => __('Create From Date'),
                'tooltip' => __(
                    'Select the start date for channel orders to be created in Magento.'
                    . ' Orders purchased before this date will not be imported into Magento.'
                ),
                'value' => $formData['magento_orders_settings']['listing_other']['create_from_date'],
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_other_store_id',
            self::STORE_SWITCHER,
            [
                'container_id' => 'magento_orders_listings_other_store_id_container',
                'name' => 'magento_orders_settings[listing_other][store_id]',
                'label' => __('Magento Store View'),
                'value' => !empty($formData['magento_orders_settings']['listing_other']['store_id']) ?
                    $formData['magento_orders_settings']['listing_other']['store_id'] :
                    $this->storeHelper->getDefaultStoreId(),
                'required' => true,
                'has_empty_option' => true,
                'has_default_option' => false,
                'tooltip' => __('The Magento Store View that Orders will be placed in.'),
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_other_product_mode',
            'select',
            [
                'container_id' => 'magento_orders_listings_other_product_mode_container',
                'name' => 'magento_orders_settings[listing_other][product_mode]',
                'label' => __('Product Not Found'),
                'values' => [
                    Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE => __('Do Not Create Order'),
                    Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IMPORT => __('Create Product and Order'),
                ],
                'value' => $formData['magento_orders_settings']['listing_other']['product_mode'],
                'tooltip' => __('What has to be done if a Listed Product does not exist in Magento.')
                    . '<span id="magento_orders_listings_other_product_mode_note">'
                    . __(
                        '<br/><b>Note:</b> Only Simple Products without Variations can be created in Magento.
                         If there is a Product with Variations on Amazon,
                         M2E Pro creates different Simple Products for each Variation.'
                    )
                    . '</span>',
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_other_product_mode_warning',
            self::MESSAGES,
            [
                'messages' => [
                    [
                        'type' => \Magento\Framework\Message\MessageInterface::TYPE_NOTICE,
                        'content' => __(
                            'Please note that a new Magento Product will be created
                            if the corresponding SKU is not found in your Catalog.'
                        ),
                    ],
                ],
                'style' => 'max-width:450px; margin-left:20%',
            ]
        );

        $values = [];
        foreach ($productTaxClasses as $taxClass) {
            $values[$taxClass['value']] = $taxClass['label'];
        }

        $fieldset->addField(
            'magento_orders_listings_other_product_tax_class_id',
            'select',
            [
                'container_id' => 'magento_orders_listings_other_product_tax_class_id_container',
                'name' => 'magento_orders_settings[listing_other][product_tax_class_id]',
                'label' => __('Product Tax Class'),
                'values' => $values,
                'value' => $formData['magento_orders_settings']['listing_other']['product_tax_class_id'],
                'tooltip' => __('Tax Class which will be used for Products created by M2E Pro.'),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_amazon_accounts_magento_orders_fba',
            [
                'legend' => __('FBA Orders Settings'),
                'collapsable' => true,
                'tooltip' => __(
                    'In this Block you can manage Stock Inventory of Products fulfilled by Amazon  (FBA Orders).<br/>
                <b>Yes</b> - after Magento Order Creation of FBA Order, Quantity of Product reduces in Magento.<br/>
                <b>No</b> - Magento Order Creation of FBA Order does not affect Quantity of Magento Product.'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_fba_mode',
            'select',
            [
                'name' => 'magento_orders_settings[fba][mode]',
                'label' => __('Create Order in Magento'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $formData['magento_orders_settings']['fba']['mode'],
                'tooltip' => __(
                    'Whether an Order has to be created in Magento if a sold Product is fulfilled by Amazon.'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_fba_store_mode',
            'select',
            [
                'container_id' => 'magento_orders_fba_store_mode_container',
                'name' => 'magento_orders_settings[fba][store_mode]',
                'label' => __('Create in separate Store View'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $formData['magento_orders_settings']['fba']['store_mode'],
            ]
        );

        $fieldset->addField(
            'magento_orders_fba_store_id',
            self::STORE_SWITCHER,
            [
                'container_id' => 'magento_orders_fba_store_id_container',
                'name' => 'magento_orders_settings[fba][store_id]',
                'label' => __('Magento Store View'),
                'value' => !empty($formData['magento_orders_settings']['fba']['store_id'])
                    ? $formData['magento_orders_settings']['fba']['store_id'] : '',
                'required' => true,
                'has_empty_option' => true,
                'has_default_option' => false,
            ]
        );

        $fieldset->addField(
            'magento_orders_fba_stock_mode',
            'select',
            [
                'container_id' => 'magento_orders_fba_stock_mode_container',
                'name' => 'magento_orders_settings[fba][stock_mode]',
                'label' => __('Manage Stock'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $formData['magento_orders_settings']['fba']['stock_mode'],
                'tooltip' => __(
                    'If <i>Yes</i>, after Magento Order Creation QTY of Magento Product reduces.'
                ),
            ]
        );

        $shipByDateFieldset = $form->addFieldset(
            'magento_block_amazon_accounts_magento_orders_shipping_information',
            [
                'legend' => __('Shipping information'),
                'collapsable' => true,
            ]
        );

        $shipByDateFieldset->addField(
            'magento_orders_import_labels_settings',
            'select',
            [
                'name' => 'magento_orders_settings[shipping_information][import_labels]',
                'label' => __('Import Invoice by Amazon, B2B, Prime labels to Magento order'),
                'values' => [
                    1 => __('Yes'),
                    0 => __('No'),
                ],
                'value' => $formData['magento_orders_settings']['shipping_information']['import_labels'] ?? 1,
            ]
        );

        $shipByDateFieldset->addField(
            'magento_orders_ship_by_date_settings',
            'select',
            [
                'name' => 'magento_orders_settings[shipping_information][ship_by_date]',
                'label' => __('Import Ship by date to Magento order'),
                'values' => [
                    1 => __('Yes'),
                    0 => __('No'),
                ],
                'value' => $formData['magento_orders_settings']['shipping_information']['ship_by_date'] ?? 1,
            ]
        );

        $shipByDateFieldset->addField(
            'magento_orders_update_without_track_settings',
            'select',
            [
                'name' => 'magento_orders_settings[shipping_information][update_without_track]',
                'label' => __('Update Order as Shipped without Tracking Info'),
                'values' => [
                    1 => __('Yes'),
                    0 => __('No'),
                ],
                'value' => $formData['magento_orders_settings']['shipping_information']['update_without_track'] ?? 0,
            ]
        );

        $value = $formData['magento_orders_settings']['shipping_information']['shipping_address_region_override'] ?? 1;
        $shipByDateFieldset->addField(
            'magento_orders_shipping_information_shipping_address_region_override',
            'select',
            [
                'name' => 'magento_orders_settings[shipping_information][shipping_address_region_override]',
                'label' => __('Override invalid Region/State required value'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $value,
                'tooltip' => __(
                    'When enabled, the invalid Region/State value will be replaced with an alternative one to create
                     an order in Magento.'
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_amazon_accounts_magento_orders_number',
            [
                'legend' => __('Magento Order Number'),
                'collapsable' => true,
                'tooltip' => __('Sets Magento Order number basing on the Settings below'),
            ]
        );

        $fieldset->addField(
            'magento_orders_number_source',
            'select',
            [
                'name' => 'magento_orders_settings[number][source]',
                'label' => __('Source'),
                'values' => [
                    Account::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO => __('Magento'),
                    Account::MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL => __('Amazon'),
                ],
                'value' => $formData['magento_orders_settings']['number']['source'],
                'tooltip' => __(
                    'If source is set to Magento, Magento Order numbers are created basing on your Magento Settings.
                    If source is set to Amazon, Magento Order numbers are the same as Amazon Order numbers.'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_number_prefix_container',
            self::CUSTOM_CONTAINER,
            [
                'text' => $this->getLayout()
                               ->createBlock(
                                   \Ess\M2ePro\Block\Adminhtml\Amazon\Account\Edit\Tabs\Order\PrefixesTable::class
                               )
                               ->addData(['form_data' => $formData])
                               ->toHtml(),
                'css_class' => 'm2epro-fieldset-table',
                'style' => 'padding: 0 !important;',
            ]
        );

        $fieldset->addField(
            'magento_orders_number_apply_to_amazon',
            'select',
            [
                'name' => 'magento_orders_settings[number][apply_to_amazon]',
                'label' => __('Use as Your Seller Order ID'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $formData['magento_orders_settings']['number']['apply_to_amazon'],
                'tooltip' => __(
                    'Set "Yes" to use Magento Order number as Your Seller Order ID in Amazon Order details.'
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_amazon_accounts_magento_orders_rules',
            [
                'legend' => __('Quantity Reservation'),
                'collapsable' => true,
                'tooltip' => __(
                    'Use the Reserve Quantity Option to prevent the Item being sold, before Magento Order created
                    (as the Product Stock QTY only reduces after Magento Order Creation).
                    It removes Items from Magento Stock at once Amazon Order comes from Amazon.
                    Reserve QTY will be used when Magento Order is created
                    or released when the term of QTY reservation has expired.'
                ),
            ]
        );

        $values = [];
        for ($day = 1; $day <= 14; $day++) {
            if ($day == 1) {
                $values[$day] = $this->__('For %number% day', $day);
            } else {
                $values[$day] = $this->__('For %number% days', $day);
            }
        }

        $fieldset->addField(
            'magento_orders_qty_reservation_days',
            'select',
            [
                'container_id' => 'magento_orders_qty_reservation_days_container',
                'name' => 'magento_orders_settings[qty_reservation][days]',
                'label' => __('Reserve Quantity'),
                'values' => $values,
                'value' => $formData['magento_orders_settings']['qty_reservation']['days'],
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_amazon_accounts_magento_orders_refund_and_cancellation',
            [
                'legend' => __('Refund & Cancellation'),
                'collapsable' => true,
                'tooltip' => __(
                    'Enable an option Cancellation & Refund if Credit Memo is Created to run automatic Cancellation
                     of Amazon Orders or automatic Refund of Items associated to Amazon Orders at the moment
                     of Credit Memos creation in Magento Orders that were created by M2E Pro. <br/><br/>

                     In case Amazon Order has Status Unshipped and you created a Credit Memo for associated
                     Magento Order which include all bought Items,
                     Amazon Order Cancellation will be run automatically. <br/><br/>

                     Automatic Refund of bought Items associated to Amazon Order is available in case
                     Amazon Order has Status Shipped. Refund action will be run only for those Items for which
                     Credit Memos were created.'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_refund',
            'select',
            [
                'container_id' => 'magento_orders_refund_container',
                'name' => 'magento_orders_settings[refund_and_cancellation][refund_mode]',
                'label' => __('Cancel or Refund if Credit Memo is Created'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $formData['magento_orders_settings']['refund_and_cancellation']['refund_mode'],
            ]
        );

        $fieldset->addField(
            'magento_orders_refund_credit_memo',
            'select',
            [
                'container_id' => 'magento_orders_refund_container_credit_memo',
                'name' => 'magento_orders_settings[refund_and_cancellation][credit_memo]',
                'label' => __('Automatically create Credit Memo when Order is cancelled'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $formData['magento_orders_settings']['refund_and_cancellation']['credit_memo'],
            ]
        );

        $fieldset->addField(
            'magento_orders_refund_credit_memo_buyer_requested_cancel',
            'select',
            [
                'container_id' => 'magento_orders_refund_container_credit_memo_buyer_requested_cancel',
                'name' => 'magento_orders_settings[refund_and_cancellation][credit_memo_buyer_requested_cancel]',
                'label' => __('Automatically create Credit Memo when Buyer requests Cancellation'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $formData['magento_orders_settings']['refund_and_cancellation']
                ['credit_memo_buyer_requested_cancel'],
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_amazon_accounts_magento_orders_customer',
            [
                'legend' => __('Customer Settings'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_customer_mode',
            'select',
            [
                'name' => 'magento_orders_settings[customer][mode]',
                'label' => __('Customer'),
                'values' => [
                    Account::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST => __('Guest Account'),
                    Account::MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED => __('Predefined Customer'),
                    Account::MAGENTO_ORDERS_CUSTOMER_MODE_NEW => __('Create New'),
                ],
                'value' => $formData['magento_orders_settings']['customer']['mode'],
                'note' => __('Customer for which Magento Orders will be created.'),
                'tooltip' => __(
                    'There are several ways to specify a Customer for which Magento Orders will be created: <br/><br/>
                     <b>Guest Account</b> - the System does not require a Customer Account to be created.
                     Default Guest Account will be defined as a Customer. <br/>
                     <b>Note:</b> The Guest Checkout Option must be enabled in Magento.
                     (<i>Yes</i> must be chosen in the Allow Guest Checkout Option in
                     Magento > Stores > Configuration > Sales > Checkout). <br/>
                     <b>Predefined Customer</b> - the System uses one predefined
                     Customer for all Amazon Orders related to this Account. You will be required
                     to provide an ID of the existing Customer, which you can find in
                     Magento > Customers > All Customers. <br/>
                     <b>Create New</b> - a new Customer will be created in Magento,
                     using Amazon Customer data of Amazon Order. <br/>
                     <b>Note:</b> A unique Customer Identifier is his e-mail address.
                     If the one already exists among Magento Customers e-mails,
                     the System uses this Customer as owner of Order and links Order to him.
                      A new Customer will not be created. <br/>
                '
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_customer_id',
            'text',
            [
                'container_id' => 'magento_orders_customer_id_container',
                'class' => 'validate-digits M2ePro-account-customer-id',
                'name' => 'magento_orders_settings[customer][id]',
                'label' => __('Customer ID'),
                'value' => $formData['magento_orders_settings']['customer']['id'],
                'required' => true,
            ]
        );

        $values = [];
        foreach ($websites as $website) {
            $values[$website['website_id']] = $website['name'];
        }

        $fieldset->addField(
            'magento_orders_customer_new_website_id',
            'select',
            [
                'container_id' => 'magento_orders_customer_new_website_id_container',
                'name' => 'magento_orders_settings[customer][website_id]',
                'label' => __('Associate to Website'),
                'values' => $values,
                'value' => $formData['magento_orders_settings']['customer']['website_id'],
                'required' => true,
            ]
        );

        $values = [];
        foreach ($groups as $group) {
            $values[$group['customer_group_id']] = $group['customer_group_code'];
        }

        $fieldset->addField(
            'magento_orders_customer_new_group_id',
            'select',
            [
                'container_id' => 'magento_orders_customer_new_group_id_container',
                'name' => 'magento_orders_settings[customer][group_id]',
                'label' => __('Customer Group'),
                'values' => $values,
                'value' => $formData['magento_orders_settings']['customer']['group_id'],
                'required' => true,
            ]
        );

        $value = [];
        $formData['magento_orders_settings']['customer']['notifications']['order_created']
        && $value[] = 'order_created';
        $formData['magento_orders_settings']['customer']['notifications']['invoice_created']
        && $value[] = 'invoice_created';

        $fieldset->addField(
            'magento_orders_customer_new_notifications',
            'multiselect',
            [
                'container_id' => 'magento_orders_customer_new_notifications_container',
                'name' => 'magento_orders_settings[customer][notifications][]',
                'label' => __('Send Emails When The Following Is Created'),
                'values' => [
                    ['label' => __('Magento Order'), 'value' => 'order_created'],
                    ['label' => __('Invoice'), 'value' => 'invoice_created'],
                ],
                'value' => $value,
                'tooltip' => __(
                    '<p>Necessary emails will be sent according to Magento Settings in
                    Stores > Configuration > Sales > Sales Emails.</p>
                    <p>Hold Ctrl Button to choose more than one Option.</p>'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_customer_import_buyer_company_name',
            'select',
            [
                'container_id' => 'magento_orders_customer_import_buyer_company_name_container',
                'name' => 'magento_orders_settings[customer][import_buyer_company_name]',
                'label' => __('Import Buyer Company Name for B2B Orders'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $formData['magento_orders_settings']['customer']['import_buyer_company_name'],
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_amazon_accounts_magento_orders_tax',
            [
                'legend' => __('Order Tax Settings'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_tax_mode',
            'select',
            [
                'name' => 'magento_orders_settings[tax][mode]',
                'label' => __('Tax Source'),
                'values' => [
                    Account::MAGENTO_ORDERS_TAX_MODE_NONE => __('None'),
                    Account::MAGENTO_ORDERS_TAX_MODE_CHANNEL => __('Amazon'),
                    Account::MAGENTO_ORDERS_TAX_MODE_MAGENTO => __('Magento'),
                    Account::MAGENTO_ORDERS_TAX_MODE_MIXED => __('Amazon & Magento'),
                ],
                'value' => $formData['magento_orders_settings']['tax']['mode'],
                'tooltip' => $this->__(
                    'Choose where the tax settings for your Magento Order will be taken from. See
                    <a href="%url%" target="_blank">this article</a> for more details.',
                    $this->supportHelper->getDocumentationArticleUrl(
                        'help/m1/amazon-integration/sales-orders/tax-calculation-settings'
                    )
                ),
            ]
        );

        $button = $this->getLayout()->createBlock(\Ess\M2ePro\Block\Adminhtml\Magento\Button::class)->addData(
            [
                'label' => __('Select states'),
                'onclick' => 'AmazonAccountObj.openExcludedStatesPopup()',
                'class' => 'action-primary',
                'style' => 'margin-left: 70px;',
                'id' => 'show_excluded_states_button',
            ]
        );

        $fieldset->addField(
            'magento_orders_tax_amazon_collects',
            'select',
            [
                'container_id' => 'magento_orders_tax_amazon_collects_container',
                'name' => 'magento_orders_settings[tax][amazon_collects]',
                'label' => __('Exclude tax collected by Amazon'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $formData['magento_orders_settings']['tax']['amazon_collects'],
                'after_element_html' => $this->getTooltipHtml(
                    __("Tax won't be included in orders shipped to the selected states.")
                ) . $button->toHtml(),
            ]
        );

        $fieldset->addField(
            'magento_orders_tax_amazon_collects_for_uk_shipment',
            'select',
            [
                'container_id' => 'magento_orders_tax_amazon_collects_for_uk_shipment_container',
                'name' => 'magento_orders_settings[tax][amazon_collect_for_uk]',
                'label' => __('Exclude UK VAT collected by Amazon'),
                'values' => [
                    Account::SKIP_TAX_FOR_UK_SHIPMENT_NONE => __('None'),
                    Account::SKIP_TAX_FOR_UK_SHIPMENT => __('All orders with UK shipments'),
                    Account::SKIP_TAX_FOR_UK_SHIPMENT_WITH_CERTAIN_PRICE => __(
                        'Orders under 135GBP price'
                    ),
                ],
                'value' => $formData['magento_orders_settings']['tax']['amazon_collect_for_uk'],
                'after_element_html' => $this->getTooltipHtml(
                    $this->__(
                        "VAT won't be included in orders with UK shipment. Find more info "
                        . '<a href="%url%" target="_blank">here</a>.',
                        $this->supportHelper->getDocumentationArticleUrl(
                            'help/m1/amazon-integration/sales-orders/tax-calculation-settings'
                        )
                    )
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_tax_excluded_states',
            'hidden',
            [
                'name' => 'magento_orders_settings[tax][excluded_states]',
                'value' => $formData['magento_orders_settings']['tax']['excluded_states'],
            ]
        );

        $button = $this->getLayout()->createBlock(\Ess\M2ePro\Block\Adminhtml\Magento\Button::class)->addData(
            [
                'label' => __('Select countries'),
                'onclick' => 'AmazonAccountObj.openExcludedCountriesPopup()',
                'class' => 'action-primary',
                'style' => 'margin-left: 70px;',
                'id' => 'show_excluded_countries_button',
            ]
        );

        $fieldset->addField(
            'magento_orders_tax_amazon_collects_for_eea_shipment',
            'select',
            [
                'container_id' => 'magento_orders_tax_amazon_collects_for_eea_shipment_container',
                'name' => 'magento_orders_settings[tax][amazon_collect_for_eea]',
                'label' => __('Exclude EEA VAT collected by Amazon'),
                'style' => 'max-width: 240px;',
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => $formData['magento_orders_settings']['tax']['amazon_collect_for_eea'],
                'after_element_html' => $this->getTooltipHtml(
                    __("VAT won't be included in orders shipped to the selected countries.")
                ) . $button->toHtml(),
            ]
        );

        $fieldset->addField(
            'magento_orders_tax_amazon_round_of_rate_value',
            'select',
            [
                'container_id' => 'magento_orders_tax_round_of_rate_value_container',
                'name' => 'magento_orders_settings[tax][round_of_rate_value]',
                'label' => __('Tax Rate rounding'),
                'style' => 'max-width: 240px;',
                'values' => [
                    Account::MAGENTO_ORDERS_TAX_ROUND_OF_RATE_NO => __('No'),
                    Account::MAGENTO_ORDERS_TAX_ROUND_OF_RATE_YES => __('Yes'),
                ],
                'value' => $formData['magento_orders_settings']['tax']['round_of_rate_value'],
                'after_element_html' => $this->getTooltipHtml(
                    __(
                        "Enable to round the decimal places of Tax Rates before they're shown in your Magento order"
                    )
                ),
            ]
        );

        $form->addField(
            'magento_orders_tax_amazon_round_of_rate_value_confirmation_popup_template',
            self::CUSTOM_CONTAINER,
            [
                'text' => __(
                    'By activating rounding of the tax rate,'
                    . ' you are confirming that all rate numbers with decimals'
                    . ' can be converted to the nearest non-decimal value - for instance,'
                    . ' 7.43% is rounded down to 7%, while 8.6% will become 9.'
                    . ' Since it has been rounded off from its original value,'
                    . ' the new percentage may not precisely reflect order costs calculations.'
                ),
                'style' => 'display: none;',
            ]
        );

        $fieldset->addField(
            'magento_orders_tax_excluded_countries',
            'hidden',
            [
                'name' => 'magento_orders_settings[tax][excluded_countries]',
                'value' => $formData['magento_orders_settings']['tax']['excluded_countries'],
            ]
        );

        $this->addImportTaxRegistrationNumber($formData, $fieldset, $account);

        $fieldset = $form->addFieldset(
            'magento_block_amazon_accounts_magento_orders_status_mapping',
            [
                'legend' => __('Order Status Mapping'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_status_mapping_mode',
            'select',
            [
                'name' => 'magento_orders_settings[status_mapping][mode]',
                'label' => __('Status Mapping'),
                'values' => [
                    Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT => __('Default Order Statuses'),
                    Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_CUSTOM => __('Custom Order Statuses'),
                ],
                'value' => $formData['magento_orders_settings']['status_mapping']['mode'],
                'tooltip' => __(
                    'Set the correspondence between Amazon and Magento order statuses.
                    The status of your Magento order will be updated based on these settings.'
                ),
            ]
        );

        $isDisabledStatusStyle = (
            $formData['magento_orders_settings']['status_mapping']['mode']
            == Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT
        );

        if (
            $formData['magento_orders_settings']['status_mapping']['mode']
            == Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT
        ) {
            $formData['magento_orders_settings']['status_mapping']['processing']
                = Account::MAGENTO_ORDERS_STATUS_MAPPING_PROCESSING;
            $formData['magento_orders_settings']['status_mapping']['shipped']
                = Account::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED;
        }

        $statusList = $this->orderConfig->getStatuses();

        $fieldset->addField(
            'magento_orders_status_mapping_processing',
            'select',
            [
                'container_id' => 'magento_orders_status_mapping_processing_container',
                'name' => 'magento_orders_settings[status_mapping][processing]',
                'label' => __('Order Status is Unshipped / Partially Shipped'),
                'values' => $statusList,
                'value' => $formData['magento_orders_settings']['status_mapping']['processing'],
                'disabled' => $isDisabledStatusStyle,
            ]
        );

        $fieldset->addField(
            'magento_orders_status_mapping_shipped',
            'select',
            [
                'container_id' => 'magento_orders_status_mapping_shipped_container',
                'name' => 'magento_orders_settings[status_mapping][shipped]',
                'label' => __('Shipping Is Completed'),
                'values' => $statusList,
                'value' => $formData['magento_orders_settings']['status_mapping']['shipped'],
                'disabled' => $isDisabledStatusStyle,
            ]
        );

        $this->setForm($form);

        $this->jsTranslator->addTranslations(
            [
                'No Customer entry is found for specified ID.' => __(
                    'No Customer entry is found for specified ID.'
                ),
                'Select states where tax will be excluded' => __(
                    'Select states where tax will be excluded'
                ),
                'Select countries where VAT will be excluded' => __(
                    'Select countries where VAT will be excluded'
                ),
            ]
        );

        return parent::_prepareForm();
    }

    private function convertGmtToLocal(?string $dateTimeString): ?string
    {
        if (empty($dateTimeString)) {
            return null;
        }

        try {
            $date = \Ess\M2ePro\Helper\Date::createDateGmt($dateTimeString);
        } catch (\Throwable $e) {
            return null;
        }

        return \Ess\M2ePro\Helper\Date::createWithLocalTimeZone($date)->format('Y-m-d H:i:s');
    }
}
