/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Rma
 * @copyright  Copyright (c) 2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

var AWRMAOrdersControl = Class.create({
    /**
     * Class initialization method. Sets default values
     * for all class variables. Url for AJAX Request can
     * be provided on class creating
     */
    initialize: function(globalObject, awrmaNewForm, getItemsUrl) {
        /* Id of select with orders */
        this.orderSelectId = 'awrma-order';
        /* Id of order items container */
        this.orderItemsContainer = 'awrma-items';
        /* Id of tr with no order message */
        this.orderItemsNoOrder = 'awrma-items-noorder';
        /*Id of tr with no items message */
        this.orderItemsNoItems = 'awrma-items-noitems';
        /* Id of tr with loading message */
        this.orderItemsLoading = 'awrma-items-loading';
        /* Id of tr with error message */
        this.orderItemsError = 'awrma-items-error';
        /* Id of service rows container */
        this.orderItemsHeadContainer = 'awrma-items-head';
        /* Class of inputs with items count */
        this.itemsCountClass = '.awrma-items-count';
        /* Order item row id */
        this.orderItemRow = 'order-item-row-';
        /* Form id */
        this.formId = 'awrma-new-form';
        /* Id of count field */
        this.orderItemCount = 'orderitem-count';
        /* Id of submit button */
        this.submitId = 'awrma-new-submit';
        /* Id of ordered items table */
        this.orderedItemsTableId = 'awrma-items-ordered-table';

        /* No remove class for synchronization */
        this.noRemoveClass = 'awrma-js-noremove';

        /* Varien Form object */
        this.form = typeof(awrmaNewForm) == 'undefined' ? null : awrmaNewForm;
        /* sync items */
        this.syncOrderItems = null;
        /* Registering global object */
        this.global = typeof(globalObject) == 'undefined' ? window : globalObject;
        /* Url for JSON request */
        this.getItemsUrl = typeof(getItemsUrl) == 'undefined' ? '/rma/customer_rma/getitemsfororder/' : getItemsUrl;
        /* Secure/Unsecure URLs fix */
        this.getItemsUrl =  this.getItemsUrl.replace(/^http[s]{0,1}/, window.location.href.replace(/:[^:].*$/i, ''));

        /* Initial actions */
        $(this.formId).onsubmit = this.validateForm.bind(this);
    },

    /* moves all service rows to head container */
    hideServiceRows: function() {
        $(this.orderItemsHeadContainer).appendChild($(this.orderItemsNoOrder).hide());
        $(this.orderItemsHeadContainer).appendChild($(this.orderItemsNoItems).hide());
        $(this.orderItemsHeadContainer).appendChild($(this.orderItemsLoading).hide());
        $(this.orderItemsHeadContainer).appendChild($(this.orderItemsError).hide());
    },

    /* shows row with "no order selected" message */
    showNoOrderRow: function() {
        this.hideServiceRows();
        $(this.orderItemsContainer).update('');
        $(this.orderItemsContainer).appendChild($(this.orderItemsNoOrder).show());
        decorateTable(this.orderedItemsTableId);
    },

    /* shows row with "no items selected" message */
    showNoItemsRow: function() {
        this.hideServiceRows();
        $(this.orderItemsContainer).update('');
        $(this.orderItemsContainer).appendChild($(this.orderItemsNoItems).show());
        decorateTable(this.orderedItemsTableId);
    },

    /* remove item row */
    removeItem: function(itemId) {
        $(itemId).remove();
        if($(this.orderItemsContainer).empty())
            this.showNoItemsRow();
        decorateTable(this.orderedItemsTableId);
    },

    /* shows row with ajax loader */
    showLoadingLine: function() {
        this.hideServiceRows();
        $(this.orderItemsContainer).update('');
        $(this.orderItemsContainer).appendChild($(this.orderItemsLoading).show());
        decorateTable(this.orderedItemsTableId);
    },

    /* shows row with ajax error message */
    showAjaxError: function() {
        this.hideServiceRows();
        $(this.orderItemsContainer).update('');
        $(this.orderItemsContainer).appendChild($(this.orderItemsError).show());
        $(this.orderSelectId).enable();
        decorateTable(this.orderedItemsTableId);
    },

    /* observer to order select */
    orderChanged: function() {
        var orderIncrementId = $(this.orderSelectId).value;
        if(orderIncrementId == '') {
            this.showNoOrderRow();
        } else {
            this.loadItemsForOrder(orderIncrementId);
        }
    },

    /* Validates all items and call standart validate if no errors */
    validateForm: function() {
        $(this.submitId).addClassName('disabled').writeAttribute('disabled', 'disabled');
        var chPassed = true;
        $$('#'+this.orderItemsContainer+' '+this.itemsCountClass).each(function(element) {
            if(!this.validateItemCount('change', element))
                chPassed = false;
        }, this);

        if(!this.form || !this.form.validator || !this.form.validator.validate()) {
            $(this.submitId).removeClassName('disabled').writeAttribute('disabled', null);
            return false;
        }

        if(chPassed)
            return this.form.submit();
        else {
            $(this.submitId).removeClassName('disabled').writeAttribute('disabled', null);
            return false;
        }
    },

    validateItemCount: function(event, element) {
        if(typeof(element) == 'undefined') element = this;
        
        /* add error message */
        var showAdvice = function(elmId, message) {
            removeAdvice(elmId);

            $(elmId).addClassName('validation-failed');

            var advice = '<div class="validation-advice awrma-advice" id="advice-' + elmId +'" style="display:none">' + message + '</div>';

            var container = $(elmId).up();
            container.insert({bottom: advice});
            new Effect.Appear('advice-'+elmId, {duration : 1});

            return false;
        }

        /* removes advice message */
        var removeAdvice = function(elmId) {
            $(elmId).removeClassName('validation-failed');
            
            if($('advice-'+elmId)) $('advice-'+elmId).remove();

            return true;
        }

        var maxCount = $(element.identify()+'-maxcount').value;
        var value = parseInt(element.value);

        if(value != '') {
            if(!isNaN(value)) {
                if(value < 1 || value > maxCount)
                    return showAdvice($(element).identify(), 'Wrong quantity');
                else
                    element.value = value;
            } else {
                return showAdvice($(element).identify(), 'Not a number');
            }
        } else {
            return showAdvice($(element).identify(), 'Can\'t be empty');
        }

        return removeAdvice($(element).identify());
    },

    /* returns self object name */
    getSelfObjectName: function() {
        for(var name in this.global) {
            if(this.global[name] == this)
                return name;
        }
        
        return false;
    },

    /* synchronize items in form with items in session */
    syncItems: function(items) {
        if(this.syncOrderItems == null && typeof(items) != 'undefined') {
            this.syncOrderItems = items;
        } else if (this.syncOrderItems) {
            for(var key in this.syncOrderItems)
                if($(this.orderItemRow+key)) {
                    $(this.orderItemRow+key).addClassName(this.noRemoveClass);
                    $(this.orderItemCount+key).value = this.syncOrderItems[key];
                }
            $$('#'+this.orderItemsContainer+'>*').each(function(element) {
                if(element.hasClassName(this.noRemoveClass))
                    element.removeClassName(this.noRemoveClass)
                else
                    element.remove();
            }, this);
            this.syncOrderItems = null;
        }
    },

    /* add onchange handler to validate items count */
    observeItemsCount: function() {
        if(this.getSelfObjectName())
            $$(this.itemsCountClass).each(function(obj) {
                obj.observe('change', this.global[this.getSelfObjectName()].validateItemCount);
            }, this);
    },

    /* function loads all items for order by increment id */
    loadItemsForOrder: function(orderIncrementId) {
        this.showLoadingLine();
        var awrmaoco = this;
        $(this.orderSelectId).disable();
        new Ajax.Request(this.getItemsUrl, {
            method: 'get',
            parameters: {
                incrementid: orderIncrementId
            },
            onSuccess: function(transport) {
                awrmaoco.hideServiceRows();
                var items = transport.responseText.evalJSON(true);
                if(typeof items == 'object') {
                    $(items).each(function(row) {
                        $(awrmaoco.orderItemsContainer).insert({bottom: row});
                    });
                    $(awrmaoco.orderSelectId).enable();
                    awrmaoco.observeItemsCount();
                    awrmaoco.syncItems();
                    decorateTable(awrmaoco.orderedItemsTableId);
                } else {
                    awrmaoco.showAjaxError();
                }
            },
            onFailure: function() {
                awrmaoco.showAjaxError();
            },
            onException: function() {
                awrmaoco.showAjaxError();
            }
        });
    }
});

