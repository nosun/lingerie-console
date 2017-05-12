function getIdFromTableSection(section){
	return $('.product-id-cell',section).text().trim();
}

/**
 * 
 * @param remindset: selectorname, parent, attrname
 * @param pid
 * @param ptype
 */
function multiAutoRemind(remindset, pid, ptype){
	//first fetch data from server.
	//get values for data.
	//ajax call for new value.
	var attrNames = [];
	for(var i= 0; i < remindset.length; i++){
		attrNames.push(remindset[i]['attrName']);
	}
	$.ajax({
		  url: url('product/fetchautoremind/'),
		  type: 'POST',
		  dataType :'json',
		  data: {'type':ptype, 'attrNames':attrNames, 'pid':pid}
		}).done(function (data) {
			//dataformat: 'name':values;
			for(var j= 0; j < remindset.length; j++){
				var attrName = remindset[j]['attrName'];
				autoRemind(remindset[j]['selector'], remindset[j]['parent'], data[attrName]);
			}
		});
}

function applySettings(event){
	var trElement = $(this).closest('tr');
	if(trElement.attr('id').indexOf('pid_') > -1){
		var eid = trElement.attr('id');
		var pid = -1;

		pid = eid.split('_')[1];
		var values = {};
		values['p-serial'] = getNewValue('p-serial', 'td', trElement, 'input');
		//values['p-name'] = getNewValue('p-name', 'td', trElement, 'input');
		values['p-type'] = $('td.p-type', trElement).text();
		values['p-weight'] = getNewValue('p-weight', 'td', trElement, 'input');
		
		values['attributes'] = {};
		//for attributes.
		$('td.p-attributes dl', trElement).each(function(){
			var selectorName = $('dd', this).attr('class');
			values['attributes'][selectorName] = getNewValue(selectorName, 'dd', this, 'input');
		});
		
		values['p-suppliers'] = getNewValue('p-suppliers', 'td', trElement, 'input');
		//get values for data.
		//ajax call for new value.
		$.ajax({
			  url: url('product/post/'),
			  type: 'POST',
			  dataType :'json',
			  data: {'pid':pid, 'values':values}
			}).done(function (result) {
				//value.
				var trElement = $('tr#pid_' + pid);
				
				if(result['status'] == 'error'){
					if(result['error_code'] == 1){
						//sn error. add an emphasis class.
						$('div.message_info', trElement).text(result['data']);
						$('div.message_info', trElement).show();
						$('input[name="p-serial"]', trElement).addClass('emphasis').focus();
						return;
					}
				}
				//success.
				//clear the contents of dialog table
				var data = result['data'];
				var container = $('#stockdialog_'+ pid+' table.t-stock tbody');
				$('tr', container).remove();
				var childFragment = '';
				for(var i=0; i < data['stocks'].length; i++){
					childFragment += '<tr id="stock-'+ data['stocks'][i]['stock_id'] +'">';
					childFragment += '<td>'+ data['stocks'][i]['stock_id'] +'</td>';
					childFragment += '<td class="stock-attributes">';
					for(var key in data['stocks'][i]['parameters']){
					    if (data['stocks'][i]['parameters'].hasOwnProperty(key)) {
					    	childFragment += '<p>' + key +' : '+ data['stocks'][i]['parameters'][key] + '</p>';
					    }
					}
					childFragment += '</td>';
					childFragment += '<td class="stock-qty">'+ data['stocks'][i]['stock_qty']+'</td>';
					childFragment += '<td class="stock-bought_price">'+ data['stocks'][i]['bought_price']+'</td>';
				}
				
				container.append(childFragment);

				$('.message_info', trElement).hide();
				edit2View('p-serial', values['p-serial'], 'td', trElement, 'input');
				//edit2View('p-type', values['p-type'], 'td', trElement, 'select');
				edit2View('p-weight', values['p-weight'], 'td', trElement, 'input');
				
				$('td.p-attributes dl', trElement).each(function(){
					var selectorName = $('dd', this).attr('class');
					edit2View(selectorName, values['attributes'][selectorName], 'dd', this, 'input');
				});
				edit2View('p-suppliers', values['p-suppliers'], 'td', trElement, 'input');
				
				//recover apply button to edit.
				$('div.button.apply', trElement).hide();
				$('div.button.edit', trElement).show();
			});
	}
	
	if(trElement.attr('id').indexOf('p_s_id_') > -1){
		var eid = trElement.attr('id');
		var sid = -1;
		var pid = -1;
		splits = eid.split('_');
		pid = splits[3]; 
		sid = splits[4];
		var values = {};
		values['site_category'] = getNewValue('site_category', 'td', trElement, 'select');
		values['site_image_dir'] = getNewValue('site_image_dir', 'td', trElement, 'input');
		values['site_listprice'] = getNewValue('site_listprice', 'td', trElement, 'input');
		values['site_price'] = getNewValue('site_price', 'td', trElement, 'input');
		values['site_pname'] = getNewValue('site_pname', 'td', trElement, 'input');

		//get values for data.
		//ajax call for new value.
		$.ajax({
			  url: url('product/post/'),
			  type: 'POST',
			  dataType :'json',
			  data: {'sid':sid, 'pid':pid, 'values':values}
		}).done(function (result) {
				var trElement = $('tr#p_s_id_' + pid + '_'+ sid);
				if(result['status'] == 'error'){
					if(result['error_code'] == 2){
						//add site related info before apply product.
						$('input', trElement).addClass('emphasis');
						$('div.message_info', trElement).text(result['data']);
						$('div.message_info', trElement).show();
						return;
					}
				}
				//success.
				$('.message_info', trElement).hide();
				edit2View('site_category', values['site_category'], 'td', trElement, 'select');
				edit2View('site_image_dir', values['site_image_dir'], 'td', trElement, 'input');
				edit2View('site_listprice', values['site_listprice'], 'td', trElement, 'input');
				edit2View('site_price', values['site_price'], 'td', trElement, 'input');
				edit2View('site_pname', values['site_pname'], 'td', trElement, 'input');
				//recover apply button to edit.
				$('div.button.apply', trElement).hide();
				$('div.button.edit', trElement).show();

				$('td.site_image_dir', trElement).each(function(){
					if($(this).text() == ''){
						parentTr = $(this).closest('tr');
						$('td div.button.buttonmd.sync', parentTr).addClass('disabled');
					}else{
						parentTr = $(this).closest('tr');
						$('td div.buttonmd.sync', parentTr).removeClass('disabled');
					}
				});
		});
	}
}
function editSettings(event){
	var trElement = $(this).closest('tr');
	if(trElement.attr('id').indexOf('pid_') > -1){
		var trElementIdSegment = trElement.attr('id').split('_');
		var pid = trElementIdSegment[1];
		view2Edit('p-serial', 'td', trElement);
		view2Edit('p-weight', 'td', trElement);
		view2Edit('p-suppliers', 'td', trElement);
		var selectFullName = $('td.p-type', trElement).text();
		
		
		/**
		 * Get the p-type
		$.ajax({
			  url: url('product/gettype'),
			  type: 'POST',
			  data: {'pid':pid, 'current':selectFullName}
		}).done(function (result) {
			var tdElement = $('tr#pid_'+pid+' td.p-type');
			$(tdElement).empty();
			$(tdElement).append(result);
		});
		*/
		
		var autoRemindSet = [];
		
		//for attributes.
		$('td.p-attributes dl', trElement).each(function(){
			var selectorName = $('dd', this).attr('class');
			view2Edit(selectorName, 'dd', this);
			var attrName = selectorName.split('-')[1];
			var autoRemindItem = {'selector':'.'+selectorName+' input', 'parent':this, 'attrName':'attributes.'+ attrName};
			
			autoRemindSet.push(autoRemindItem);
			
			//autoRemind('.'+selectorName+' input', this, 'attributes.' + attrName, selectFullName);
		});
		//for autocomplete for colors and sizes.
		//autoRemind( ".p-colors input", trElement, product_colors, selectFullName);
		//autoRemind(".p-sizes input", trElement, product_sizes, selectFullName);
		var autoRemindItem = {'selector':".p-suppliers input", 'parent':trElement, 'attrName':'suppliers'};
		autoRemindSet.push(autoRemindItem);
		//autoRemind(".p-suppliers input", trElement, 'suppliers', pid);
		multiAutoRemind(autoRemindSet, pid, selectFullName);
	}
	if(trElement.attr('id').indexOf('p_s_id_') > -1){
		//get sid.
		var trElementIdSegment = trElement.attr('id').split('_');
		var sid = trElementIdSegment[4];
		var pid = trElementIdSegment[3];
		
		var selectFullName = $('td.site_category', trElement).text();
		//view2Edit('site_category', 'td', trElement);
		$.ajax({
			  url: url('product/getcategory'),
			  type: 'POST',
			  data: {'sid':sid, 'pid':pid, 'current':selectFullName}
		}).done(function (result) {
			//add the selection.
			var tdElement = $('tr#p_s_id_'+pid+'_'+sid+' td.site_category');
			$(tdElement).empty();
			$(tdElement).append(result);
			//$('select', tdElement).selectBox();
		});
		
		view2Edit('site_image_dir', 'td', trElement);
		view2Edit('site_pname', 'td', trElement);
		view2Edit('site_listprice', 'td', trElement);
		view2Edit('site_price', 'td', trElement);
	}

	$('div.button.apply', $(this).parent()).show();
	$(this).hide();
}

