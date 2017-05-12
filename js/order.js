function inputBlur(defaultVal){
	if(this.value==''){
		this.value=defaultVal;
	}
}
function inputFocus(defaultVal){
	if(this.value==defaultVal){
		this.value='';
	}
}

function onOffSwitch(){
    if ($(this).is(':checked')) {
    	$('.switcher-support').show();
    	$('.switcher-on-span').attr('rowspan', '2');
    }else{
    	$('.switcher-support').hide();
    	$('.switcher-on-span').attr('rowspan', '1');
    }
}

$(document).ready(function(){
	$('.f-createdafter input.datepicker').datepicker();
	$('.f-createdbefore input.datepicker').datepicker();

	$( 'button, input[type="submit"], div.button' ).button();
	$('select').selectBox();
	$('input.onoffswitch-checkbox:checkbox').change(onOffSwitch);
	$('.switcher-support').hide();
	$('.switcher-on-span').attr('rowspan', '1');
	$('input[type="checkbox"].filter-by-order').click(function(){
		if ($(this).prop("checked") == true) {
			//choose this item.(erase from session history removed list).
			var oid = $(this).attr('id').split('_')[1];
			$.ajax({
				  url: url('order/filterbyitemadd'),
				  type: 'POST',
				  dataType: 'html',
				  data: {'oid':oid}
			}).done(function (result) {
				//@TODO tell user it was added.
			});
		}else{
			//remove this item.(added to session history removed list).
			var oid = $(this).attr('id').split('_')[1];
			$.ajax({
				  url: url('order/filterbyitemremove'),
				  type: 'POST',
				  dataType: 'html',
				  data: {'oid':oid}
			}).done(function (result) {
				//@TODO tell user it was removed.
			});
		}
	});
	
	$('div.button.print.print-orderaddress').click(function(){
		window.open(url('order/generateaddressprint'), "_blank");
	});
	
	$('div.button.print.print-orderaddress-inline').click(function(){
		window.open(url('order/generateaddressprintinline'), "_blank");
	});
	
    $('a.shipping-no').editable({
        url: url('order/editshippingno'),
        title: '填写运单号(格式：运输方式,运单号)',
        emptytext: '未填写运单号',
        showbuttons:'left',
        inputclass: 'shipping-no-input',
        success: function(response){
        	response = $.parseJSON(response);
        	if(!response.success){
        		return response.msg;
        	}
        }
    });
    
    $('.actual-shipping-fee').editable({
    	url: url('order/addactualshippingfee'),
    	title: '请输入运费',
    	emptytext: '空',
    	validate: function(value) {
 	 		var pattern = /^([1-9]\d*(\.\d+)?|0\.\d*[1-9]\d*)$/;
 			if(!pattern.test($.trim(value))) {
 	 			return '请输入正确的运费';
 			}
 		},
 		success: function(response) {
 			response = $.parseJSON(response);
        	if(!response.success){
        		return response.msg;
        	}
 		}
    });
    
    $('.t-row').editable(
        	{
        	  selector: 'a',
          	  url: url('order/updateorder'),
          	  title: 'Update Select Value',
          	  showbuttons:'left',
          	  inputclass: 'shipping-no-input'
            });
	
	$( "#showaddress-dialog").dialog({
		autoOpen: false,
		minWidth: '450',
		modal: true,
		buttons: {"Close": function() {$( this ).dialog( "close" );}},
		close: function() {
			//clear dialog contents.
			$( "#showaddress-dialog" ).html('');
		}
	});
	$('.view_address').button().click(function(){
		var oid = $(this).closest('tr').prop('id').split('_')[1];
		$.get(url("order/ajaxgetorderaddress/" + oid), function( data ) {
	    	   $( "#showaddress-dialog" ).html(data);
	    	   $( "#showaddress-dialog" ).dialog('open');
			});
	});
});