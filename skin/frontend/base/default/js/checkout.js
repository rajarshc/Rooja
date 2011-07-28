
var Shipping = Class.create();
Shipping.prototype = {
    initialize: function(form, addressUrl, saveUrl, methodsUrl){
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }
        this.addressUrl = addressUrl;
        this.saveUrl = saveUrl;
        this.methodsUrl = methodsUrl;
        this.onAddressLoad = this.fillForm.bindAsEventListener(this);
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    $("shipping:country_id").observe("change",this.updateCountry.bind(this));
}, 

updateCountry: function(object){
	  review.loader();
	  var data = {country_id:$("shipping:country_id").value};
	  var request = new Ajax.Request(
	        updateCountryUrl,
           {
               method:'post',
               parameters:data,
                onComplete:function(transport){
                    var json = transport.responseText.evalJSON(true);
                    $("reviewCheckout").update(json.review);
                    $("checkoutloader").remove();
               	 $("shipping_method_section").update(json.shipping_method);

               }
         });
},

    setAddress: function(addressId){
        if (addressId) {
            request = new Ajax.Request(
                this.addressUrl+addressId,
                {method:'get', onSuccess: this.onAddressLoad, onFailure: checkout.ajaxFailure.bind(checkout)}
            );
        }
        else {
            this.fillForm(false);
        }
    },

    newAddress: function(isNew){
        if (isNew) {
            this.resetSelectedAddress();
            Element.show('shipping-new-address-form');
        } else {
            Element.hide('shipping-new-address-form');
        }
        shipping.setSameAsBilling(false);
    },

    resetSelectedAddress: function(){
        var selectElement = $('shipping-address-select')
        if (selectElement) {
            selectElement.value='';
        }
    },

    fillForm: function(transport){
        var elementValues = {};
        if (transport && transport.responseText){
            try{
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                elementValues = {};
            }
        }
        else{
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(this.form);
        for (var elemIndex in arrElements) {
            if (arrElements[elemIndex].id) {
                var fieldName = arrElements[elemIndex].id.replace(/^shipping:/, '');
                arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
                if (fieldName == 'country_id' && shippingForm){
                    shippingForm.elementChildLoad(arrElements[elemIndex]);
                }
            }
        }
    },

    setSameAsBilling: function(flag) {
        $('shipping:same_as_billing').checked = flag;
// #5599. Also it hangs up, if the flag is not false
//        $('billing:use_for_shipping_yes').checked = flag;
        if (flag) {
            this.syncWithBilling();
        }
    },

    syncWithBilling: function () {
        $('billing-address-select') && this.newAddress(!$('billing-address-select').value);
        $('shipping:same_as_billing').checked = true;
        if (!$('billing-address-select') || !$('billing-address-select').value) {
            arrElements = Form.getElements(this.form);
            for (var elemIndex in arrElements) {
                if (arrElements[elemIndex].id) {
                    var sourceField = $(arrElements[elemIndex].id.replace(/^shipping:/, 'billing:'));
                    if (sourceField){
                        arrElements[elemIndex].value = sourceField.value;
                    }
                }
            }
            //$('shipping:country_id').value = $('billing:country_id').value;
            shippingRegionUpdater.update();
            $('shipping:region_id').value = $('billing:region_id').value;
            $('shipping:region').value = $('billing:region').value;
            //shippingForm.elementChildLoad($('shipping:country_id'), this.setRegionValue.bind(this));
        } else {
            $('shipping-address-select').value = $('billing-address-select').value;
        }
    },

    setRegionValue: function(){
        $('shipping:region').value = $('billing:region').value;
    },

    save: function(){
        if (checkout.loadWaiting!=false) return;
        var validator = new Validation(this.form);
        if (validator.validate()) {
            checkout.setLoadWaiting('shipping');
            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method:'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(transport){
        checkout.setLoadWaiting(false);
    },

    nextStep: function(transport){
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.error){
            if ((typeof response.message) == 'string') {
                alert(response.message);
            } else {
                if (window.shippingRegionUpdater) {
                    shippingRegionUpdater.update();
                }
                alert(response.message.join("\n"));
            }

            return false;
        }

        checkout.setStepResponse(response);

        /*
        var updater = new Ajax.Updater(
            'checkout-shipping-method-load',
            this.methodsUrl,
            {method:'get', onSuccess: checkout.setShipping.bind(checkout)}
        );
        */
        //checkout.setShipping();
    }
}
                                  
