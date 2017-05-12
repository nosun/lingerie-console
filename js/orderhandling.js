function lockunlock(event){
       var lockElement = $('span.lock-icon', this);
       var removeCls = '';
       var addCls = '';
       if (lockElement.hasClass('locked')){
    	   removeCls = 'locked';
    	   addCls = 'unlocked';
      } else {
	   	   removeCls = 'unlocked';
		   addCls = 'locked';
      }
       lockElement.removeClass(removeCls);
       lockElement.addClass(addCls);
       
       jQuery.ajax({
           type: "POST" ,
           url: url('order/changelockstate'),
           data: {'oid':$('.pool-order', $(this).parent()).prop('id'), 'lock_state':addCls, 'pool':$(this).parent().parent().prop('id')},
           success : function (data){}
       });
}
function fillnewitems(event){
    jQuery.ajax({
        type: "POST" ,
        url: url('order/fillnewitems'),
        data: {'oid':$('.pool-order', $(this).parent()).prop('id'), 'pool':$(this).parent().parent().prop('id')},
        success : function (data){
            if(data.indexOf('ERROR') == 0){
                $('.message_info_total').show();
                $('<div style="padding:5px 20px 5px 10px; font-size:bold; color:#948; border-radius:3px;"><span style="font-size:20px;">'+ data +'</span></div>').appendTo($('.message_info_total'));
                setTimeout(function(){$('.message_info_total').empty().hide();},10000);
                return;
            }
            var returnElem = $(data);
            var pool_order = $('.pool-order', returnElem);
            if( pool_order.attr('old') == 'readyExportOrders'){
                //get the order id.
                var order_id = pool_order.prop('id');
                //remove the order from old pool.
                var curElem = $('#'+ order_id, $('#arrangedOrders')).parent();
                
                curElem.remove();
                //add the new order info into new pool.
                $('#readyExportOrders').append(returnElem);
                $( ".lockstate", returnElem).dblclick(lockunlock);
                $( '.subitems-dialog', returnElem ).dialog({
                    autoOpen: false ,
                    height: '500' ,
                    width: '900' ,
                    title: '订单详细信息' ,
                    modal: true ,
                    buttons: { "Close" : function () {$( this ).dialog( "close" );}},
                    close: function () {},
                    open: function(){
                    	//延迟加载图片
                    	$('img', this).each(function(){
                    		var attr = $(this).attr('original');
                    		if (typeof attr !== 'undefined' && attr !== false) {
        	            		var href = $(this).attr('original');
        	            		$(this).attr('src', href);
        	            		$(this).removeAttr('original');
                    		}
                    	});
                    }
               });
               $( 'a.oitemdetail', returnElem ).click( function() {
                    event.preventDefault();
                    var oid = $( this).prop( 'id').split( '_' )[0];
                    $.get(url("order/ajaxgetorderitems/" + oid), function( data ) {
         	    	   $( ".subitems-dialog" ).html(data);
         	    	   $( ".subitems-dialog" ).dialog('open');
         			});
               });

               $('.admin-note', returnElem).editable({
    	    	  url: url('order/addadminnote'),
    	    	  title: 'Enter Admin Note',
    	    	  rows: 4,
    	    	  inputclass: 'width240',
    	    	  display: function(value) {
    	          	if(value == ''){
    	          		value = '<i class="icon-unnoted"></i>';
    	          	}else{
    	          		value = '<i class="icon-noted"></i>';
    	          	}
    	          	$(this).html(value);
    	    	}
    	      });
               
               //added for out_stock button.
	        	if($(returnElem).attr('data-sid') != '13'){
	        		//not lingeriemore.
	        		$('<div class="button order_out_stock out_own_stock">出库</div>').appendTo($(returnElem));
	        		$('div.button', returnElem).button();
	        	} else{
	        		$('<div class="button disable order_out_stock out_own_stock">出库</div>').appendTo($(returnElem));
	        		$('div.button', returnElem).button();
	        	    $('div.button.disable', returnElem).button('disable');
	        	}
	             $( 'div.button' , returnElem).bind('click' , outOwnStock);

            }else{
                //still in arrangedOrders pool.
            	var order_id = pool_order.prop('id');
            	var curElem = $('#'+ order_id, $('#arrangedOrders')).parent();
            	
            	curElem.html(returnElem.html());
            	 $('.update', curElem).click(fillnewitems);

                 $( '.subitems-dialog', curElem ).dialog({
                     autoOpen: false ,
                     height: '500' ,
                     width: '900' ,
                     title: '订单详细信息' ,
                     modal: true ,
                     buttons: { "Close" : function () {$( this ).dialog( "close" );}},
                     close: function () {}
                });
                $( 'a.oitemdetail', curElem ).click( function() {
                	event.preventDefault();
                    var oid = $( this).prop( 'id').split( '_' )[0];
                    $.get(url("order/ajaxgetorderitems/" + oid), function( data ) {
         	    	   $( ".subitems-dialog" ).html(data);
         	    	   $( ".subitems-dialog" ).dialog('open');
         			});
                });
                
                $('.admin-note', curElem).editable({
              	  url: url('order/addadminnote'),
      	    	  title: 'Enter Admin Note',
      	    	  rows: 4,
      	    	  inputclass: 'width240',
      	    	  display: function(value) {
                  	if(value == ''){
                  		value = '<i class="icon-unnoted"></i>';
                  	}else{
                  		if(value.length > 6){value = '<i class="icon-noted"></i>' + value.substr(0, 6) + '...';}
                  		else{value = '<i class="icon-noted"></i>'+value;}
                  	}
      	          	$(this).html(value);
                  }
      	        });
            }
        }
    });
}

