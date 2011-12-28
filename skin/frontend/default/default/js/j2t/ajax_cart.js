/**
 * J2T-DESIGN.
 *
 * @category   J2t
 * @package    J2t_Ajaxcheckout
 * @copyright  Copyright (c) 2003-2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    GPL
 */
/*
var loadingW = 260;
var loadingH = 50;
var confirmW = 260;
var confirmH = 134;
*/
var inCart = false;

if (window.location.toString().search('/product_compare/') != -1){
	var win = window.opener;
}
else{
	var win = window;
}

if (window.location.toString().search('/checkout/cart/') != -1){
    inCart = true;
}


function setLocation(url){
    if(!inCart && (/*(url.search('/add') != -1 ) || (url.search('/remove') != -1 ) ||*/ url.search('checkout/cart/add') != -1) ){
        
        if (ajax_cart_qty == 1){
            sendQtyAsk(url);
        } else {
            sendcart(url, 'url', 1);
        }
    } else if (url.search('options=cart') != -1) {
        sendoptions(url);
    } else{
        window.location.href = url;
    }
}



document.observe("dom:loaded", function() {
    if (optionsPrice == undefined){
        var optionsPrice;
    }
    if (productAddToCartForm == undefined){
        var productAddToCartForm;
    }
    if (optionsPrice == undefined){
        var optionsPrice;
    }
    if (spConfig == undefined){
        var spConfig;
    }
    if (DateOption == undefined){
        var DateOption;
    }
});


function getQtyValue(){
    var qty_val = $('j2t_ajax_confirm_wrapper').down('.qty');
    if (isNaN(qty_val.value)){
        return 1
    } else {
        return qty_val.value;
    }
}

function sendQtyAsk(url){
    showLoading();
    $('j2t_ajax_qty').down('.j2t-btn-cart').stopObserving();
    $('j2t_ajax_qty').down('.j2t-btn-cart').observe('click', function(){
                                                                sendcart(url, 'url', getQtyValue());
                                                            });
    var qty_content = $('j2t_ajax_qty').innerHTML;
    //qty_content = qty_content.replace('<button', '<button onclick="sendcart(\''+url+'\', \'url\', getQtyValue());"');

    $('j2t_ajax_confirm').update('<div id="j2t_ajax_confirm_wrapper">'+qty_content+ '</div>');
    showConfirm();

    $('j2t_ajax_confirm').down('.j2t-btn-cart').stopObserving();
    $('j2t_ajax_confirm').down('.j2t-btn-cart').observe('click', function(){
                                                                sendcart(url, 'url', getQtyValue());
                                                            });
}


