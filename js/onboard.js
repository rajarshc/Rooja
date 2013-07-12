function close_wrapper(){
	jQuery('#wrappers').slideToggle(1000);
	document.getElementById('on-board').setAttribute("style","height:0px;");
	Mage.Cookies.set('closeonboard', 'Yes');
}

function change_wrapper1(){
	jQuery('#quick-tour-wrapper').fadeOut(400);
	setTimeout(function(){jQuery('#how-we-work-wrapper').fadeIn(400);},400);
}

function change_wrapper2(){
	jQuery('#how-we-work-wrapper').fadeOut(400);
	setTimeout(function(){jQuery('#form-wrapper').fadeIn(400);},400);
}
function change_wrapper3(){
	jQuery('#form-wrapper').fadeOut(400);
	setTimeout(function(){jQuery('#lets-shop-wrapper').fadeIn(400);},400);
}


function validateEmail(email) 
{
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function CheckLength(form) {

	var password = form.password.value;

	if (password.length < 6)
	return false;
			
	return true;
}

	
function change_name(){
	document.getElementById('welcome-guest').innerHTML = document.forms['intro'].firstname.value;
}

function confirm_pass(){
	document.forms['intro'].confirmation.value = document.forms['intro'].password.value;
}

function inputFocus(i){
    if(i.value==i.defaultValue){ i.value=""; i.style.color="#2F0B3A"; }
}
function inputBlur(i){
    if(i.value==""){ i.value=i.defaultValue; i.style.color="#888"; }
}

function assign(){
		if(Mage.Cookies.get('closeonboard') != "Yes"){
			jQuery('#wrappers').slideToggle(1000);
			document.getElementById('on-board').setAttribute("style","height:700px;");
		}
		jQuery('#close-button').show();
		setTimeout(function(){jQuery('#quick-tour-wrapper').fadeIn(400);},1000);	
}

function splitname(){
var fullname = document.forms['intro'].firstname.value;
document.forms['intro'].firstname.value = fullname.split(' ').slice(0, -1).join(' ');
document.forms['intro'].lastname.value = fullname.split(' ').slice(-1).join(' ');
if(document.forms['intro'].firstname.value == "")
document.forms['intro'].firstname.value = document.forms['intro'].lastname.value;
}