function ordercommunicate(event) {
	var communicateBtn = $(this);
	communicateBtn.button({ disabled: true });
	var oid = communicateBtn.prev().prop('id');
	var poolName = communicateBtn.parent().parent().prop('id');
	$.ajax({
        type: "POST" ,
        url: url('order/addcommunicatetime'),
        data: {'oid': oid, 'pool_name':poolName},
        success : function (response){
        	var ret = $.parseJSON(response);
        	if (ret.communicate_time != undefined) {
	        	communicateBtn.prev().append('<span>' + ret.communicate_time + '</span>');
	        	communicateBtn.css('display', 'none');
	        	communicateBtn.parent().css('background-color', '#6495ed');
        	} else {
        		alert(response);
        	}
        },
        error : function(data){
        	communicateBtn.button({ disabled: false });
        }
	});
}
function addUpdate(elem){
      $('<div class="update"><span class="update-icon"></span></div>').appendTo(elem);
       $('.update', elem).bind('click', fillnewitems);
}
function removeUpdate(elem){
       $('.update', elem).unbind('click', fillnewitems);
      $('div.update', elem).remove();
}
function addLock(elem) {
	$('<div class="lockstate"><span class="lock-icon locked"></span></div>').appendTo(elem);
}
function changeLockToTrash(elem){
      $('div.lockstate', elem).remove();
      $('<div class="trash"><span class="trash-icon"></span></div>').appendTo(elem);
}

function changeTrashToLock(elem, state){
      $('div.trash' , elem).remove();
      $('<div class="lockstate"><span class="lock-icon ' + state + '"></span></div>').appendTo(elem);
}
function changeLockState(elem, lockstate){
       var lockElem = $('.lockstate .lock-icon', elem);
       if (lockElem.hasClass('locked')){
            lockElem.removeClass('locked');
      }
       if (lockElem.hasClass('unlocked')){
            lockElem.removeClass('unlocked');
      }
      lockElem.addClass(lockstate);
}

function moveToTrash(event){
    $(this).parent().remove();
    var oid = $('.pool-order', $(this).parent()).prop('id');
    jQuery.ajax({
        type: "POST" ,
        url: url('order/movetotrash'),
        data: {'oid':oid},
        success : function (data){}
    });
}
function outAmazonStock(event){
	var oid = $('.pool-order', $(this).parent()).prop('id');
	$(this).button("disable");
    jQuery.ajax({
        type: "POST" ,
        url: url('order/ajaxupdateamazonstock'),
        data: {'oid':oid},
        dataType: 'json',
        success : function (data){
        	if(data['status'] == 'Success'){
        		//find the oid
        		$('#amazonOrders #'+data['oid']).parent().remove();
        	}
        }
        });
}
function outOwnStock(event){
	var oid = $('.pool-order', $(this).parent()).prop('id');
	$(this).button("disable");
    jQuery.ajax({
        type: "POST" ,
        url: url('order/ajaxupdateownstock'),
        data: {'oid':oid},
        dataType: 'json',
        success : function (data){
        	if(data['status'] == 'Success'){
        		//find the oid
        		$('#readyExportOrders #'+data['oid']).parent().remove();
        	}
        }
        });
}