//shipping method
var Review = Class.create();
Review.prototype = {
 initialize: function(updateUrl,agre,updateCartUrl){
     this.updateUrl = updateUrl;
     this.updateCartUrl = updateCartUrl;
   //  console.log(updateCartUrl);
     
 },
 
setUpdateCartUrl: function(updateCartUrl){
	this.updateCartUrl = updateCartUrl;
},
 
 save: function(){
	// First validate the form
		var form = new VarienForm('checkoutform');

		if(!form.validator.validate())	{
			//Event.stop(e);				
		}
		else	{
			 form.submit();
		} 
 },
 
 loader: function(){
	 
	 var parentNode = $("reviewCheckout").parentNode;
	 var loaderDiv = document.createElement('div');
	 loaderDiv.addClassName('checkoutloader');
	 loaderDiv.setAttribute("id","checkoutloader");
	 loaderDiv.setStyle({
		 width: $("reviewCheckout").getStyle("width"),
		 height: $("reviewCheckout").getStyle("height"),
		 marginTop:"-"+$("reviewCheckout").getStyle("height") 
		 });
	 parentNode.appendChild(loaderDiv);
	 
	 loaderDiv.setStyle({opacity:0.5});
	
	
	 
 },
 
 loaderCart: function(){
	 var parentNode = $$(".cart").first();
	 var loaderDiv = document.createElement('div');
	 loaderDiv.addClassName('checkoutloader');
	 loaderDiv.setAttribute("id","cartLoader");
	 loaderDiv.setStyle({
		 width: $$(".cart").first().getStyle("width"),
		 height: $$(".cart").first().getStyle("height"),
		 marginTop:"-"+$$(".cart").first().getStyle("height") 
		 });
	 parentNode.appendChild(loaderDiv);
	 
	 loaderDiv.setStyle({opacity:0.5}); 
 },
 
 decreaseItem: function(item_id){
	this.loader();
	this.loaderCart();
	var parameters = new Object();
	parameters.type = "decrease";
	parameters.item_id = item_id;
	 var request = new Ajax.Request(
			  updateCartUrl,
            {
	            method:'post',
                parameters:parameters,
                 onComplete:function(transport){
                	 var json = transport.responseText.evalJSON(true);
                	 $("reviewCheckout").update(json.review);
                	 $("shipping_method_section").update(json.shipping_method);

                 	 $("checkoutloader").remove();
                	 review.updateCart(json.cart);
                }
            }
        ); 
 }
 ,
 
 increaseItem: function(item_id){
		this.loader();
		this.loaderCart();  
		var parameters = new Object();
		parameters.type = "increase";
		parameters.item_id = item_id;
		 var request = new Ajax.Request(
				  updateCartUrl,
	            {
		            method:'post',
	                parameters:parameters,
	                 onComplete:function(transport){

//	                	 console.log(transport);
	                	 var json = transport.responseText.evalJSON(true);
	                	 $("reviewCheckout").update(json.review);
	                	 $("shipping_method_section").update(json.shipping_method);
	                 	 $("checkoutloader").remove();
	                	 review.updateCart(json.cart);
	                }
	            }
	        ); 
	 
 },

 
     updateReview: function(transport){
    	 $("reviewCheckout").update(transport.responseText);
 	 $("checkoutloader").remove();
 	
 }
 ,
 
 updateCart: function(content){
	      $$(".cart").first().update(content);
	 	 $("cartLoader").remove();
	 	
	 }
	 ,
 
 loadReview: function(transport){
 	  
	 	
	 	 var request = new Ajax.Request(
	 			  this.updateUrl,
	             {
	                 method:'post',
	                 onComplete:function(transport){
	                	
	                	 $("reviewCheckout").setStyle({opacity:1});
	                	 $("reviewCheckout").update(transport.responseText);
	                 }
	             }
	         ); 
	
	 
	 	 
	 
	 	
	 }

 
}

