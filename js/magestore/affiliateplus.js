var MapAccount = Class.create();
MapAccount.prototype = {
    initialize: function(changeCustomerUrl){
        
        this.changeCustomerUrl = changeCustomerUrl;
       
    },
	
	changeCustomer : function(customerId)
	{
		var url = this.changeCustomerUrl;
		
		url += 'customer_id/' + customerId;

		new Ajax.Updater('map-customer-info',url,{method: 'get', onComplete: function(){updateAccountInfo();} ,onFailure: ""}); 	
		
	}
}

function updateAccountInfo()
{
	$('name').value = $('map_customer_name').value;
	$('email').value = $('map_customer_email').value;
	$('customer_id').value = $('map_customer_id').value;
}

function affiliateResetForm()
{
	location.href='';
}

/* Affiliate Credit JS */
function checkOutLoadAffiliateCredit(json) {
    if ($('affiliateplus_container') != null) {
        $('affiliateplus_container').remove();
    }
    var formElement = getPaymentFormElement();
    formElement.insert({
        before: json.html
    });
}
function getPaymentFormElement() {
    var formEl = $('checkout-payment-method-load');
    if (typeof formEl.down('#checkout-payment-method-load') == 'undefined') {
        return formEl;
    } else {
        return formEl.down('#checkout-payment-method-load');
    }
}

function onLoadAffiliateCreditForm() {
    $('affiliateplus_credit').disabled = false;
    $('affiliateplus_container').select('input').each(function(field){
        field.disabled = false;
    });
}

function changeUseAffiliateCredit(el) {
    var url = $('affiliate_cache_url').value.replace('/creditPost', '/changeUseCredit');
    var params = 'affiliatepluscredit=';
    if (el.checked) {
        params += '1';
    }
    var formEl = getPaymentFormElement();
    formEl.hide();
    $('affiliateplus_container').down('dd.affiliateplus_credit').hide();
    $('affiliateplus_credit_ajaxload').show();
    $('affiliateplus_credit').disabled = true;
    new Ajax.Request(url, {
        method: 'post',
        postBody: params,
        parameters: params,
        onComplete: function(response) {
            if (response.responseText.isJSON()) {
                var res = response.responseText.evalJSON();
                if (res.updatepayment) {
                    if (typeof shippingMethod != 'undefined') {
                        shippingMethod.save();
                    } else if (typeof billing != 'undefined'){
                        billing.save();
                    } else {
                        save_address_information(save_address_url);
                    }
                } else if (res.html) {
                    var container = $('affiliateplus_container');
                    container.innerHTML = res.html;
                    onLoadAffiliateCreditForm();
                    formEl.show();
                } else {
                    formEl.show();
                    $('affiliateplus_container').down('dd.affiliateplus_credit').show();
                    $('affiliateplus_credit_ajaxload').hide();
                }
            } else {
                formEl.show();
                $('affiliateplus_container').down('dd.affiliateplus_credit').show();
                $('affiliateplus_credit_ajaxload').hide();
            }
            if (typeof(save_shipping_method) != 'undefined') {
                save_shipping_method(shipping_method_url);
            }
        }
    });
}

function showAffiliateCreditInput(el) {
    var parent = Element.extend(el.parentNode);
    el.hide();
    parent.down('.credit_input').show();
    parent.down('.credit_input input').focus();
}

function enterUpdateAffiliateCreditInput(el, e) {
    if (e.keyCode == 13) {
        updateAffiliateCreditInput(el);
    }
}

function isNotEnterKeyPressed(e) {
    if (e.keyCode != 13) {
        return true;
    }
    return false;
}

function updateAffiliateCreditInput(el) {
    var parent = Element.extend(el.parentNode);
    var url = $('affiliate_cache_url').value.replace('/creditPost', '/changeCredit');
    var params = 'affiliatepluscredit=' + parent.down('input').value;
    
    var formEl = getPaymentFormElement();
    formEl.hide();
    $('affiliateplus_container').down('dd.affiliateplus_credit').hide();
    $('affiliateplus_credit_ajaxload').show();
    $('affiliateplus_credit').disabled = true;
    new Ajax.Request(url, {
        method: 'post',
        postBody: params,
        parameters: params,
        onComplete: function(response) {
            if (response.responseText.isJSON()) {
                var res = response.responseText.evalJSON();
                if (res.updatepayment) {
                    if (typeof shippingMethod != 'undefined') {
                        shippingMethod.save();
                    } else if (typeof billing != 'undefined'){
                        billing.save();
                    } else {
                        save_address_information(save_address_url);
                    }
                } else if (res.html) {
                    var container = $('affiliateplus_container');
                    container.innerHTML = res.html;
                    onLoadAffiliateCreditForm();
                    formEl.show();
                } else {
                    formEl.show();
                    $('affiliateplus_container').down('dd.affiliateplus_credit').show();
                    $('affiliateplus_credit_ajaxload').hide();
                }
            } else {
                formEl.show();
                $('affiliateplus_container').down('dd.affiliateplus_credit').show();
                $('affiliateplus_credit_ajaxload').hide();
            }
            if (typeof(save_shipping_method) != 'undefined') {
                save_shipping_method(shipping_method_url);
            }
        }
    });
}