function refreshAndCompute(event){
   //firstly find all the pools.
   //oid=>{old=>'', new=>'', new_lockstate=>''}
   var orderDeltas = {};
  $('.sort-container .pool-order').each(function(){
         if ($(this).attr('new') != '' && $(this).attr('old') != $(this).attr('new')){
              orderDeltas['order_' + this.id] = {};
              orderDeltas['order_' + this.id]['old'] = $(this ).attr('old');
              orderDeltas['order_' + this.id]['new'] = $(this ).attr('new');
               if ($('div.lockstate span' , $(this).parent()).hasClass('locked')){
                    orderDeltas['order_' + this.id]['new_lockstate'] = 1;
              } else {
                    orderDeltas['order_' + this.id]['new_lockstate'] = 0;
              }
        }
  });
  $('.message_info_total').css({'display':'block'});
  $('<div style="padding:5px 20px 5px 10px; font-size:bold; color:#948; border-radius:3px;"><img src="http://www.dearlover-corsets.com/images/refresh.gif"/><span style="font-size:20px;">正在更新</span></div>').appendTo($('.message_info_total'));
  jQuery.ajax({
    type: "POST" ,
    url: url('order/refreshandcompute'),
    data: {'json' :JSON.stringify(orderDeltas)},
    success : function (data){
    	//delete old dialogs.
    	$(".ui-dialog").remove();
 
            $('#ajax-refresh-container').empty();
        $(data).appendTo($('#ajax-refresh-container'));

        $( 'button, input[type="submit"], div.button').button();
        $('div.button.disable').button('disable');
        $("#readyExportOrders").multisortable({
            connectWith: "#ommitedOrders, #readyExportOrders",
            handle: ".handle" ,
            selectedClass: "ui-selected" ,
              stop: stopReadyExportOrders
        });

        $( "#needImportOrders" ).multisortable({
            connectWith: ".sortable-list" ,
            handle: ".handle" ,
            selectedClass: "ui-selected" ,
              stop: stopNeedImportOrders
        });

        $( "#arrangedOrders" ).multisortable({
            connectWith: ".sortable-list" ,
            handle: ".handle" ,
            selectedClass: "ui-selected" ,
              stop: stopArrangedOrders
        });

        $( "#ommitedOrders" ).multisortable({
            connectWith: "#readyExportOrders, #needImportOrders" ,
            handle: ".handle" ,
            selectedClass: "ui-selected" ,
              stop: stopOmmitedOrders
        });
        $( ".sortable-list" ).selectable({
            filter: "div.sortable-item" ,
            cancel: ".handle"
        });
        $( "#readyExportOrders .lockstate" ).dblclick(lockunlock);
        $( "#ommitedOrders .trash" ).click(moveToTrash);
        $('#arrangedOrders .update').click(fillnewitems);
        $('#arrangedOrders .communicate').click(ordercommunicate);
        $( '.subitems-dialog' ).dialog({
              autoOpen: false ,
              height: '500' ,
              width: '900' ,
              title: '订单详细信息' ,
              modal: true ,
              buttons: { "Close" : function () {$( this ).dialog( "close" );}},
              close: function () {},
              open: function(){
              	//延迟加载图片
              	$('img', this).each(function(){
              		var attr = $(this).attr('original');
              		if (typeof attr !== 'undefined' && attr !== false) {
  	            		var href = $(this).attr('original');
  	            		$(this).attr('src', href);
  	            		$(this).removeAttr('original');
              		}
              	});
              }
        });
        $( 'a.oitemdetail' ).click( function(event) {
        	event.preventDefault();
            var oid = $( this).prop( 'id').split( '_' )[0];
            $.get(url("order/ajaxgetorderitems/" + oid), function( data ) {
 	    	   $( ".subitems-dialog" ).html(data);
 	    	   $( ".subitems-dialog" ).dialog('open');
 			});
        });
        $('.message_info_total').empty();
        $('.message_info_total').css({'display':'none'});
        $('div.button.print.print-stockout').click(function(){
    		window.open(url('order/generateoosprint'), "_blank");
    	});
        $('div.button.print.print-order').click(function(){
    		window.open(url('order/generateorderprint'), "_blank");
    	});
    //$('div.button.refresh-lock-all').click(batchOrderLockedItemsUpdate);
        $('div.button.export-stock-lack').click(function(){
  		  window.open(url('order/generateproductlackfile'), "_blank");
        });
        $('div.button.amazon_csv').click(function(){
      	  window.open(url('order/ajaxgenerateamazonorder'), "_blank"); 
        });
        $('div.button.out_amazon_stock').click(outAmazonStock);
        $('div.button.out_own_stock').click(outOwnStock);
        $('.admin-note').editable(
        	{
          	  url: url('order/addadminnote'),
        	  title: 'Enter Admin Note',
        	  rows: 4,
        	  inputclass: 'width240',
        	  display: function(value) {
              	if(value == ''){
              		value = '<i class="icon-unnoted"></i>';
              	}else{
              		if(value.length > 6){value = '<i class="icon-noted"></i>' + value.substr(0, 6) + '...';}
              		else{value = '<i class="icon-noted"></i>'+value;}
              	}
              	$(this).html(value);
        	}
         });
    	}
  });
}