// payment
var Payment = Class.create();
Payment.prototype = {  
    beforeInitFunc:$H({}),
    afterInitFunc:$H({}),
    beforeValidateFunc:$H({}),
    afterValidateFunc:$H({}),
    initialize: function(form, saveUrl){
    	
        this.form = form;
        this.saveUrl = saveUrl;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },

    addBeforeInitFunction : function(code, func) {
        this.beforeInitFunc.set(code, func);
    },

    beforeInit : function() {
        (this.beforeInitFunc).each(function(init){
           (init.value)();;
        });
    },

    init : function () {
    	
        this.beforeInit(); 
   
        var elements  = $$("#checkout-payment-method-load input");
//        console.log(elements);
        var method = null;
        for (var i=0; i<elements.length; i++) {
            if (elements[i].name=='payment[method]') {
                if (elements[i].checked) {
                    method = elements[i].value;
                }
                if (i==0)
                	{
                		method = elements[i].setAttribute("checked",true);
                		
                		this.switchMethod(elements[i].value);
                	}
            } else {
                elements[i].disabled = true;
            }
            elements[i].setAttribute('autocomplete','off');
        }
        if (method) { 
        	
        	
        	
        	
        }
        
        this.afterInit();
    },

    addAfterInitFunction : function(code, func) {
        this.afterInitFunc.set(code, func);
    },

    afterInit : function() {
        (this.afterInitFunc).each(function(init){
            (init.value)();
        });
    },

    
    initMethod: function(){
    	
    	
    },
    
    switchMethod: function(method){
    	
    	
        if (this.currentMethod && $('payment_form_'+this.currentMethod)) {
            this.changeVisible(this.currentMethod, true);
        }
        if ($('payment_form_'+method)){
            this.changeVisible(method, false);
            $('payment_form_'+method).fire('payment-method:switched', {method_code : method});
        } else {
            //Event fix for payment methods without form like "Check / Money order"
            document.body.fire('payment-method:switched', {method_code : method});
        }
        
        
var  paymentData  = new Array();
  paymentData.payment_method = method;
        
       // var paymentData = "!23123";
  
  var paymentData = new Object();
 
//  review.loader();
//        var request = new Ajax.Request(
//                this.saveUrl,
//                {
//                    method:'post',
//                    onComplete: function(transport){review.updateReview(transport)},
//                    parameters: {payment_method:method}
//                }
//            ); 
        
        this.currentMethod = method;
    },

    changeVisible: function(method, mode) {
        var block = 'payment_form_' + method;
        [block + '_before', block, block + '_after'].each(function(el) {
            element = $(el);
            if (element) {
                element.style.display = (mode) ? 'none' : '';
                element.select('input', 'select', 'textarea').each(function(field) {
                    field.disabled = mode;
                });
            }
        });
    },

    addBeforeValidateFunction : function(code, func) {
        this.beforeValidateFunc.set(code, func);
    },

    beforeValidate : function() {
        var validateResult = true;
        var hasValidation = false;
        (this.beforeValidateFunc).each(function(validate){
            hasValidation = true;
            if ((validate.value)() == false) {
                validateResult = false;
            } 
        }.bind(this));
        if (!hasValidation) {
            validateResult = false;
        }
        return validateResult;
    },

    validate: function() {
        var result = this.beforeValidate();
        if (result) {
            return true;
        }
        var methods = document.getElementsByName('payment[method]');
        if (methods.length==0) {
            alert(Translator.translate('Your order cannot be completed at this time as there is no payment methods available for it.'));
            return false;
        }
        for (var i=0; i<methods.length; i++) {
            if (methods[i].checked) {
                return true;
            }
        }
        result = this.afterValidate();
        if (result) {
            return true;
        }
        alert(Translator.translate('Please specify payment method.'));
        return false;
    },

    addAfterValidateFunction : function(code, func) {
        this.afterValidateFunc.set(code, func);
    },

    afterValidate : function() {
        var validateResult = true;
        var hasValidation = false;
        (this.afterValidateFunc).each(function(validate){
            hasValidation = true;
            if ((validate.value)() == false) {
                validateResult = false;
            }
        }.bind(this));
        if (!hasValidation) {
            validateResult = false;
        }
        return validateResult;
    },

    save: function(){
        if (checkout.loadWaiting!=false) return;
        var validator = new Validation(this.form);
        if (this.validate() && validator.validate()) {
            checkout.setLoadWaiting('payment');
            var request = new Ajax.Request(
                this.saveUrl,
                {
                    method:'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
            );
        }
    },

    resetLoadWaiting: function(){
        checkout.setLoadWaiting(false);
    },

    nextStep: function(transport){
        if (transport && transport.responseText){
            try{
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        /*
        * if there is an error in payment, need to show error message
        */
        if (response.error) {
            if (response.fields) {
                var fields = response.fields.split(',');
                for (var i=0;i<fields.length;i++) {
                    var field = null;
                    if (field = $(fields[i])) {
                        Validation.ajaxError(field, response.error);
                    }
                }
                return;
            }
            alert(response.error);
            return;
        }

        checkout.setStepResponse(response);

        //checkout.setPayment();
    },
 
    initWhatIsCvvListeners: function(){
        $$('.cvv-what-is-this').each(function(element){
            Event.observe(element, 'click', toggleToolTip);
        });
    }
}


//billing
var Billing = Class.create();
Billing.prototype = {
 initialize: function(form, addressUrl, saveUrl){
     this.form = form;
     if ($(this.form)) {
         $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
     }
     this.addressUrl = addressUrl;
     this.saveUrl = saveUrl;
     this.onAddressLoad = this.fillForm.bindAsEventListener(this);
     this.onSave = this.nextStep.bindAsEventListener(this);
     this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
     $("billing:country_id").observe("change",this.updateCountry.bind(this));
 }, 

 updateCountry: function(object){
	  review.loader();
	  var data = {country_id:$("billing:country_id").value};
	  var request = new Ajax.Request(
	        updateCountryUrl,
            {
                method:'post',
                parameters:data,
                 onComplete:function(transport){
                     var json = transport.responseText.evalJSON(true);
                     $("reviewCheckout").update(json.review);
                     $("checkoutloader").remove();
                	 $("shipping_method_section").update(json.shipping_method);

                }
          });
	
 },
 setAddress: function(addressId){
     if (addressId) {
         request = new Ajax.Request(
             this.addressUrl+addressId,
             {method:'get', onSuccess: this.onAddressLoad, onFailure: checkout.ajaxFailure.bind(checkout)}
         );
     }
     else {
         this.fillForm(false);
     }
 },

 newAddress: function(isNew){
     if (isNew) {
         this.resetSelectedAddress();
         Element.show('billing-new-address-form');
     } else {
         Element.hide('billing-new-address-form');
     }
 },

 resetSelectedAddress: function(){
     var selectElement = $('billing-address-select')
     if (selectElement) {
         selectElement.value='';
     }
 },

 fillForm: function(transport){
     var elementValues = {};
     if (transport && transport.responseText){
         try{
             elementValues = eval('(' + transport.responseText + ')');
         }
         catch (e) {
             elementValues = {};
         }
     }
     else{
         this.resetSelectedAddress();
     }
     arrElements = Form.getElements(this.form);
     for (var elemIndex in arrElements) {
         if (arrElements[elemIndex].id) {
             var fieldName = arrElements[elemIndex].id.replace(/^billing:/, '');
             arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
             if (fieldName == 'country_id' && billingForm){
                 billingForm.elementChildLoad(arrElements[elemIndex]);
             }
         }
     }
 },

 setUseForShipping: function(flag) {
     $('shipping:same_as_billing').checked = flag;
 },

 save: function(){
     if (checkout.loadWaiting!=false) return;

     var validator = new Validation(this.form);
     if (validator.validate()) {
         checkout.setLoadWaiting('billing');

//         if ($('billing:use_for_shipping') && $('billing:use_for_shipping').checked) {
//             $('billing:use_for_shipping').value=1;
//         }

         var request = new Ajax.Request(
             this.saveUrl,
             {
                 method: 'post',
                 onComplete: this.onComplete,
                 onSuccess: this.onSave,
                 onFailure: checkout.ajaxFailure.bind(checkout),
                 parameters: Form.serialize(this.form)
             }
         );
     }
 },

 resetLoadWaiting: function(transport){
     checkout.setLoadWaiting(false);
 },

 /**
     This method recieves the AJAX response on success.
     There are 3 options: error, redirect or html with shipping options.
 */
 nextStep: function(transport){
     if (transport && transport.responseText){
         try{
             response = eval('(' + transport.responseText + ')');
         }
         catch (e) {
             response = {};
         }
     }

     if (response.error){
         if ((typeof response.message) == 'string') {
             alert(response.message);
         } else {
             if (window.billingRegionUpdater) {
                 billingRegionUpdater.update();
             }

             alert(response.message.join("\n"));
         }

         return false;
     }

     checkout.setStepResponse(response);
     payment.initWhatIsCvvListeners();
     // DELETE
     //alert('error: ' + response.error + ' / redirect: ' + response.redirect + ' / shipping_methods_html: ' + response.shipping_methods_html);
     // This moves the accordion panels of one page checkout and updates the checkout progress
     //checkout.setBilling();
 }
}

//shipping method
var ShippingMethod = Class.create();
ShippingMethod.prototype = {
 initialize: function(form, saveUrl){
     this.form = form;
     if ($(this.form)) {
         $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
     }
     this.saveUrl = saveUrl;
     this.validator = new Validation(this.form);
     this.onSave = this.nextStep.bindAsEventListener(this);
     this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
     $(this.form+" input[type='radio']");
     
     
 },
 
 selectMethod: function(methodcode)
 {
	 review.loader();
	 var request = new Ajax.Request(
             this.saveUrl,
             {
                 method:'post',
                 onSuccess: function(transport) { review.updateReview(transport);},
                 parameters: {shipping_method:methodcode}
             }
         ); 
	 
	
 },

 validate: function() {
     var methods = document.getElementsByName('shipping_method');
     if (methods.length==0) {
         alert(Translator.translate('Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.'));
         return false;
     }

     if(!this.validator.validate()) {
         return false;
     }

     for (var i=0; i<methods.length; i++) {
         if (methods[i].checked) {
             return true;
         }
     }
     alert(Translator.translate('Please specify shipping method.'));
     return false;
 },

 save: function(){

     if (this.validate()) {
      
         var request = new Ajax.Request(
             this.saveUrl,
             {
                 method:'post',
                 onComplete: this.onComplete,
                 onSuccess: this.onSave,
                 onFailure: checkout.ajaxFailure.bind(checkout),
                 parameters: Form.serialize(this.form)
             }
         );
     }
 },

 resetLoadWaiting: function(transport){
     checkout.setLoadWaiting(false);
 },

 nextStep: function(transport){
     if (transport && transport.responseText){
         try{
             response = eval('(' + transport.responseText + ')');
         }
         catch (e) {
             response = {};
         }
     } 

     if (response.error) {
         alert(response.message);
         return false;
     }

     if (response.update_section) {
         $('checkout-'+response.update_section.name+'-load').update(response.update_section.html);
     }

     payment.initWhatIsCvvListeners();

     if (response.goto_section) {
         checkout.gotoSection(response.goto_section);
         checkout.reloadProgressBlock();
         return;
     }

     if (response.payment_methods_html) {
         $('checkout-payment-method-load').update(response.payment_methods_html);
     }

     checkout.setShippingMethod();
 }
}