function addCategory(event){
	var trElement = $(this).closest('tr');
	if(trElement.attr('id').indexOf('p_s_id_') > -1){
		//get sid.
		var trElementIdSegment = trElement.attr('id').split('_');
		var sid = trElementIdSegment[4];
		var pid = trElementIdSegment[3];
		
		$.ajax({
			  url: url('product/addcategory'),
			  type: 'POST',
			  dataType: 'html',
			  data: {'sid':sid, 'pid':pid}
		}).done(function (result) {
			//add the selection.
			var tdElement = $('tr#p_s_id_'+pid+'_'+sid+' td.site_other_categories');
			/*$(tdElement).empty();*/
			var makeCatChangeBtn = $('.update_categories', tdElement);
			if(makeCatChangeBtn.css('display') == 'none'){
				makeCatChangeBtn.css({'display':'block'});
			}
			$(result).insertBefore(makeCatChangeBtn);
			
			//$('select', tdElement).selectBox();
			
		});
	}
}

function updateCategory(event){
	var tdElement = $(this).closest('td');
	var trElement = $(this).closest('tr');
	
	if(trElement.attr('id').indexOf('p_s_id_') > -1){
		//get sid.
		var trElementIdSegment = trElement.attr('id').split('_');
		var sid = trElementIdSegment[4];
		var pid = trElementIdSegment[3];

		$('select', tdElement).each(function(){
			var input = $('input[name="added_categories"]', tdElement);
			if(input.val() == ''){
				input.val($(this).val());
			}else{
				input.val(input.val() + ',' + $(this).val());
			}
		});
		var categories = $('input[name="added_categories"]', tdElement).val();
		$.ajax({
			  url: url('product/updatecategory'),
			  type: 'POST',
			  dataType: 'html',
			  data: {'sid':sid, 'pid':pid, 'added_categories':categories}
		}).done(function (result) {
			var tdElement = $('tr#p_s_id_'+pid+'_'+sid+' td.site_other_categories');
			if($(result).text().substring(0, 5) === 'error'){
				tdElement.append(result);
				$('div.message_info', tdElement).show();
				setTimeout(function(){$('div.message_info', tdElement).remove();},5000);
				return;
			}
			tdElement.empty();
			tdElement.append(result);
			$('div.button.update_categories', tdElement).click(updateCategory);
		});
	}
}
function syncToSites(event){
	if($(this).hasClass('disabled') || $(this).hasClass('completed')) return;
	var idValue = $(this).closest('tr').attr('id');
	var idSegments = idValue.split('_');
	var pid = idSegments[3];
	var sid = idSegments[4];
	var sync_img = true;
	if($(this).hasClass('resync')){
		sync_img = false;
	}
	
	$('#p_s_id_'+pid+'_'+sid + ' div.functions_td div.refresh_layer').addClass('show');
	
	$.ajax({
		  url: url('product/synctosite/'),
		  type: 'POST',
		  data: {'pid':pid, 'sid':sid, 'sync_img':sync_img}
	}).done(function(result) {
		$('#p_s_id_'+pid+'_'+sid + ' div.functions_td div.refresh_layer').removeClass('show');
		if(result == 'success'){
			$('#p_s_id_'+pid+'_'+sid + ' div.functions_td div.buttonmd.sync').addClass('resync');
			$('#p_s_id_'+pid+'_'+sid + ' div.functions_td div.buttonmd.sync').text('Update Info');
			$('#p_s_id_'+pid+'_'+sid + ' div.functions_td div.button.shelf').show();
			
		}else{
			$('pid_' + pid + ' div.message_info').text(result);
			$('pid_' + pid + ' div.message_info').show();
			setTimeout(function(){$('pid_' + pid + ' div.message_info').hide();},5000);
		}
	});
}