/**
 * Comment form control
 */
var AWRMACommentFormControl = Class.create({
    initialize: function(vf) {
        /* Varien Form */
        this.form = vf;
        /* Comment form id */
        this.commentFormId = 'awrma-comment-form';
        /* Comment form submit id */
        this.submitComment = 'awrma-comment-submit';

        $(this.commentFormId).onsubmit = this.validateForm.bind(this);
    },

    validateForm: function() {
        $(this.submitComment).addClassName('disabled').writeAttribute('disabled', 'disabled');

        if(!this.form || !this.form.validator || !this.form.validator.validate()) {
            $(this.submitComment).removeClassName('disabled').writeAttribute('disabled', null);
            return false;
        }

        return this.form.submit();
    }
});

/**
 * Admin RMA Form control
 */
var AWRMAAdminRmaFormControl = Class.create({
    initialize: function(globalObject, vf) {
        /* Varien Form object */
        this.form = vf;
        /* Form Id */
        this.formId = vf.formId;
        /* Class of inputs with items count */
        this.itemsCountClass = '.awrma-items-count';
        /* Submit button Id*/
        this.submitId = 'awrma-save-button';
        /* Save and cont Id */
        this.saveAndContinueEditId = 'awrma-save-and-continue';
        /* Print */
        this.printId = 'awrma-print';
        /* Registering global object */
        this.global = typeof(globalObject) == 'undefined' ? window : globalObject;

        this.submitButtons = new Array(this.submitId, this.saveAndContinueEditId, this.printId);
    },

    disableSubmitButtons: function() {
        for(var i = 0; i < this.submitButtons.length; i++)
            if($(this.submitButtons[i]))
                $(this.submitButtons[i]).addClassName('disabled').writeAttribute('disabled', 'disabled');
    },

    enableSubmitButtons: function() {
        for(var i = 0; i < this.submitButtons.length; i++)
            if($(this.submitButtons[i]))
                $(this.submitButtons[i]).removeClassName('disabled').writeAttribute('disabled', null);
    },

    validateForm: function() {
        this.disableSubmitButtons();
        var chPassed = true;
        $$('#'+this.formId+' '+this.itemsCountClass).each(function(element) {
            if(!this.validateItemCount('change', element))
                chPassed = false;
        }, this);

        if(!this.form || !this.form.validator || !this.form.validator.validate()) {
            this.enableSubmitButtons();
            return false;
        }

        if(chPassed)
            return this.form.submit();
        else {
            this.enableSubmitButtons();
            return false;
        }
    },

    validateItemCount: function(event, element) {
        if(typeof(element) == 'undefined') element = this;

        /* add error message */
        var showAdvice = function(elmId, message) {
            removeAdvice(elmId);

            $(elmId).addClassName('validation-failed');

            var advice = '<div class="validation-advice awrma-advice" id="advice-' + elmId +'" style="display:none">' + message + '</div>';

            var container = $(elmId).up();
            container.insert({bottom: advice});
            new Effect.Appear('advice-'+elmId, {duration : 1});

            return false;
        }

        /* removes advice message */
        var removeAdvice = function(elmId) {
            $(elmId).removeClassName('validation-failed');

            if($('advice-'+elmId)) $('advice-'+elmId).remove();

            return true;
        }

        var maxCount = $(element.identify()+'-maxcount').value;
        var value = parseInt(element.value);

        if(value != '') {
            if(!isNaN(value)) {
                if(value < 1 || value > maxCount)
                    return showAdvice($(element).identify(), 'Wrong quantity');
                else
                    element.value = value;
            } else {
                return showAdvice($(element).identify(), 'Not a number');
            }
        } else {
            return showAdvice($(element).identify(), 'Can\'t be empty');
        }

        return removeAdvice($(element).identify());
    },

    /* add onchange handler to validate items count */
    observeItemsCount: function() {
        if(this.getSelfObjectName())
            $$(this.itemsCountClass).each(function(obj) {
                obj.observe('change', this.global[this.getSelfObjectName()].validateItemCount);
            }, this);
    },

    /* returns self object name */
    getSelfObjectName: function() {
        for(var name in this.global) {
            if(this.global[name] == this)
                return name;
        }

        return false;
    }
});
