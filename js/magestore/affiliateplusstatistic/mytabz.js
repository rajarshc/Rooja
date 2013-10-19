/**
* Released under the terms or MIT license:
*
* Copyright (c) Bajcic Dragan
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy 
* of this software and associated documentation files (the "Software"), to deal 
* in the Software without restriction, including without limitation the rights 
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
* copies of the Software, and to permit persons to whom the Software is 
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all 
* copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE 
* SOFTWARE.
*/

var mt = Class.create();
mt.prototype = {
  initialize: function(tabs_container,tabs_class) {
		
		this.bgcolor='#ece1f4';
		this.activeColor='#ffffff';
		this.tc=tabs_container;
		this.tbClass=tabs_class;
		this.elm  = $(tabs_container);
		
		new Insertion.Top(this.elm,'<div id="tab-bar"></div>');

		this.tabs = $$(tabs_class);
		this.clear(this.tabs);

		MT=this;
  },
	
	clear: function(tabs){
		tabs.each(function(elm){
			elm.setStyle({display:'none'});
		});
	},
	
	makeActive: function(tab_id){
		
		$(tab_id).setStyle({display:'block'});
		$('mt-'+tab_id).setStyle({backgroundColor:MT.activeColor});
		$$('div.mt-tab').each(function(elm){
		if(elm.hasClassName('mt-tab-active')){

				elm.removeClassName('mt-tab-active');
				elm.setStyle({backgroundColor:MT.bgcolor});
			}
		
		});
		$('mt-'+tab_id).addClassName('mt-tab-active');
	},
	
	addTab: function(tab_id,text){
		
		new Insertion.Bottom($('tab-bar'),'<div id="mt-'+tab_id+'" class="mt-tab">'+text+'</div>');
		var elmnt=$('mt-'+tab_id);
		elmnt.setStyle({backgroundColor:this.bgcolor});
		
		Event.observe(elmnt, 'click', function() {

			window.mt.prototype.clear(MT.tabs);
			window.mt.prototype.makeActive(tab_id,MT.tbClass);
		});
		
		Event.observe(elmnt, 'mouseover', function(nn) {
			
			if(!elmnt.hasClassName('mt-tab-active')){
			elmnt.setStyle({backgroundColor:MT.activeColor});
			}
		});
		
		Event.observe(elmnt, 'mouseout', function(nn) {

			if(!elmnt.hasClassName('mt-tab-active')){
			elmnt.setStyle({backgroundColor:MT.bgcolor});
			
			}
		
		});
	
	},
	
	
	removeTabTitles: function(tabTitlesClass){
			this.tabsTitles = $$(tabTitlesClass);
			this.tabsTitles.each(function(elm){
				elm.setStyle({display:'none'});
			});
	}
};





	
