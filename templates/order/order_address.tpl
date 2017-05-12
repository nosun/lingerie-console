<dt class="ship-address" style="text-align:center;font-weight:bold;padding: 5px 0;background-color: #f7f7f7;border-radius: 5px;border: 1px solid #dddddd;margin: 5px 0;">Order <?php echo $order->number;?>
<span class="button ui-button ui-widget ui-state-default ui-corner-all" id="edit-address-btn">编辑地址</span>
<span class="button ui-button ui-widget ui-state-default ui-corner-all" id="apply-edit-address-btn" data-pk="<?php echo $order->oid;?>">确定</span>
</dt>
<dd>
<table class="tbl-address inline">
<tbody>

<tr>
<th>First Name</th>
<td><span class="addr-prop" data-prop="delivery_first_name"><?php echo $order->delivery_first_name;?></span></td>
<th>Last Name</th>
<td><span class="addr-prop" data-prop="delivery_last_name"><?php echo $order->delivery_last_name;?></span></td>
</tr>
<tr>
<th>Email</th>
<td><span class="addr-prop" data-prop="delivery_email"><?php echo $order->delivery_email;?></span></td>
<th>Tel.</th>
<td><span class="addr-prop" data-prop="<?php if(!empty($order->delivery_mobile)) {echo 'delivery_mobile';}elseif(!empty($order->delivery_phone)) {echo 'delivery_phone';}?>"><?php if(!empty($order->delivery_mobile)) {echo $order->delivery_mobile;}elseif(!empty($order->delivery_phone)) {echo $order->delivery_phone;}?></span></td>
</tr>

<tr>
<th>ZIPCode</th>
<td><span class="addr-prop" data-prop="delivery_postcode"><?php echo $order->delivery_postcode;?></span></td>
<th>City</th>
<td><span class="addr-prop" data-prop="delivery_city"><?php echo $order->delivery_city;?></span></td>
</tr>
<tr>
<th>Province</th>
<td><span class="addr-prop" data-prop="delivery_province"><?php echo $order->delivery_province;?></span></td>
<th>Country</th>
<td><span class="addr-prop" data-prop="delivery_country"><?php echo $order->delivery_country;?></span></td>
</tr>

<tr>
<th>Address</th>
<td colspan="3"><span class="addr-prop" data-prop="delivery_address"><?php echo $order->delivery_address;?></span></td>
</tr>
</tbody>

</table>
</dd>
<script>

$('#edit-address-btn').click(function(){
	$(this).hide();
	$('#apply-edit-address-btn').show();
	
	var propList = {};
	$('.addr-prop', $(this).parent().siblings()).each(function(){
		view2Edit('addr-prop', 'span', $(this).parent());
	});
});
$('#apply-edit-address-btn').click(function(){
	var applyEditAddressBtn = $(this);
	
	var oid = applyEditAddressBtn.attr('data-pk');
	var propList = {};
	$('.addr-prop', applyEditAddressBtn.parent().siblings()).each(function(){
    	var propName = $(this).attr('data-prop');
		var newValue = $.trim(getNewValue('addr-prop', 'span', $(this).parent(), 'input'));
		propList[propName] = newValue;
	});
	
	$.ajax({
    	url: "<?php echo url('order/updateorderaddress'); ?>",
        type: 'post',
        data: {'oid': oid, 'prop-list' : propList},
    }).done(function(response){
        response = $.parseJSON(response);
        if (response.success) {
        	$('.addr-prop', applyEditAddressBtn.parent().siblings()).each(function(){
            	var newValue = $.trim(getNewValue('addr-prop', 'span', $(this).parent(), 'input'));
        		edit2View('addr-prop', newValue, 'span', $(this).parent(), 'input');
        	});
        	applyEditAddressBtn.hide();
        	$('#edit-address-btn').show();
        } else {
			alert(response.message);
        }
    }).fail(function(jqXHR, textStatus ) {
        alert(textStatus);
    });
});
</script>