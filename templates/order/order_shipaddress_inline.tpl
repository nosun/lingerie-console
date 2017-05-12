<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php $this->render('before_body.tpl');
$orderCount = 0;
?>
<body class="print print-address">
<!-- content in -->
<!--  <div id="content"> -->
<div>
<!--main content -->
<h1>地址文档(<?php echo $orderShippingMethods[$filters['shipping_method']];?>)</h1>
<div id="maincontent">
<div id="main-body">
<table class="tbl-shipaddresses print-main"><caption></caption>
<tbody>
<?php foreach ($orders as $index=>$order):?>
<tr <?php if($orderCount > 0 && $orderCount % 15 == 0){echo 'class="pagebreak"';}?>>
<td>
<table class="tbl-address">
<tbody>
<tr class="head <?php echo ($orderCount % 2 == 0)? 'odd':'even';?>">
<td>
	<?php
		$delivery_mobile = '';
		if (isset($order->delivery_mobile)) {
			$delivery_mobile = $order->delivery_mobile;
		} else if (isset($order->delivery_phone)) {
			$delivery_mobile = $order->delivery_phone;
		}
		echo sprintf("%s--%d--%s--%s--%s--%s--%s--%s--%s--%s", $order->number,
			$order->total_qty,
			$order->delivery_first_name,
			$order->delivery_last_name,
			$order->delivery_address,
			$order->delivery_city,
			$order->delivery_province,
			$order->delivery_postcode,
			$order->delivery_country,
			$delivery_mobile);?>
</td>
</tr>
</tbody>
</table>
</td>
</tr>

<?php 
$orderCount++;
endforeach;?>
</tbody>

</table>

</div>
</div>
<!--end main content -->
</div><!-- end content -->
<!-- content out -->
<!-- end wrapper -->
<!-- footer in -->


<!-- footer out -->
</body>
</html>