function changeShelfState(event){
	var idValue = $(this).closest('tr').attr('id');
	var idSegments = idValue.split('_');
	var pid = idSegments[3];
	var sid = idSegments[4];
	$('#p_s_id_'+pid+'_'+sid + ' div.functions_td div.refresh_layer').addClass('show');
	$.ajax({
		url: url('product/changeshelfstate'),
		type: 'POST',
		dataType: 'json',
		data: {'pid':pid, 'sid':sid}
	}).done(function(result){
		$('#p_s_id_'+pid+'_'+sid + ' div.functions_td div.refresh_layer').removeClass('show');
		if(result['status'] == 'success'){
			var nextClass;
			var prevClass;
			var nextText;
			if(result['data'] == '0'){
				nextClass = 'on-shelf';
				prevClass = 'off-shelf';
				nextText = '上架';
			}else{
				nextClass = 'off-shelf';
				prevClass = 'on-shelf';
				nextText = '下架';
			}
			$('#p_s_id_'+pid+'_'+sid + ' div.functions_td div.button.shelf').removeClass(prevClass).addClass(nextClass).text(nextText);
		}else{
			$('pid_' + pid + ' div.message_info').text(result['data']);
			$('pid_' + pid + ' div.message_info').show();
			setTimeout(function(){$('pid_' + pid + ' div.message_info').hide();},5000);
		}
	});
}

