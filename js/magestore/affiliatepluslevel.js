var MapToptier = Class.create();
MapToptier.prototype = {
    initialize: function(changeToptierUrl){
        this.changeToptierUrl = changeToptierUrl;
       
    },	
	changeToptier : function(toptierId)
	{
		var url = this.changeToptierUrl;
		url += 'account_id/' + toptierId;
		new Ajax.Updater('map-toptier-info',url,{method: 'get', onComplete: function(){updateToptierInfo();} ,onFailure: ""});
	}
}

function updateToptierInfo()
{
	$('toptier').value = $('map_toptier_name').value;
	$('toptier_id').value = $('map_toptier_id').value;
    if ($('toptier_id').value) {
        $('level').value = parseInt($('map_toptier_level').value) + 1;
    } else {
        $('level').value = '';
    }
}
