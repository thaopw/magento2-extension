define([
    'mage/translate',
    'M2ePro/Listing/Other/Grid',
    'M2ePro/Amazon/Listing/Removing'
], function ($t) {
    window.AmazonListingOtherGrid = Class.create(ListingOtherGrid, {

        afterPrepareAction: function()
        {
            this.removingHandler = new AmazonListingOtherRemoving(this);
        },

        // ---------------------------------------

        tryToMove: function (listingId)
        {
            this.movingHandler.submit(listingId, this.onSuccess)
        },

        onSuccess: function () {
            this.unselectAllAndReload();
        },

        // ---------------------------------------

        massActionSubmitClick: function()
        {
            if (this.validateItemsForMassAction() === false) {
                return;
            }

            const self = this;
            let selectAction = true;
            $$('select#'+self.gridId+'_massaction-select option').each(function(o) {
                if (o.selected && o.value == '') {
                    self.alert($t('Please select Action.'));
                    selectAction = false;
                    return;
                }
            });

            if (!selectAction) {
                return;
            }

            self.scrollPageToTop();

            const selectedAction = $('amazonListingUnmanagedGrid_massaction-select').value;

            if (selectedAction === 'removing') {
                self.confirm({
                    title: $t('Remove Item(s) from Amazon'),
                    content: '<p>' + $t('You are about to permanently remove the selected item(s) from your Amazon account. This action will delete the item(s) from the Amazon channel and cannot be undone.') + '</p>'
                            + '<br><p>' + $t('Are you sure you want to proceed?') + '</p>',
                    actions: {
                        confirm: () => {
                            self.actions['removingAction']();
                        },
                        cancel: function () {}
                    }
                });

                return;
            }

            self.confirm({
                actions: {
                    confirm: function () {
                        $$('select#'+self.gridId+'_massaction-select option').each(function(o) {

                            if (!o.selected) {
                                return;
                            }

                            if (!o.value || !self.actions[o.value + 'Action']) {
                                self.alert($t('Please select Action.'));
                                return;
                            }

                            self.actions[o.value + 'Action']();

                        });
                    },
                    cancel: function () {
                        return false;
                    }
                }
            });
        },
    });
});