function filterProduct(event){
	var sn_filter = $('tr#product-filter input[name="sn_filter"]').val();
	var name_filter = $('tr#product-filter input[name="name_filter"]').val();
	var type_filter = $('tr#product-filter input[name="type_filter"]').val();
	var weight_filter = $('tr#product-filter input[name="weight_filter"]').val();
	var color_filter = $('tr#product-filter input[name="color_filter"]').val();
	var size_filter = $('tr#product-filter input[name="size_filter"]').val();
	$.ajax({
		  url: url('product/ajaxfilter/'),
		  type: 'POST',
		  dataType :'html',
		  data: {'sn_filter':sn_filter, 'name_filter':name_filter,'type_filter':type_filter,'weight_filter':weight_filter,'color_filter':color_filter,'size_filter':size_filter}
		}).done(function (htmlSection) {
			//complete reading the remote site information.
			//now fill all the selection box for the category name.
			$('div.message_info_total').text('Complete sync with remote db.');
			$('div.message_info_total').show();
			setTimeout(function(){$('div.message_info_total').hide();},5000);
});
}

function addNewProduct(event){
	//first get the product type that should be inserted.
	$selectedType = $('select[name="type-select"]').val();
	$.ajax({
		  url: url('product/addnew'),
		  type: 'POST',
		  dataType: 'html',
		  data: {'type':$selectedType}
		}).done(function (htmlSection) {
			var trs = $(htmlSection);
			$('table.t-products>tbody').prepend($(trs));
			var trElements = $('[id^=p_s_id_]', trs[1]);
			$('div.button.edit', trs).button().click(editSettings);
			$('div.button.apply', trs).button().click(applySettings);
			
			$('div.button.shelf', trs).button().click(changeShelfState);
			
			$('div.buttonmd.sync', trs).button().click(syncToSites);

			$('.stock-dialog', trs).dialog({
				autoOpen: false,
				height: 500,
				width: 650,
				modal: true,
				buttons: {"应用": applyStockDialog, "取消": function() {$( this ).dialog( "close" );}},
				close: function() {}
			});
			$('.edit-stock', trs).button().click(function() {
				var pid = $(this).prop('id').split('_')[1];;
				$( "#stockdialog_"+ pid ).dialog( "open" );
			});

			var autoRemindSet = [];
			//auto remind.
			//for attributes.
			$('td.p-attributes dl', trs[0]).each(function(){
				var selectorName = $('dd', this).attr('class');
				var autoCompleteData = $('dd', this).attr('autocompletedata').split(',');
				autoRemind('dd.'+ selectorName+' input', this, autoCompleteData);
			});
			var supplierAutoRemind = $('td.p-suppliers', trs[0]).attr('autocompletedata').split(',');
			autoRemind("td.p-suppliers input", trs[0], supplierAutoRemind);
			
			$('div.button.apply', trs[0]).button().show();
			$('div.button.edit', trs[0]).button().hide();
			//recover apply button to edit.
			$('div.button.apply', trElements).button().show();
			$('div.button.edit', trElements).button().hide();
			var pid = getIdFromTableSection(htmlSection);
			$('div.btn-add.cat-add', trElements).click(addCategory);
			$('div.button.update_categories', trElements).click(updateCategory);
		    $("#fileupload_"+pid).fileupload({
		        dataType: 'json',
		        done: function (e, data) {
		            $.each(data.result.files, function (index, file) {
		                $('td#images_'+pid).append('<a href="'+file.url+'"><img src="' + file.thumbnail_url +'" title="' + file.name + '" alt="' + file.name + '" class="product_thumb"/> </a>');
		            });
		        },
		        formData: {sn: $('tr#pid_'+pid+'>td.p-serial').text().trim(), pid: pid},
		        progressall: function (e, data) {
		            var progress = parseInt(data.loaded / data.total * 100, 10);
		            $('#progress_'+ pid +' .bar').css(
		                'width',
		                progress + '%'
		            );
		        }
		    }); 
		});
}

