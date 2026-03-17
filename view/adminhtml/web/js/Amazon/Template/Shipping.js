define([
    'jquery',
    'mage/translate',
    'M2ePro/Amazon/Template/Edit'
], function ($, $t) {

    window.AmazonTemplateShipping = Class.create(AmazonTemplateEdit, {
        initialize: function (currentId) {

            this.accountSelect = $('#account_select');
            this.accountHiddenInput = $('#account_id');
            this.templateSelect = $('#template_id');
            this.refreshTemplatesButton = $('#refresh_templates');

            this.modeSelect = $('#template_mode');
            this.magentoAttributeRow = $('#magento_attribute_tr');
            this.amazonTemplateRow = $('#amazon_template_tr');

            this.setValidationCheckRepetitionValue(
                    'M2ePro-shipping-tpl-title',
                    $t('The specified Title is already used for other Policy. Policy Title must be unique.'),
                    'Amazon\\Template\\Shipping', 'title', 'id',
                    currentId
            );

            this.initObservers();
        },

        initObservers: function () {
            this.accountSelect.on('change', this.accountChange.bind(this));
            this.accountSelect.trigger('change');

            this.refreshTemplatesButton.on('click', this.refreshTemplateShipping.bind(this));

            this.modeSelect.on('change', this.modeChange.bind(this));
            this.modeSelect.trigger('change');
        },

        duplicateClick: function ($headId) {
            this.showConfirmMsg = false;

            this.setValidationCheckRepetitionValue(
                    'M2ePro-shipping-tpl-title',
                    $t('The specified Title is already used for other Policy. Policy Title must be unique.'),
                    'Amazon\\Template\\Shipping', 'title', 'id', ''
            );

            CommonObj.duplicateClick($headId, $t('Add Shipping Policy'));
        },

        accountChange: function () {
            this.accountHiddenInput.val(this.accountSelect.val());

            if (!this.accountHiddenInput.val()) {
                this.templateSelect.prop('disabled', true);
                this.refreshTemplatesButton.addClass('disabled');
            } else {
                this.templateSelect.prop('disabled', false);
                this.refreshTemplatesButton.removeClass('disabled');
                this.refreshTemplateShipping();
            }
        },

        modeChange: function () {
            const mode = Number(this.modeSelect.val());

            this.magentoAttributeRow.hide();
            this.amazonTemplateRow.hide();

            if (mode === 1) {
                this.amazonTemplateRow.show();
            }

            if (mode === 2) {
                this.magentoAttributeRow.show();
            }
        },

        refreshTemplateShipping: function () {
            new Ajax.Request(M2ePro.url.get('amazon_template_shipping/refresh'), {
                method: 'post',
                parameters: {
                    account_id: this.accountHiddenInput.val()
                },
                onSuccess: this.renderTemplates.bind(this)
            });
        },

        renderTemplates: function () {
            new Ajax.Request(M2ePro.url.get('amazon_template_shipping/getTemplates'), {
                method: 'post',
                parameters: {
                    account_id: this.accountHiddenInput.val()
                },
                onSuccess: (transport) => {
                    const newOptions = transport.responseText.evalJSON(true);

                    const currentVal = this.templateSelect.val();

                    let optionsHtml = '';
                    newOptions.forEach(item => {
                        optionsHtml += `<option value="${item.template_id}">${item.title}</option>`;
                    });

                    this.templateSelect.html(optionsHtml);
                    this.templateSelect.val(currentVal);
                    this.templateSelect.trigger('change');
                }
            });
        }
    });
});