function sendoptions(url){
    //alert(j2t_show_options);
    if (j2t_show_options == 0){
        //setLocation(url);
        document.location.href = url;
    } else {
        //////////////

        showLoading();
        url = url.replace('checkout/cart', 'j2tajaxcheckout/index/cart/cart');
        var myAjax = new Ajax.Request(
        url,
        {
            asynchronous: true,
            method: 'post',
            postBody: '',
            onException: function (xhr, e)
            {
                alert('Exception : ' + e);
            },
            onComplete: function (xhr)
            {
                var result = xhr.responseText;
                $('j2t-temp-div').innerHTML = result.stripScripts();

                var product_html = '';
                if (j2t_product_essentials != ''){
                    product_html = $('j2t-temp-div').down('.'+j2t_product_essentials).innerHTML;
                } else {
                    product_html = $('j2t-temp-div').down('.product-essential').innerHTML;
                }

                
                var txt_script = '';
                var scripts = [];
                var script_sources = xhr.responseText.split(/<script.*?>/);
                for (var i=1; i < script_sources.length; i++){
                    var str = script_sources[i].split(/<\/script>/)[0];
                    str = str.replace('//<![CDATA[', '');
                    str = str.replace('//]]>', '');
                    if (str.indexOf('optionsPrice') != -1 || str.indexOf('spConfig') != -1 || /*str.indexOf('decorateGeneric') != -1 ||*/
                        str.indexOf('j2t_points') != -1 || str.indexOf('productAddToCartForm') != -1 ||
                        str.indexOf('DateOption') != -1){

                        str = str.replace('var optionsPrice', 'optionsPrice');
                        str = str.replace('var spConfig', 'spConfig');
                        str = str.replace('var DateOption', 'DateOption');

                        str = str.replace('var optionsPrice', 'optionsPrice');
                        str = str.replace('var productAddToCartForm', 'productAddToCartForm');
                        str = str.replace('this.form.submit()', 'sendcart(\'\', \'form\', 1)');

                        scripts.push(str);
                        txt_script += str + "\n";
                    }
                }
                $('j2t-temp-div').innerHTML = '';
                $('j2t_ajax_confirm').update('<div id="j2t_ajax_confirm_wrapper">'+product_html+ '</div><script type="text/javascript">'+txt_script+'</script>');


                if (j2t_product_image != ''){
                    $('j2t_ajax_confirm').down('.'+j2t_product_image).hide();
                } else {
                    $('j2t_ajax_confirm').down('.product-img-box').hide();
                }

                var arr;
                if (j2t_product_shop != ''){
                    arr = $('j2t_ajax_confirm').down('.'+j2t_product_shop).childElements();
                } else {
                    arr = $('j2t_ajax_confirm').down('.product-shop').childElements();
                }
                
                
                arr.each(function(node){
                  node.style.display = 'none';
                });

                if (j2t_product_options != ''){
                    $('j2t_ajax_confirm').down('.'+j2t_product_options).show();
                } else {
                    $('j2t_ajax_confirm').down('.product-options').show();
                }

                if (j2t_product_bottom != ''){
                    $('j2t_ajax_confirm').down('.'+j2t_product_bottom).show();
                } else {
                    $('j2t_ajax_confirm').down('.product-options-bottom').show();
                }
                

                replaceDelUrls();

                if (ajax_cart_show_popup){
                    showConfirm();
                } else {
                    hideJ2tOverlay();
                }
            }

        });

        //////////////
    }
    
}
function sendcart(url, type, qty_to_insert){
    var continue_scr = true;
    if ($('pp_checkout_url')){
        //http://www.j2t-design.net
        var pp = $('pp_checkout_url').value;
        if (pp != ''){
            continue_scr = false;
            var form = $('product_addtocart_form');
            form.submit();
        }
    }
    if (continue_scr) {
        
        hideJ2tOverlay();
        showLoading();
        if (type == 'form'){
            //alert('la');
            var found_file = false;
            var form = $('product_addtocart_form');
            if (form){
                inputs = form.getInputs('file');
                if (inputs.length > 0){
                    found_file = true;
                }
            }

            if (found_file){
                form.submit();
            } else {
                url = ($('product_addtocart_form').action).replace('checkout/cart', 'j2tajaxcheckout/index/cart/cart');
                var myAjax = new Ajax.Request(
                url,
                {
                    asynchronous: true,
                    method: 'post',
                    postBody: $('product_addtocart_form').serialize(),
                    parameters : Form.serialize("product_addtocart_form"),
                    onException: function (xhr, e)
                    {
                        alert('Exception : ' + e);
                    },
                    onComplete: function (xhr)
                    {
                        $('j2t-temp-div').innerHTML = xhr.responseText;
                        var upsell_items = $('j2t-temp-div').down('.j2t-ajaxupsells').innerHTML;
                        var return_message = $('j2t-temp-div').down('.j2t_ajax_message').innerHTML;
                        var middle_text = '<div class="j2t-cart-bts">'+$('j2t-temp-div').down('.back-ajax-add').innerHTML+'</div>';

                        $('j2t_ajax_confirm').innerHTML = '<div id="j2t_ajax_confirm_wrapper">'+return_message + middle_text + upsell_items + '</div>';
                        var link_cart_txt = $('j2t-temp-div').down('.cart_content').innerHTML;

                        $$('.top-link-cart').each(function (el){
                            el.innerHTML = link_cart_txt;
                        });

                        if (j2t_custom_top_link != ''){
                            $$('.'+j2t_custom_top_link).each(function (el){
                                el.innerHTML = link_cart_txt;
                            });
                        }

                        var mini_cart_txt = $('j2t-temp-div').down('.cart_side_ajax').innerHTML;

                        $$('.mini-cart').each(function (el){
                            el.replace(mini_cart_txt);
                        });

                        $$('.block-cart').each(function (el){
                            el.replace(mini_cart_txt);
                        });

                        if (j2t_custom_mini_cart != ''){
                            $$('.'+j2t_custom_mini_cart).each(function (el){
                                el.replace(mini_cart_txt);
                            });
                        }

                        replaceDelUrls();

                        if (ajax_cart_show_popup){
                            showConfirm();
                        } else {
                            hideJ2tOverlay();
                        }

                    }

                });
            }

        } else if (type == 'url'){
            
            url = url.replace('checkout/cart', 'j2tajaxcheckout/index/cart/cart');
            var myAjax = new Ajax.Request(
            url,
            {
                asynchronous: true,
                method: 'post',
                postBody: '',
                parameters: 'qty='+qty_to_insert,
                onException: function (xhr, e)
                {
                    alert('Exception : ' + e);
                },
                onComplete: function (xhr)
                {
                    $('j2t-temp-div').innerHTML = xhr.responseText;
                    var upsell_items = $('j2t-temp-div').down('.j2t-ajaxupsells').innerHTML;
                    var return_message = $('j2t-temp-div').down('.j2t_ajax_message').innerHTML;
                    var middle_text = '<div class="j2t-cart-bts">'+$('j2t-temp-div').down('.back-ajax-add').innerHTML+'</div>';

                    var content_ajax = return_message + middle_text + upsell_items;

                    $('j2t_ajax_confirm').innerHTML = '<div id="j2t_ajax_confirm_wrapper">'+content_ajax + '</div>';

                    var link_cart_txt = $('j2t-temp-div').down('.cart_content').innerHTML;

                    $$('.top-link-cart').each(function (el){
                        el.innerHTML = link_cart_txt;
                    });

                    if (j2t_custom_top_link != ''){
                        $$('.'+j2t_custom_top_link).each(function (el){
                            el.innerHTML = link_cart_txt;
                        });
                    }

                    var mini_cart_txt = $('j2t-temp-div').down('.cart_side_ajax').innerHTML;

                    $$('.mini-cart').each(function (el){
                        el.replace(mini_cart_txt);
                        //new Effect.Opacity(el, { from: 0, to: 1, duration: 1.5 });
                    });

                    $$('.block-cart').each(function (el){
                        el.replace(mini_cart_txt);
                        //new Effect.Opacity(el, { from: 0, to: 1, duration: 1.5 });
                    });

                    if (j2t_custom_mini_cart != ''){
                        $$('.'+j2t_custom_mini_cart).each(function (el){
                            el.replace(mini_cart_txt);
                        });
                    }

                    replaceDelUrls();
                    if (ajax_cart_show_popup){
                        showConfirm();
                    } else {
                        hideJ2tOverlay();
                    }
                }

            });

        }
        
        
    }
    
}