function remoteSyncLatest(event){
	$selectedSite = $('select[name="site_sync"]').val();
	$.ajax({
		  url: url('product/syncinfo'),
		  type: 'POST',
		  dataType: 'json',
		  data: {'sid':$selectedSite}
		}).done(function (htmlSection) {
			//complete reading the remote site information.
			//now fill all the selection box for the category name.
			$('div.message_info_total').text('Complete sync with remote db.');
			$('div.message_info_total').show();
			setTimeout(function(){$('div.message_info_total').hide();},5000);
	});
}

function editStock(event){
	var dialog = $(this).closest('div.stock-dialog');
	//$p_sn = $(dialog).prop('id').split("_")[1];
	$('tr', dialog).each(function(){
		view2Edit('stock-qty', 'td', this);
		view2Edit('stock-bought_price', 'td', this);
		view2Edit('suppliers-sn', 'td', this);
	});
	$('div.button.stock_apply', dialog).show();
	$(this).hide();
}

function applyStock(event){
	var dialog = $(this).closest('div.stock-dialog');
	//$p_sn = $(dialog).prop('id').split("_")[1];
	$('tr', dialog).each(function(){
		var values = {};
		values['stock-qty'] = getNewValue('stock-qty', 'td', this, 'input');
		values['stock-bought_price'] = getNewValue('stock-bought_price', 'td', this, 'input');
		values['suppliers-sn'] = getNewValue('suppliers-sn', 'td', this, 'input');
		edit2View('stock-qty', values['stock-qty'], 'td', this, 'input');
		edit2View('stock-bought_price', values['stock-bought_price'], 'td', this, 'input');
		edit2View('suppliers-sn',  values['suppliers-sn'], 'td', this, 'input');
	});
	$('div.button.stock_edit', dialog).show();
	$(this).hide();
}

