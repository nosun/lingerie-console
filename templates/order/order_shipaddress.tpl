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
<tr <?php if($orderCount > 0 && $orderCount % 5 == 0){echo 'class="pagebreak"';}?>>
<td>
<table class="tbl-address">
<tbody>
<tr class="head <?php echo ($orderCount % 2 == 0)? 'odd':'even';?>">
<th>单号</th>
<td><?php echo $order->number;?></td>
<th>件数</th>
<td><?php echo $order->total_qty;?></td>
</tr>
<tr>
<th>姓氏</th>
<td><?php echo $order->delivery_last_name;?></td>
<th>名字</th>
<td><?php echo $order->delivery_first_name;?></td>
</tr>
<tr>

<th>电话</th>
<td><?php if(isset($order->delivery_mobile)) {echo $order->delivery_mobile;} if(isset($order->delivery_phone)) {echo $order->delivery_phone;}?></td>
<th>邮编</th>
<td><?php echo $order->delivery_postcode;?></td>
</tr>

<tr>
<th>地址</th>
<td><?php echo $order->delivery_address;?></td>
<th>城市</th>
<td><?php echo $order->delivery_city;?></td>
</tr>
<tr>
<th>省份</th>
<td><?php echo $order->delivery_province;?></td>
<th>国家</th>
<td colspan="3"><?php echo $order->delivery_country;?></td>
<!-- 
<th>邮件</th>
<td><?php echo $order->delivery_email;?></td>
 -->
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