function replaceDelUrls(){
    //if (!inCart){
        $$('a').each(function(el){
            if(el.href.search('checkout/cart/delete') != -1 && el.href.search('javascript:cartdelete') == -1){
                el.href = 'javascript:cartdelete(\'' + el.href +'\')';
            }
        });
    //}
}

function replaceAddUrls(){
    $$('a').each(function(link){
        if(link.href.search('checkout/cart/add') != -1){
            link.href = 'javascript:setLocation(\''+link.href+'\'); void(0);';
        }
    });
}

function cartdelete(url){
    
    showLoading();
    url = url.replace('checkout/cart', 'j2tajaxcheckout/index/cartdelete/cart');
    var myAjax = new Ajax.Request(
    url,
    {
        asynchronous: true,
        method: 'post',
        postBody: '',
        onException: function (xhr, e)
        {
            alert('Exception : ' + e);
        },
        onComplete: function (xhr)
        {
            $('j2t-temp-div').innerHTML = xhr.responseText;
            //$('j2t-temp-div').insert(xhr.responseText);

            var cart_content = $('j2t-temp-div').down('.cart_content').innerHTML;

            //alert(cart_content);

            $$('.top-link-cart').each(function (el){
                el.innerHTML = cart_content;
            });

            if (j2t_custom_top_link != ''){
                $$('.'+j2t_custom_top_link).each(function (el){
                    el.innerHTML = cart_content;
                });
            }

            var process_reload_cart = false;
            var full_cart_content = $('j2t-temp-div').down('.j2t_full_cart_content').innerHTML;
            $$('.cart').each(function (el){
                el.replace(full_cart_content);
                process_reload_cart = true;
            });

            if (!process_reload_cart){
                $$('.checkout-cart-index .col-main').each(function (el){
                    el.replace(full_cart_content);
                    //new Effect.Opacity(el, { from: 0, to: 1, duration: 1.5 });
                });
            }


            if (j2t_custom_cart != ''){
                $$('.'+j2t_custom_cart).each(function (el){
                    el.replace(full_cart_content);
                });
            }


            var cart_side = '';
            if ($('j2t-temp-div').down('.cart_side_ajax')){
                cart_side = $('j2t-temp-div').down('.cart_side_ajax').innerHTML;
            }

            
            $$('.mini-cart').each(function (el){
                el.replace(cart_side);
                //new Effect.Opacity(el, { from: 0, to: 1, duration: 1.5 });
            });
            $$('.block-cart').each(function (el){
                el.replace(cart_side);
                //new Effect.Opacity(el, { from: 0, to: 1, duration: 1.5 });
            });

            if (j2t_custom_mini_cart != ''){
                $$('.'+j2t_custom_mini_cart).each(function (el){
                    el.replace(mini_cart_txt);
                });
            }

            replaceDelUrls();

            //$('j2t_ajax_progress').hide();
            hideJ2tOverlay();
        }

    });
}

