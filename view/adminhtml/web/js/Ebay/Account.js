define([
    'Magento_Ui/js/modal/modal',
    'M2ePro/Common',
    'M2ePro/External/jstree/jstree.min',
    'mage/adminhtml/form',
    'mage/calendar'
], function(modal) {

    window.EbayAccount = Class.create(Common, {

        // ---------------------------------------

        initialize: function() {
            this.setValidationCheckRepetitionValue('M2ePro-account-title',
                M2ePro.translator.translate('The specified Title is already used for other Account. Account Title must be unique.'),
                'Account', 'title', 'id',
                M2ePro.formData.id,
                M2ePro.php.constant('\\Ess\\M2ePro\\Helper\\Component\\Ebay::NICK')
            );

            jQuery.validator.addMethod('M2ePro-account-customer-id', function(value) {

                var checkResult = false;

                if ($('magento_orders_customer_id_container').getStyle('display') == 'none') {
                    return true;
                }

                new Ajax.Request(M2ePro.url.get('general/checkCustomerId'), {
                    method: 'post',
                    asynchronous: false,
                    parameters: {
                        customer_id: value,
                        id: M2ePro.formData.id
                    },
                    onSuccess: function(transport) {
                        checkResult = transport.responseText.evalJSON()['ok'];
                    }
                });

                return checkResult;
            }, M2ePro.translator.translate('No Customer entry is found for specified ID.'));

            jQuery.validator.addMethod('M2ePro-account-feedback-templates', function(value) {

                if (value == 0) {
                    return true;
                }

                var checkResult = false;

                new Ajax.Request(M2ePro.url.get('ebay_account_feedback/templateCheck'), {
                    method: 'post',
                    asynchronous: false,
                    parameters: {
                        id: M2ePro.formData.id
                    },
                    onSuccess: function(transport) {
                        checkResult = transport.responseText.evalJSON()['ok'];
                    }
                });

                return checkResult;
            }, M2ePro.translator.translate('You should create at least one Response Template.'));

            jQuery.validator.addMethod(
                'M2ePro-require-select-attribute',
                function(value, el) {
                    if ($('other_listings_mapping_mode').value == 0) {
                        return true;
                    }

                    var isAttributeSelected = false;

                    $$('.attribute-mode-select').each(function(obj) {
                        if (obj.value != 0) {
                            isAttributeSelected = true;
                        }
                    });

                    return isAttributeSelected;
                },
                M2ePro.translator.translate(
                    'If Yes is chosen, you must select at least one Attribute for Product Linking.'
                )
            );

            this.initMagentoOrdersCreateFromDate();
        },

        initMagentoOrdersCreateFromDate: function () {
            const listingsCreateFromDate = jQuery('#magento_orders_listings_create_from_date');
            listingsCreateFromDate.calendar({
                showsTime: true,
                dateFormat: 'yy-mm-dd',
                timeFormat: 'HH:mm:00',
                showButtonPanel: false,
            }).datepicker('setDate', listingsCreateFromDate.val());

            const listingsOtherCreateFromDate = jQuery('#magento_orders_listings_other_create_from_date');
            listingsOtherCreateFromDate.calendar({
                showsTime: true,
                dateFormat: 'yy-mm-dd',
                timeFormat: 'HH:mm:00',
                showButtonPanel: false,
            }).datepicker('setDate', listingsOtherCreateFromDate.val());
        },

        initObservers: function() {

            if ($('ebayAccountEditTabs_listingOther')) {

                $('other_listings_synchronization')
                    .observe('change', this.other_listings_synchronization_change)
                    .simulate('change');
                $('other_listings_mapping_mode')
                    .observe('change', this.other_listings_mapping_mode_change)
                    .simulate('change');
                $('mapping_sku_mode')
                    .observe('change', this.mapping_sku_mode_change)
                    .simulate('change');
                $('mapping_title_mode')
                    .observe('change', this.mapping_title_mode_change)
                    .simulate('change');
                $('mapping_item_id_mode')
                    .observe('change', this.mapping_item_id_mode_change)
                    .simulate('change');
            }

            if ($('ebayAccountEditTabs_order')) {

                $('magento_orders_listings_mode')
                    .observe('change', this.magentoOrdersListingsModeChange)
                    .simulate('change');
                $('magento_orders_listings_store_mode')
                    .observe('change', this.magentoOrdersListingsStoreModeChange)
                    .simulate('change');

                $('magento_orders_listings_other_mode')
                    .observe('change', this.magentoOrdersListingsOtherModeChange)
                    .simulate('change');
                $('magento_orders_listings_other_product_mode')
                    .observe('change', this.magentoOrdersListingsOtherProductModeChange);

                $('magento_orders_number_source')
                    .observe('change', this.magentoOrdersNumberChange);
                $('magento_orders_number_prefix_prefix')
                    .observe('keyup', this.magentoOrdersNumberChange);
                $('magento_orders_number_prefix_use_marketplace_prefix')
                    .observe('change', this.magentoOrdersNumberChange);

                EbayAccountObj.renderOrderNumberExample();

                $('magento_orders_customer_mode')
                    .observe('change', this.magentoOrdersCustomerModeChange)
                    .simulate('change');

                $('magento_orders_status_mapping_mode')
                    .observe('change', this.magentoOrdersStatusMappingModeChange);

                $('order_number_example-note').previous().remove();
            }
        },

        // ---------------------------------------

        saveAndClose: function() {
            var self = this,
                url = typeof M2ePro.url.urls.formSubmit == 'undefined' ?
                    M2ePro.url.formSubmit + 'back/' + Base64.encode('list') + '/' :
                    M2ePro.url.get('formSubmit', {'back': Base64.encode('list')});

            if (!this.isValidForm()) {
                return;
            }

            new Ajax.Request(url, {
                method: 'post',
                parameters: Form.serialize($('edit_form')),
                onSuccess: function(transport) {
                    transport = transport.responseText.evalJSON();

                    if (transport.success) {
                        window.close();
                    } else {
                        self.alert(transport.message);
                    }
                }
            });
        },

        // ---------------------------------------

        deleteClick: function(id) {
            this.confirm({
                content: M2ePro.translator.translate('confirmation_account_delete'),
                actions: {
                    confirm: function() {
                        if (id === undefined) {
                            setLocation(M2ePro.url.get('deleteAction'));
                        } else {
                            setLocation(M2ePro.url.get('*/ebay_account/delete/', {
                                id: id,
                            }));
                        }
                    },
                    cancel: function() {
                        return false;
                    }
                }
            });
        },

        feedbacksReceiveChange: function() {
            var self = EbayAccountObj;

            if ($('feedbacks_receive').value == 1) {
                $('feedbacks_auto_response_container').show();
            } else {
                $('feedbacks_auto_response_container').hide();

            }
            $('feedbacks_auto_response').value = M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::FEEDBACKS_AUTO_RESPONSE_NONE');
            self.feedbacksAutoResponseChange();
        },

        feedbacksAutoResponseChange: function() {
            if ($('feedbacks_auto_response').value == M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::FEEDBACKS_AUTO_RESPONSE_NONE')) {
                $('feedbacks_auto_response_only_positive_container').hide();
                $('feedback_templates_grid_container').hide();
            } else {
                $('feedbacks_auto_response_only_positive_container').show();
                $('feedback_templates_grid_container').show();
            }
        },

        // ---------------------------------------

        openFeedbackTemplatePopup: function(templateId) {
            var self = this;

            new Ajax.Request(M2ePro.url.get('ebay_account_feedback_template/getForm'), {
                method: 'GET',
                parameters: {
                    id: templateId
                },
                onSuccess: function(transport) {

                    var response = transport.responseText.evalJSON();

                    var container = $('edit_feedback_template_form_container');

                    if (container) {
                        container.remove();
                    }

                    $('html-body').insert({
                        bottom: '<div id="edit_feedback_template_form_container">' + response.html + '</div>'
                    });

                    self.initFormValidation('#edit_feedback_template_form');

                    self.feedbackTemplatePopup = jQuery('#edit_feedback_template_form_container');

                    modal({
                        title: response.title,
                        type: 'popup',
                        modalClass: 'width-50',
                        buttons: [{
                            text: M2ePro.translator.translate('Cancel'),
                            class: 'action-secondary action-dismiss',
                            click: function() {
                                self.feedbackTemplatePopup.modal('closeModal');
                            }
                        }, {
                            text: M2ePro.translator.translate('Save'),
                            class: 'action-primary',
                            click: function() {
                                if (!jQuery('#edit_feedback_template_form').valid()) {
                                    return false;
                                }

                                new Ajax.Request(M2ePro.url.get('ebay_account_feedback_template/save'), {
                                    parameters: $('edit_feedback_template_form').serialize(true),
                                    onSuccess: function() {
                                        self.feedbackTemplatePopup.modal('closeModal');
                                        $('add_feedback_template_button_container').hide();
                                        $('feedback_templates_grid').show();
                                        window['ebayAccountEditTabsFeedbackGridJsObject'].reload();
                                    }
                                });
                            }
                        }]
                    }, self.feedbackTemplatePopup);

                    self.feedbackTemplatePopup.modal('openModal');
                }
            });
        },

        // ---------------------------------------

        feedbacksDeleteAction: function(id) {
            this.confirm({
                actions: {
                    confirm: function() {
                        new Ajax.Request(M2ePro.url.get('ebay_account_feedback_template/delete'), {
                            method: 'post',
                            parameters: {
                                id: id
                            },
                            onSuccess: function() {
                                if ($('ebayAccountEditTabsFeedbackGrid').select('tbody tr').length == 1) {
                                    $('add_feedback_template_button_container').show();
                                    $('feedback_templates_grid').hide();
                                }

                                window['ebayAccountEditTabsFeedbackGridJsObject'].reload();
                            }
                        });
                    },
                    cancel: function() {
                        return false;
                    }
                }
            });
        },

        // ---------------------------------------

        ebayStoreSelectCategory: function(id) {
            $('ebay_store_categories_selected_container').show();
            $('ebay_store_categories_selected').value = id;
        },

        ebayStoreSelectCategoryHide: function() {
            $('ebay_store_categories_selected_container').hide();
            $('ebay_store_categories_selected').value = '';
        },

        ebayStoreInitExtTree: function(categoriesTreeArray) {
            const convertToJsTreeData = function(data) {
                return data.map(function(item) {
                    return {
                        id: item.id,
                        text: item.text,
                        state: {
                            opened: true,
                            selected: false
                        },
                        children: item.children ? convertToJsTreeData(item.children) : []
                    };
                });
            };

            const treeDiv = jQuery('#tree-div');

            try {
                treeDiv.jstree('destroy');
            } catch (e) {}

            treeDiv.jstree({
                core: {
                    data: convertToJsTreeData(categoriesTreeArray),
                    multiple: false
                },
                checkbox: {
                    keep_selected_style: false,
                    three_state: false,
                    cascade: 'none'
                },
                plugins: ['checkbox']
            });

            treeDiv.on('changed.jstree', function(e, data) {
                if (!data || !data.selected.length) {
                    return;
                }

                const selectedNode = data.instance.get_node(data.selected[0]);

                data.instance.get_json('#', { flat: true }).forEach(function(node) {
                    if (node.id !== selectedNode.id) {
                        data.instance.deselect_node(node.id);
                    }
                });

                const checkbox = jQuery('#' + selectedNode.id + '_anchor .jstree-checkbox')[0];
                if (checkbox) {
                    varienElementMethods.setHasChanges(checkbox);
                }

                EbayAccountObj.ebayStoreSelectCategory(selectedNode.id);
            });
        },

        // ---------------------------------------

        magentoOrdersListingsModeChange: function() {
            var self = EbayAccountObj;

            if ($('magento_orders_listings_mode').value == 1) {
                $('magento_orders_listings_create_from_date_container').show();
                $('magento_orders_listings_store_mode_container').show();
            } else {
                $('magento_orders_listings_create_from_date_container').hide();
                $('magento_orders_listings_store_mode_container').hide();
                $('magento_orders_listings_store_mode').value = M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_DEFAULT');
            }

            self.magentoOrdersListingsStoreModeChange();
            self.changeVisibilityForOrdersModesRelatedBlocks();
        },

        magentoOrdersListingsStoreModeChange: function() {
            if ($('magento_orders_listings_store_mode').value == M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_LISTINGS_STORE_MODE_CUSTOM')) {
                $('magento_orders_listings_store_id_container').show();
            } else {
                $('magento_orders_listings_store_id_container').hide();
                $('magento_orders_listings_store_id').value = '';
            }
        },

        magentoOrdersListingsOtherModeChange: function() {
            var self = EbayAccountObj;

            if ($('magento_orders_listings_other_mode').value == 1) {
                $('magento_orders_listings_other_create_from_date_container').show();
                $('magento_orders_listings_other_product_mode_container').show();
                $('magento_orders_listings_other_store_id_container').show();
            } else {
                $('magento_orders_listings_other_create_from_date_container').hide();
                $('magento_orders_listings_other_product_mode_container').hide();
                $('magento_orders_listings_other_store_id_container').hide();
                $('magento_orders_listings_other_product_mode').value = M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE');
                $('magento_orders_listings_other_store_id').value = '';
            }

            self.magentoOrdersListingsOtherProductModeChange();
            self.changeVisibilityForOrdersModesRelatedBlocks();
        },

        magentoOrdersListingsOtherProductModeChange: function() {
            if ($('magento_orders_listings_other_product_mode').value == M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_LISTINGS_OTHER_PRODUCT_MODE_IGNORE')) {
                $('magento_orders_listings_other_product_mode_note').hide();
                $('magento_orders_listings_other_product_tax_class_id_container').hide();
                $('magento_orders_listings_other_product_mode_warning').hide();
            } else {
                $('magento_orders_listings_other_product_mode_note').show();
                $('magento_orders_listings_other_product_tax_class_id_container').show();
                $('magento_orders_listings_other_product_mode_warning').show();
            }
        },

        magentoOrdersNumberChange: function() {
            var self = EbayAccountObj;
            self.renderOrderNumberExample();
        },

        renderOrderNumberExample: function() {
            var orderNumber = $('sample_magento_order_id').value;
            if ($('magento_orders_number_source').value == M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_NUMBER_SOURCE_CHANNEL')) {
                orderNumber = $('sample_ebay_order_id').value;
            }

            var marketplacePrefix = '';
            if ($('magento_orders_number_prefix_use_marketplace_prefix').value == 1) {
                marketplacePrefix = $('sample_marketplace_prefix').value;
            }

            orderNumber = $('magento_orders_number_prefix_prefix').value + marketplacePrefix + orderNumber;

            $('order_number_example_container').update(orderNumber);
        },

        magentoOrdersCustomerModeChange: function() {
            var customerMode = $('magento_orders_customer_mode').value;

            if (customerMode == M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_CUSTOMER_MODE_PREDEFINED')) {
                $('magento_orders_customer_id_container').show();
                $('magento_orders_customer_id').addClassName('M2ePro-account-product-id');
            } else {  // M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::ORDERS_CUSTOMER_MODE_GUEST') || M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::ORDERS_CUSTOMER_MODE_NEW')
                $('magento_orders_customer_id_container').hide();
                $('magento_orders_customer_id').value = '';
                $('magento_orders_customer_id').removeClassName('M2ePro-account-product-id');
            }

            var action = (customerMode == M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_CUSTOMER_MODE_NEW')) ? 'show' : 'hide';
            $('magento_orders_customer_new_website_id_container')[action]();
            $('magento_orders_customer_new_group_id_container')[action]();
            $('magento_orders_customer_new_notifications_container')[action]();

            if (action == 'hide') {
                $('magento_orders_customer_new_website_id').value = '';
                $('magento_orders_customer_new_group_id').value = '';
                $('magento_orders_customer_new_notifications').value = '';
            }
        },
        magentoOrdersStatusMappingModeChange: function() {
            // Reset dropdown selected values to default
            $('magento_orders_status_mapping_new').value = M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_STATUS_MAPPING_NEW');
            $('magento_orders_status_mapping_paid').value = M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_STATUS_MAPPING_PAID');
            $('magento_orders_status_mapping_shipped').value = M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_STATUS_MAPPING_SHIPPED');

            var disabled = $('magento_orders_status_mapping_mode').value == M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT');
            $('magento_orders_status_mapping_new').disabled = disabled;
            $('magento_orders_status_mapping_paid').disabled = disabled;
            $('magento_orders_status_mapping_shipped').disabled = disabled;
        },

        changeVisibilityForOrdersModesRelatedBlocks: function() {
            var self = EbayAccountObj;

            if ($('magento_orders_listings_mode').value == 0 && $('magento_orders_listings_other_mode').value == 0) {

                $('magento_block_ebay_accounts_magento_orders_number-wrapper').hide();
                $('magento_orders_number_source').value = M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_NUMBER_SOURCE_MAGENTO');

                $('magento_block_ebay_accounts_magento_orders_customer-wrapper').hide();
                $('magento_orders_customer_mode').value = M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_CUSTOMER_MODE_GUEST');
                self.magentoOrdersCustomerModeChange();

                $('magento_block_ebay_accounts_magento_orders_status_mapping-wrapper').hide();
                $('magento_orders_status_mapping_mode').value = M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_STATUS_MAPPING_MODE_DEFAULT');
                self.magentoOrdersStatusMappingModeChange();

                $('magento_block_ebay_accounts_magento_orders_refund_and_cancellation').hide();

                $('magento_block_ebay_accounts_magento_orders_rules-wrapper').hide();
                $('magento_orders_creation_mode').value = M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_CREATE_CHECKOUT_AND_PAID');
                $('magento_orders_qty_reservation_days').value = 1;

                $('magento_block_ebay_accounts_magento_orders_tax-wrapper').hide();
                $('magento_orders_tax_mode').value = M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::MAGENTO_ORDERS_TAX_MODE_MIXED');
            } else {
                $('magento_block_ebay_accounts_magento_orders_number-wrapper').show();
                $('magento_block_ebay_accounts_magento_orders_customer-wrapper').show();
                $('magento_block_ebay_accounts_magento_orders_status_mapping-wrapper').show();
                $('magento_block_ebay_accounts_magento_orders_rules-wrapper').show();
                $('magento_block_ebay_accounts_magento_orders_tax-wrapper').show();
            }
        },

        // ---------------------------------------

        other_listings_synchronization_change: function() {
            var relatedStoreViews = $('magento_block_ebay_accounts_other_listings_related_store_views-wrapper');

            if (this.value == 1) {
                $('other_listings_mapping_mode_tr').show();
                $('other_listings_mapping_mode').simulate('change');
                if (relatedStoreViews) {
                    relatedStoreViews.show();
                }
            } else {
                $('other_listings_mapping_mode').value = 0;
                $('other_listings_mapping_mode').simulate('change');
                $('other_listings_mapping_mode_tr').hide();
                if (relatedStoreViews) {
                    relatedStoreViews.hide();
                }
            }
        },

        other_listings_mapping_mode_change: function() {
            if (this.value == 1) {
                $('magento_block_ebay_accounts_other_listings_product_mapping-wrapper').show();
            } else {
                $('magento_block_ebay_accounts_other_listings_product_mapping-wrapper').hide();

                $('mapping_sku_mode').value = M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE');
                $('mapping_title_mode').value = M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE');
            }

            $('mapping_sku_mode').simulate('change');
            $('mapping_title_mode').simulate('change');
        },

        synchronization_mapped_change: function() {
            if (this.value == 0) {
                $('settings_button').hide();
            } else {
                $('settings_button').show();
            }
        },

        mapping_sku_mode_change: function() {
            var self = EbayAccountObj,
                attributeEl = $('mapping_sku_attribute');

            $('mapping_sku_priority').hide();
            if (this.value != M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::OTHER_LISTINGS_MAPPING_SKU_MODE_NONE')) {
                $('mapping_sku_priority').show();
            }

            attributeEl.value = '';
            if (this.value == M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::OTHER_LISTINGS_MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, attributeEl);
            }
        },

        mapping_title_mode_change: function() {
            var self = EbayAccountObj,
                attributeEl = $('mapping_title_attribute');

            $('mapping_title_priority').hide();
            if (this.value != M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_NONE')) {
                $('mapping_title_priority').show();
            }

            attributeEl.value = '';
            if (this.value == M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::OTHER_LISTINGS_MAPPING_TITLE_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, attributeEl);
            }
        },

        mapping_item_id_mode_change: function() {
            var self = EbayAccountObj,
                attributeEl = $('mapping_item_id_attribute');

            $('mapping_item_id_priority').hide();
            if (this.value != M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_NONE')) {
                $('mapping_item_id_priority').show();
            }

            attributeEl.value = '';
            if (this.value == M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Account::OTHER_LISTINGS_MAPPING_ITEM_ID_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, attributeEl);
            }
        },

        refreshStoreCategories: function()
        {
            new Ajax.Request(M2ePro.url.get('ebay_account_store_category/refresh'), {
                method: 'post',
                parameters: {
                    account_id: M2ePro.formData.id
                },
                onSuccess: function()
                {
                    EbayAccountObj.renderCategories();
                }
            });
        },

        renderCategories: function()
        {
            new Ajax.Request(M2ePro.url.get('ebay_account_store_category/getTree'), {
                method: 'post',
                parameters: {
                    account_id: M2ePro.formData.id
                },
                onSuccess: function(transport)
                {
                    var categories = JSON.parse(transport.responseText);

                    if (categories.length !== 0) {
                        if (document.getElementById('ebay_store_categories_not_found')) {
                            document.getElementById('ebay_store_categories_not_found').hide();
                        }

                        if (document.getElementById('ebay_store_categories_no_subscription_message')) {
                            document.getElementById('ebay_store_categories_no_subscription_message').hide();
                        }

                        if (document.getElementById('tree-div')) {
                            document.getElementById('tree-div').innerHTML = "";
                        }

                        EbayAccountObj.ebayStoreInitExtTree(categories);
                    }
                }
            });
        }

        // ---------------------------------------
    });

});
