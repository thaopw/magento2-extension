define([
    'jquery',
    'M2ePro/Ebay/Listing/View/Grid',
    'M2ePro/Listing/Moving',
    'M2ePro/Listing/Mapping',
    'Magento_Ui/js/modal/modal',
    'M2ePro/Ebay/PromotedListing/Campaign'
], function (jQuery) {

    window.EbayListingViewSettingsGrid = Class.create(EbayListingViewGrid, {

        // ---------------------------------------

        marketplaceId: null,
        accountId: null,

        // ---------------------------------------

        initialize: function($super, gridId, listingId, marketplaceId, accountId)
        {
            this.marketplaceId = marketplaceId;
            this.accountId = accountId;

            $super(gridId, listingId);
        },

        // ---------------------------------------

        prepareActions: function($super)
        {
            $super();

            this.movingHandler = new ListingMoving(this);
            this.mappingHandler = new ListingMapping(this, 'ebay');

            this.actions = Object.extend(this.actions, {
                editAllSettingsAction: function(id) {
                    this.editSettings(id,
                            M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Template\\Manager::TEMPLATE_ALL_POLICY')
                    );
                }.bind(this),
                editPriceQuantityFormatSettingsAction: function(id) {
                    this.editSettings(id,
                        M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Template\\Manager::TEMPLATE_SELLING_FORMAT')
                    );
                }.bind(this),
                editDescriptionSettingsAction: function(id) {
                    this.editSettings(id,
                        M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Template\\Manager::TEMPLATE_DESCRIPTION')
                    );
                }.bind(this),
                editSynchSettingsAction: function(id) {
                    this.editSettings(id,
                        M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Template\\Manager::TEMPLATE_SYNCHRONIZATION')
                    );
                }.bind(this),
                editShippingSettingsAction: function(id) {
                    this.editSettings(id,
                        M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Template\\Manager::TEMPLATE_SHIPPING')
                    );
                }.bind(this),
                editReturnSettingsAction: function(id) {
                    this.editSettings(id,
                        M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Template\\Manager::TEMPLATE_RETURN_POLICY')
                    );
                }.bind(this),

                editCategorySettingsAction: function(id) {
                    EbayListingCategoryObj.editCategorySettings(id, 'both');
                }.bind(this),

                editMotorsAction: function(id) {
                    this.openMotorsPopup(id);
                }.bind(this),

                remapProductAction: function(id) {
                    this.mappingHandler.openPopUp(id, null, this.listingId);
                }.bind(this),

                managePromotionAction: (function() {
                    PromotionObj.openPromotionPopup(this.getSelectedProductsString());
                }).bind(this),

                managePromotedListingsAction: (() => {
                    window.CampaignObj.openCampaignModal(this.getSelectedProductsArray());
                }),

                movingAction: this.movingHandler.run.bind(this.movingHandler),

                transferringAction: function(id) {
                    this.transferring(id);
                }.bind(this)

            });
        },

        // ---------------------------------------


        tryToMove: function(listingId)
        {
            this.movingHandler.submit(listingId, this.onSuccess)
        },

        onSuccess: function()
        {
            this.unselectAllAndReload();
        },

        // ---------------------------------------

        editSettings: function(id, templateNick)
        {
            this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

            new Ajax.Request(M2ePro.url.get('ebay_template/editListingProductsPolicy'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids : this.selectedProductsIds.join(','),
                    templateNick : templateNick
                },
                onSuccess: function(transport) {

                    var result = transport.responseText;

                    if (+result === 0) {
                        return;
                    }

                    this.unselectAll();

                    var title = this.getPopUpTitle(templateNick, this.getSelectedProductsTitles());

                    if (typeof this.popUp != 'undefined') {
                        var $title = this.popUp.data('mageModal').modal.find('.modal-title');
                        $title.text(title);
                    }

                    this.openPopUp(
                        title,
                        transport.responseText,
                        {
                            buttons: [{
                                text: M2ePro.translator.translate('Cancel'),
                                class: 'action-dismiss',
                                click: function () {
                                    this.closeModal();
                                }
                            }, {
                                text: M2ePro.translator.translate('Save'),
                                class: 'action-primary action-accept',
                                click: function () {
                                    EbayListingProductSettingsObj.save(function (params) {
                                        EbayListingViewSettingsGridObj.saveSettings(params);
                                    });
                                }
                            }],
                            closed: function() {
                                self.selectedProductsIds = [];
                                self.selectedCategoriesData = {};

                                return true;
                            }

                        },
                        'modal_setting_policy_action_dialog'
                    );

                    this.insertHelpLink('modal_setting_policy_action_dialog');

                }.bind(this)
            });
        },

        openMotorsPopup: function(id)
        {
            EbayListingViewSettingsMotorsObj.savedNotes = {};

            this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();
            EbayListingViewSettingsMotorsObj.openAddPopUp(this.selectedProductsIds);
        },

        // ---------------------------------------

        saveSettings: function(savedTemplates)
        {
            var requestParams = {};

            // push information about saved templates into the request params
            // ---------------------------------------
            $H(savedTemplates).each(function(i) {
                requestParams[i.key] = i.value;
            });
            // ---------------------------------------

            // ---------------------------------------
            requestParams['products_ids'] = this.selectedProductsIds.join(',');
            // ---------------------------------------

            new Ajax.Request(M2ePro.url.get('ebay_template/saveListingProductsPolicy'), {
                method: 'post',
                asynchronous: true,
                parameters: requestParams,
                onSuccess: function(transport) {
                    this.popUp.modal('closeModal');
                    this.getGridObj().doFilter();
                }.bind(this)
            });
        },

        // ---------------------------------------

        getSelectedProductsTitles: function()
        {
            if (this.selectedProductsIds.length > 3) {
                return '';
            }

            var title = '';

            // use the names of only first three products for pop up title
            for (var i = 0; i < 3; i++) {
                if (typeof this.selectedProductsIds[i] == 'undefined') {
                    break;
                }

                if (title != '') {
                    title += ', ';
                }

                title += this.getProductNameByRowId(this.selectedProductsIds[i]);
            }

            return title;
        },

        // ---------------------------------------

        getPopUpTitle: function(templateNick, productTitles)
        {
            var title = '',
                templatesNames = {};

            templatesNames[
                M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Template\\Manager::TEMPLATE_RETURN_POLICY')
            ] = M2ePro.translator.translate('Edit Return Policy Setting');
            templatesNames[
                M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Template\\Manager::TEMPLATE_SHIPPING')
            ] = M2ePro.translator.translate('Edit Shipping Policy Setting');
            templatesNames[
                M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Template\\Manager::TEMPLATE_DESCRIPTION')
            ] = M2ePro.translator.translate('Edit Description Policy Setting');
            templatesNames[
                M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Template\\Manager::TEMPLATE_SELLING_FORMAT')
            ] = M2ePro.translator.translate('Edit Selling Policy Setting');
            templatesNames[
                M2ePro.php.constant('\\Ess\\M2ePro\\Model\\Ebay\\Template\\Manager::TEMPLATE_SYNCHRONIZATION')
            ] = M2ePro.translator.translate('Edit Synchronization Policy Setting');

            if (templatesNames[templateNick]) {
                title = templatesNames[templateNick];
            }

            var productTitlesArray = productTitles.split(',');
            if (productTitlesArray.length > 1) {
                productTitles = productTitlesArray.map(function(el) { return el.trim(); }).join('", "');
            }

            if (productTitles) {
                title += ' ' + M2ePro.translator.translate('For') + ' "' + productTitles + '"';
            }

            return title;
        },

        // ---------------------------------------

        transferring: function(id)
        {
            this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();
            this.unselectAll();

            window.EbayListingTransferringObj.popupShow(this.selectedProductsIds);
        },

        // ---------------------------------------

        confirm: function (config) {
            if (config.actions && config.actions.confirm) {
                config.actions.confirm();
            }
        },

        insertHelpLink: function (popUpElementId)
        {
            var modalHeader = jQuery('#'+popUpElementId)
                    .closest('.modal-inner-wrap')
                    .find('h1.modal-title');

            if (modalHeader.has('#popup_template_help_link')) {
                modalHeader.find('#popup_template_help_link').remove();
            }

            var tips = jQuery('#popup_template_help_link');
            modalHeader.append(tips);
            tips.show();
        }

        // ---------------------------------------
    });
});