function showJ2tOverlay(){
    
    new Effect.Appear($('j2t-overlay'), { duration: 0.5,  to: 0.8 });
}

function hideJ2tOverlay(){
    $('j2t-overlay').hide();
    $('j2t_ajax_progress').hide();
    $('j2t_ajax_confirm').hide();
}


function j2tCenterWindow(element) {
     if($(element) != null) {

          // retrieve required dimensions
            var el = $(element);
            var elDims = el.getDimensions();
            var browserName=navigator.appName;
            if(browserName==="Microsoft Internet Explorer") {

                if(document.documentElement.clientWidth==0) {
                    //IE8 Quirks
                    //alert('In Quirks Mode!');
                    var y=(document.viewport.getScrollOffsets().top + (document.body.clientHeight - elDims.height) / 2);
                    var x=(document.viewport.getScrollOffsets().left + (document.body.clientWidth - elDims.width) / 2);
                }
                else {
                    var y=(document.viewport.getScrollOffsets().top + (document.documentElement.clientHeight - elDims.height) / 2);
                    var x=(document.viewport.getScrollOffsets().left + (document.documentElement.clientWidth - elDims.width) / 2);
                }
            }
            else {
                // calculate the center of the page using the browser andelement dimensions
                var y = Math.round(document.viewport.getScrollOffsets().top + ((window.innerHeight - $(element).getHeight()))/2);
                var x = Math.round(document.viewport.getScrollOffsets().left + ((window.innerWidth - $(element).getWidth()))/2);
            }
            // set the style of the element so it is centered
            var styles = {
                position: 'absolute',
                top: y + 'px',
                left : x + 'px'
            };
            el.setStyle(styles);




     }
}


function generateTemplateBox(content, box_w, box_h){
    //var use_template = true;
    //var box_width_height = 20;
    if (use_template){
        var middle_w = box_w - (box_width_height * 2);
        var middle_h = box_h + (box_width_height * 2);

        $('j2t-div-template').down('.j2t-box-cm').innerHTML = content;

        $('j2t-div-template').down('.j2t-box-tl').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': box_width_height+'px'});
        $('j2t-div-template').down('.j2t-box-tm').setStyle({ 'float': 'left', 'width': middle_w+'px', 'height': box_width_height+'px'});
        $('j2t-div-template').down('.j2t-box-tr').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': box_width_height+'px'});

        $('j2t-div-template').down('.j2t-box-cl').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': middle_h+'px'});
        $('j2t-div-template').down('.j2t-box-cm').setStyle({ 'float': 'left', 'width': middle_w+'px', 'height': middle_h+'px'});
        $('j2t-div-template').down('.j2t-box-cr').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': middle_h+'px'});

        $('j2t-div-template').down('.j2t-box-bl').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': box_width_height+'px'});
        $('j2t-div-template').down('.j2t-box-bm').setStyle({ 'float': 'left', 'width': middle_w+'px', 'height': box_width_height+'px'});
        $('j2t-div-template').down('.j2t-box-br').setStyle({ 'float': 'left', 'width': box_width_height+'px', 'height': box_width_height+'px'});

        content = $('j2t-div-template').innerHTML;
    }
    return content;
}