function stopReadyExportOrders(event, elem) {
    var nestedContainer = $(elem.item).closest('.sortable-list' );
    var elemItems = $('.ui-selected' , nestedContainer);
    if (elemItems.length == 0){
         elemItems = elem.item;
   }
    var nestedId = $(nestedContainer).attr('id' );
    if (nestedId == 'ommitedOrders'){
         $('.lockstate', elemItems).unbind('dblclick', lockunlock);
         changeLockToTrash(elemItems);
         $('.trash', elemItems).bind('click', moveToTrash);
         //remove out inventory button.
         $('div.order_out_stock', elemItems).unbind('click', lockunlock);
         $('div.order_out_stock', elemItems).remove();
   }
   $('.pool-order', elemItems).attr('new',nestedId);
}

function stopNeedImportOrders(event, elem) {
    var nestedContainer = $(elem.item).closest('.sortable-list' );
    var elemItems = $('.ui-selected' , nestedContainer);
    if (elemItems.length == 0){
         elemItems = elem.item;
   }
    var nestedId = $(nestedContainer).attr('id' );
    if ( nestedId == 'ommitedOrders' ){
         changeLockToTrash(elemItems);
         $( '.trash' , elemItems).bind('click' , moveToTrash);
   } else if (nestedId == 'readyExportOrders'){
         changeLockState(elemItems, 'unlocked' );
         $( '.lockstate' , elemItems).bind('dblclick' , lockunlock);
         $('<div class="button order_out_stock out_own_stock" style="color:#ff0000">需更新</div>').appendTo(elemItems);
   } else if (nestedId == 'arrangedOrders'){
         changeLockState(elemItems, 'locked' );
   }
   $( '.pool-order' , elemItems).attr('new' ,nestedId);
}

function stopArrangedOrders(event, elem) {
	var nestedContainer = $(elem.item).closest('.sortable-list' );
    var elemItems = $('.ui-selected' , nestedContainer);
    if (elemItems.length == 0){
         elemItems = elem.item;
   }
    var nestedId = $(nestedContainer).attr('id' );
    if ( nestedId == 'ommitedOrders' ){
         removeUpdate(elemItems);
         changeLockToTrash(elemItems);
         $( '.trash' , elemItems).bind('click' , moveToTrash);
   } else if (nestedId == 'readyExportOrders'){
         removeUpdate(elemItems);
         addLock(elemItems);
         $( '.lockstate' , elemItems).bind('dblclick' , lockunlock);
   } else if (nestedId == 'needImportOrders'){
         removeUpdate(elemItems);
         changeLockState(elemItems, 'unlocked' );
   }
   $( '.pool-order' , elemItems).attr('new' ,nestedId);
}

function stopOmmitedOrders(event, elem) {
    var nestedContainer = $(elem.item).closest('.sortable-list' );
    var nestedId = $(nestedContainer).attr('id' );
    var elemItems = $('.ui-selected' , nestedContainer);
    if (elemItems.length == 0){
         elemItems = elem.item;
   }
    if (nestedId == 'readyExportOrders'){
         $( '.trash' , elemItems).unbind('click' , moveToTrash);
         changeTrashToLock(elemItems, 'unlocked' );
         $( '.lockstate' , elemItems).bind('dblclick' , lockunlock);
         $(elemItems).each(function(){
         	if($(this).attr('data-sid') != '13'){
         		//not lingeriemore.
         		$('<div class="button order_out_stock out_own_stock">出库</div>').appendTo($(this));
         		$('div.button', this).button();
         	} else{
         		$('<div class="button disable order_out_stock out_own_stock">出库</div>').appendTo($(this));
         		$('div.button', this).button();
         	    $('div.button.disable', this).button('disable');
         	}
          });
          $( 'div.button' , elemItems).bind('click' , outOwnStock);
         
         
   } else if (nestedId == 'needImportOrders'){
         $( '.trash' , elemItems).unbind('click' , moveToTrash);
         changeTrashToLock(elemItems, 'unlocked' );
   }
   $( '.pool-order', elemItems).attr('new' ,nestedId);
}

