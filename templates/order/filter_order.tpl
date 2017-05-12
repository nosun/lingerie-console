<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php $this->render('before_body.tpl');?>
<body class="syn-asfe-body">
<?php $this->render('header.tpl');?>
<!-- content in -->
<div id="content">
<!--main content -->
<div id="maincontent">

<div id="main-body">
<div class="main-head">
		<h1>订单过滤</h1>
		<div class="button-group">
			<div class="button remote_sync_latest_order"><a href="<?php echo url('order/syncorders');?>">Sync Orders</a></div>
			<?php $this->render('onoffswitch.tpl');?>
			<div class="message_info_total"></div>
		</div>
</div>


<form id="orderfilter" name="orderfilter" method="post" action="<?php echo url('order/orderfilter');?>">
<table class="filter">

<tbody>
<tr id="order-filter" style="background-color:#ffeeff">
<th class="filter-cell"><b>过滤：</b></th>
<td class="f-number_pre w100"><input type="text" name="number_pre_filter" value="<?php echo isset($filters['number'])? $filters['number']:'订单前缀'?>" onblur="if(this.value==''){this.value='订单前缀';}" onfocus="if(this.value=='订单前缀'){this.value='';}"/></td>
<td class="f-createdafter w100"><input type="text" name="createdafter_filter" class="datepicker" value="<?php echo isset($filters['created >=']) ? date('m/d/Y', $filters['created >=']) : date('m/d/Y', time());?>" /></td>
<td class="f-createdbefore w100"><input type="text" name="createdbefore_filter" class="datepicker" value="<?php echo isset($filters['created <=']) ? date('m/d/Y', $filters['created <=']) : date('m/d/Y', time());?>" /></td>
<td class="f-status w100">

<select name="status_filter" id="status_filter">
<?php foreach($orderStatuses as $statusOpt=>$statusVal):?>
<option value="<?php echo $statusOpt?>" <?php echo (isset($filters['status']) && $filters['status'] === strval($statusOpt))?'selected':'';?>><?php echo $statusVal;?></option>
<?php endforeach;?>
</select>
</td>
<td class="f-status_payment w100">
<select name="status_payment_filter" id="status_payment_filter">
<?php foreach($orderPaymentStatuses as $paymentStatusOpt=>$paymentStatusVal):?>
<option value="<?php echo $paymentStatusOpt?>" <?php echo (isset($filters['status_payment']) && $filters['status_payment'] === strval($paymentStatusOpt))?'selected':'';?>><?php echo $paymentStatusVal;?></option>
<?php endforeach;?>
</select>
</td>
<td class="f-payment_method w100">
<select name="payment_method_filter" id="payment_method_filter">
<?php foreach($orderPaymentMethods as $paymentMethodOpt=>$paymentMethodVal):?>
<option value="<?php echo $paymentMethodOpt?>" <?php echo (isset($filters['payment_method']) && $filters['payment_method'] === $paymentMethodOpt)?'selected':'';?>><?php echo $paymentMethodVal;?></option>
<?php endforeach;?>
</select>
</td>
<td class="f-status_shipping w100">
<select name="status_shipping_filter" id="status_shipping_filter">
<?php foreach($orderShippingStatuses as $shippingStatusOpt=>$shippingStatusVal):?>
<option value="<?php echo $shippingStatusOpt?>" <?php echo (isset($filters['status_shipping']) && $filters['status_shipping'] === strval($shippingStatusOpt))?'selected':'';?>><?php echo $shippingStatusVal;?></option>
<?php endforeach;?>
</select>
</td>
<td class="f-shipping_method w100">
<select name="shipping_method_filter" id="shipping_method_filter">
<?php foreach($orderShippingMethods as $shippingMethodOpt=>$shippingMethodVal):?>
<option value="<?php echo $shippingMethodOpt?>" <?php echo (isset($filters['shipping_method']) && $filters['shipping_method'] === $shippingMethodOpt)?'selected':'';?>><?php echo $shippingMethodVal;?></option>
<?php endforeach;?>
</select>
</td>

<td class="f-inventory w100">
<select name="outinventory_filter" id="outinventory_filter">
<?php foreach($outInventoryStatuses as $outinventoryOpt=>$outinventoryVal):?>
<option value="<?php echo $outinventoryOpt?>" <?php echo (isset($filters['outinventory']) && $filters['outinventory'] === strval($outinventoryOpt))?'selected':'';?>><?php echo $outinventoryVal;?></option>
<?php endforeach;?>
</select>
</td>

<td ><input type="submit" name="apply_filters" class="button filter_order" value="过滤订单"/>
<input type="submit" name="clear_filters" class="button filter_order" value="重置过滤条件"/>
</td>
</tr>
</tbody>
</table>
</form>


<?php $this->render('order/order_list.tpl');?>


<div class="pagination"><?php echo $pagination;?></div>
</div>
</div>
<!--end main content -->
<div id="showaddress-dialog" title="Shipping Information"></div>
</div><!-- end content -->
<!-- content out -->
<!-- end wrapper -->
<!-- footer in -->
<?php $this->render('footer.tpl')?>
<script type="text/javascript" src="<?php echo url("js/jqueryui-editable.min.js");?>"></script>
<script type="text/javascript" src="<?php echo url("js/order.js");?>"></script>
<!-- footer out -->
</body>
</html>