function applyStockDialog(){
	//sn = $(this).prop('id').split('-')[1];
	var stockVals = [];
	$('table.t-stock tr', $(this)).each(function(){
		var stockId = $(this).prop('id').split('-')[1];
		if (stockId == undefined) {
			return;
		}
		var qty = $('td.stock-qty', this).text();
		if(qty == ''){
			//still in edit mode
			qty = $('td.stock-qty input', this).val();
			edit2View('stock-qty', qty, 'td', this, 'input');
		}
		var bought_price = $('td.stock-bought_price', this).text();
		if(bought_price == ''){
			bought_price = $('td.stock-bought_price input', this).val();
			edit2View('stock-bought_price', bought_price, 'td', this, 'input');
		}
		var suppliers_sn = $('td.suppliers-sn', this).text();
		if (suppliers_sn == '') {
			suppliers_sn = $('td.suppliers-sn input', this).val();
			edit2View('suppliers-sn', suppliers_sn, 'td', this, 'input');
		}
		stockVals.push({'stock_id':stockId, 'stock_qty':qty, 'bought_price':bought_price, 'suppliers_sn':suppliers_sn});
	});
	$.ajax({
		  url: url('product/completestockedit/'),
		  type: 'POST',
		  dataType :'json',
		  data: {'stocks':stockVals}
		}).done(function (data) {});
	$('div.button.stock_edit', this).show();
	$('div.button.stock_apply', this).hide();
	$( this ).dialog( "close" );
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
	$('div.button.apply').hide();
	$('div.button.edit').show();
	$('div.button.edit').click(editSettings);
	$('div.button.apply').click(applySettings);
	$('div.button.remote_sync_latest').click(remoteSyncLatest);
	$('div.button.add_new_product').click(addNewProduct);
	$('div.btn-add.cat-add').click(addCategory);
	$('div.button.update_categories').click(updateCategory);
	$('div.buttonmd.sync').click(syncToSites);
	$('div.button.shelf').click(changeShelfState);
	$('div.filter_product').click(filterProduct);
	$('input.onoffswitch-checkbox:checkbox').change(onOffSwitch);
	$(".stock-dialog div.button.stock_edit").click(editStock);
	$(".stock-dialog div.button.stock_apply").click(applyStock);
	$('.stock-dialog').dialog({
		autoOpen: false,
		height: 500,
		width: 650,
		modal: true,
		buttons: {"应用": applyStockDialog, "取消": function() {$( this ).dialog( "close" );}},
		close: function() {}
	});
	$('.edit-stock').button().click(function() {
		var pid = $(this).prop('id').split('_')[1];;
		$( "#stockdialog_"+ pid ).dialog( "open" );
	});
	
	$('.switcher-support').hide();
	$('.switcher-on-span').attr('rowspan', '1');
	
});