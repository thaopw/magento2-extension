define([
    'M2ePro/Plugin/Messages',
    'M2ePro/Amazon/Template/Edit'
], function (MessageObj) {

    window.AmazonTemplateShipping = Class.create(AmazonTemplateEdit,  {

        initialize: function()
        {
            this.setValidationCheckRepetitionValue('M2ePro-shipping-tpl-title',
                M2ePro.translator.translate('The specified Title is already used for other Policy. ' +
                    'Policy Title must be unique.'),
                'Amazon\\Template\\Shipping', 'title', 'id',
                M2ePro.formData.id
            );
        },

        initObservers: function()
        {
            $('account_id').observe('change', this.accountChange.bind(this)).simulate('change');
        },

        duplicateClick: function($headId)
        {
            this.showConfirmMsg = false;

            this.setValidationCheckRepetitionValue('M2ePro-shipping-tpl-title',
                M2ePro.translator.translate('The specified Title is already used for other Policy. Policy Title must be unique.'),
                'Amazon\\Template\\Shipping', 'title', 'id', ''
            );

            CommonObj.duplicateClick($headId, M2ePro.translator.translate('Add Shipping Policy'));
        },

        submitForm: function(url, newWindow = false)
        {
            var form = $('edit_form');
            form.target = newWindow ? '_blank' : '_self';

            new Ajax.Request(url, {
                method: 'post',
                parameters: this.collectFormData(form),
                onSuccess: function (transport) {
                    var resultResponse = transport.responseText.evalJSON();
                    if (resultResponse.status === true) {
                        window.location = resultResponse.url;
                    } else {
                        MessageObj.addError(M2ePro.translator.translate('Policy Saving Error'));
                    }
                }
            });
        },

        saveFormUsingAjax: function() {
            new Ajax.Request(M2ePro.url.get('formSubmit'), {
                method: 'post',
                parameters: this.collectFormData($('edit_form')),
                onSuccess: function(transport) {
                    var result = transport.responseText.evalJSON();

                    if (result.status) {
                        window.close();
                    } else {
                        console.error('Policy Saving Error');
                    }
                }
            });
        },

        /**
         * Collecting form data with ignoring disabled fields
         */
        collectFormData: function (form) {
            var formData = {};
            jQuery(form).find ('input, select').each(function (){
                formData[this.name] = jQuery(this).val();
            });

            return formData;
        },

        accountChange: function()
        {
            var accountIdSelect = $('account_id');
            var templateIdSelect = $('template_id');
            var refreshTemplatesButton = $('refresh_templates');
            var placeholderOptions = '<option></option>';

            if (!accountIdSelect.hasAttribute('disabled')) {
                templateIdSelect.update();
                templateIdSelect.insert(placeholderOptions);
            }

            if (!accountIdSelect.value) {
                templateIdSelect.setAttribute("disabled", "disabled");
                refreshTemplatesButton.addClassName('disabled');
            } else {
                templateIdSelect.removeAttribute('disabled');
                refreshTemplatesButton.removeClassName('disabled');
            }
        },

        refreshTemplateShipping: function()
        {
            new Ajax.Request(M2ePro.url.get('amazon_template_shipping/refresh'), {
                method: 'post',
                parameters: {
                    account_id: $('account_id').value
                },
                onSuccess: function()
                {
                    AmazonTemplateShippingObj.renderTemplates();
                }
            });
        },

        renderTemplates: function()
        {
            new Ajax.Request(M2ePro.url.get('amazon_template_shipping/getTemplates'), {
                method: 'post',
                parameters: {
                    account_id: $('account_id').value
                },
                onSuccess: function(transport)
                {
                    var select = $('template_id');
                    var options = '<option></option>';
                    var firstItem = null;
                    var currentValue = select.value;

                    var data = transport.responseText.evalJSON(true);

                    data.each(function(item) {
                        options += `<option value="${item.template_id}">${item.title}</option>`;

                        if (!firstItem) {
                            firstItem = item;
                        }
                    });

                    select.update();
                    select.insert(options);

                    if (currentValue !== '') {
                        select.value = currentValue;
                    } else if (typeof id !== 'undefined' && M2ePro.formData[id] > 0) {
                        select.value = M2ePro.formData[id];
                    } else {
                        select.value = firstItem.id;
                    }
                }
            });
        }
    });
});