$(document).ready( function(){
      $( 'button, input[type="submit"], div.button').button();
      $('div.button.disable').button('disable');
      $('select').selectBox();
      $( '.refresh_and_compute' ).click(refreshAndCompute);
      $('.adjust_stock').click(function(){
    	  window.open(url('stock/adjuststock'), "_blank");
      });
      $("#readyExportOrders").multisortable({
          connectWith: "#ommitedOrders, #readyExportOrders",
          handle: ".handle" ,
          selectedClass: "ui-selected" ,
            stop: stopReadyExportOrders
      });

      $( "#needImportOrders" ).multisortable({
          connectWith: ".sortable-list" ,
          handle: ".handle" ,
          selectedClass: "ui-selected" ,
            stop: stopNeedImportOrders
      });

      $( "#arrangedOrders" ).multisortable({
          connectWith: ".sortable-list" ,
          handle: ".handle" ,
          selectedClass: "ui-selected" ,
            stop: stopArrangedOrders
      });

      $( "#ommitedOrders" ).multisortable({
          connectWith: "#readyExportOrders, #needImportOrders" ,
          handle: ".handle" ,
          selectedClass: "ui-selected" ,
            stop: stopOmmitedOrders
      });
      $( ".sortable-list" ).selectable({
          filter: "div.sortable-item" ,
          cancel: ".handle"
      });
      $( "#readyExportOrders .lockstate" ).dblclick(lockunlock);
      $( "#ommitedOrders .trash" ).click(moveToTrash);
      $('#arrangedOrders .update').click(fillnewitems);
      $('#arrangedOrders .communicate').click(ordercommunicate);
      
      $( '.subitems-dialog' ).dialog({
            autoOpen: false ,
            height: '500' ,
            width: '900' ,
            title: '订单详细信息' ,
            modal: true ,
            buttons: { "Close" : function () {$( this ).dialog( "close" );}},
            close: function () {},
            open: function(){
            	//延迟加载图片
            	$('img', this).each(function(){
            		var attr = $(this).attr('original');
            		if (typeof attr !== 'undefined' && attr !== false) {
	            		var href = $(this).attr('original');
	            		$(this).attr('src', href);
	            		$(this).removeAttr('original');
            		}
            	});
            }
      });
      $( 'a.oitemdetail' ).click( function(event) {
    	  	event.preventDefault();
            var oid = $( this).prop( 'id').split( '_' )[0];
            $.get(url("order/ajaxgetorderitems/" + oid), function( data ) {
 	    	   $( ".subitems-dialog" ).html(data);
 	    	   $( ".subitems-dialog" ).dialog('open');
 			});
      });
      
      $('div.button.print.print-stockout').click(function(){
    		window.open(url('order/generateoosprint'), "_blank");
    	});
      $('div.button.print.print-order').click(function(){
    	  var param = $(this).parent().prop('id').split('-')[0];
    		window.open(url('order/generateorderprint/' + param), "_blank");
    	});
      $('div.button.export-stock-lack').click(function(){
    		  window.open(url('order/generateproductlackfile'), "_blank");
      });
      
      $('div.button.amazon_csv').click(function(){
    	  window.open(url('order/ajaxgenerateamazonorder'), "_blank"); 
      });
      $('div.button.out_amazon_stock').click(outAmazonStock);
      $('div.button.out_own_stock').click(outOwnStock);

      $('.admin-note').editable(
    	{
    	  url: url('order/addadminnote'),
    	  title: 'Enter Admin Note',
    	  rows: 4,
    	  onblur: 'cancel',
    	  //showbuttons: 'bottom',
    	  display: function(value) {
          	if(value == ''){
          		value = '<i class="icon-unnoted"></i>';
          	}else{
          		if(value.length > 6){value = '<i class="icon-noted"></i>' + value.substr(0, 6) + '...';}
          		else{value = '<i class="icon-noted"></i>'+value;}
          	}
          	$(this).html(value);
    	}
      });
});