function showLoading(){
    showJ2tOverlay();
    var progress_box = $('j2t_ajax_progress');
    progress_box.show();
    progress_box.style.width = loadingW + 'px';
    progress_box.style.height = loadingH + 'px';

    //width : 320 height : 140
    //312 x 102

    if (use_template){
        progress_box.style.width = loadingW + (box_width_height * 2) + 'px';
        progress_box.style.height = loadingH + (box_width_height * 2) + 'px';
    }
    //alert($('j2t_ajax_progress').getWidth() +' et '+ $('j2t_ajax_progress').getHeight());

    $('j2t_ajax_progress').innerHTML = generateTemplateBox($('j2t-loading-data').innerHTML, $('j2t_ajax_progress').getWidth()-box_width_height, $('j2t_ajax_progress').getHeight()-(box_width_height*2));
    progress_box.style.position = 'absolute';

    j2tCenterWindow(progress_box);
}


function showConfirm(){
    showJ2tOverlay();
    $('j2t_ajax_progress').hide();
    var confirm_box = $('j2t_ajax_confirm');
    confirm_box.show();
    confirm_box.style.width = confirmW + 'px';
    confirm_box.style.height = confirmH + 'px';
    //j2t_ajax_confirm_wrapper
    if ($('j2t_ajax_confirm_wrapper') && $('j2t-upsell-product-table')){
        //alert($('j2t_ajax_confirm_wrapper').getHeight());
        confirm_box.style.height = $('j2t_ajax_confirm_wrapper').getHeight() + 'px';
        decorateTable('j2t-upsell-product-table');
    }

    if (use_template){
        confirm_box.style.width = $('j2t_ajax_confirm_wrapper').getWidth() + (box_width_height * 2) + 'px';
        confirm_box.style.height = $('j2t_ajax_confirm_wrapper').getHeight() + (box_width_height * 4) + 'px';
    }

    $('j2t_ajax_confirm_wrapper').replace('<div id="j2t_ajax_confirm_wrapper">'+generateTemplateBox($('j2t_ajax_confirm_wrapper').innerHTML, $('j2t_ajax_confirm_wrapper').getWidth(), $('j2t_ajax_confirm_wrapper').getHeight())+'<div>');

    confirm_box.style.position = 'absolute';
    j2tCenterWindow(confirm_box);
}

Event.observe(window, 'resize', function(){
    var confirm_box = $('j2t_ajax_confirm');
    j2tCenterWindow(confirm_box);

    var progress_box = $('j2t_ajax_progress');
    j2tCenterWindow(progress_box);
});

Event.observe(window, 'scroll', function(){
    var confirm_box = $('j2t_ajax_confirm');
    j2tCenterWindow(confirm_box);

    var progress_box = $('j2t_ajax_progress');
    j2tCenterWindow(progress_box);
});

document.observe("dom:loaded", function() {
    replaceDelUrls();
    replaceAddUrls();
    Event.observe($('j2t-overlay'), 'click', hideJ2tOverlay);

    var cartInt = setInterval(function(){
        if (typeof productAddToCartForm  != 'undefined'){
            if ($('j2t-overlay')){
                Event.observe($('j2t-overlay'), 'click', hideJ2tOverlay);
            }
            productAddToCartForm.submit = function(url){
                if(this.validator && this.validator.validate()){
                    sendcart('', 'form', 1);
                }
                clearInterval(cartInt);
                return false;
            }
        } else {
            clearInterval(cartInt);
        }
    },500);

    var form = $('product_addtocart_form');
    if(form){
        inputs = form.getInputs('file');

        if (inputs.length == 0){
            Event.observe("product_addtocart_form", "submit", function(event){
                event.stop();
            });
        }
    }
    
});
