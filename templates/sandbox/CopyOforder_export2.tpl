<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php $this->render('before_body.tpl');?>
<body class="print">
<!-- content in -->
<div id="content">
<!--main content -->
<h1>打单文档</h1>
<div id="maincontent">
<div id="main-body">

<table class="t-orders print-main"><caption></caption>
<thead>
<tr class="t-header">
<th>订单号</th>
<th>订单状态</th>
<th>付款状态</th>
<th>运输方式</th>
</tr>
</thead>
<tbody>

<?php $orderCount = 0;
foreach($orders as $k=>$v):
$orderCount++;
?>
<tr class="t-row order-row <?php echo ($orderCount % 2 == 0)? 'odd':'even';?>" id="oid_<?php echo $v->oid;?>">
<td class="o-number"><?php echo $v->number;?></td>

<td class="o-status"><?php
  if($v->status==0): echo '待处理';
  elseif($v->status==1): echo '处理中';
  elseif($v->status==2): echo '已完成';
  elseif($v->status==-1): echo '已取消';
  elseif($v->status==-2): echo '已删除';
  endif;
?></td>
<td class="o-status_payment"><?php
  if($v->status_payment==0):
     echo '未付款';
  elseif($v->status_payment==1):
     echo '已付款';
  endif;?></td>
<td class="o-shipping_method"><?php echo $v->shipping_method;?></td>
</tr>
<?php 
$oitemCount = 0;
foreach($v->items as $k2=>$order_item):
if(($oitemCount % 4) == 0):?>
<tr class="order-row">
<?php endif;?>
<td>

<table id="o-<?php echo $v->oid;?>-item-<?php echo $order_item->p_sn;?>" class="t-order-item">
<tbody>
<tr>
<th style="width:60%;"><span class="fl"><span>SN:</span><span class="oitem-sn"><?php echo $order_item->p_sn;?></span></span></th>
<th colspan="2"><span class="fr">订购<span class="oitem-qty"><?php echo $order_item->qty;?></span>件</span></th>
</tr>
<tr class="oitem_details">
<td colspan="2">
<table style="width:100%;">
<tbody>
<tr>
<td style="width:93px;height:95px;">
<?php if(isset($order_item->imageSource)):?>
<a href="<?php echo url($order_item->imageSource);?>"><img src="<?php echo url(get_thumbnail($order_item->imageSource));?>" title="" alt="" class="product_thumb"/></a>
<?php else:?>
<img src="<?php echo url('files/default.jpg');?>" title="" alt="" class="product_thumb"/>
<?php endif;?>
</td>

<?php if(isset($order_item->data)):?>
<td style="text-align:right; width:25%;">
<?php foreach($order_item->data as $pName=>$pValue):?>
<p><?php echo translate($pName);?>:</p>
<?php endforeach;?>
 </td>
 <td style="width:25%;">
<?php foreach($order_item->data as $pName=>$pValue):?>
<p><?php echo $pValue;?></p>
<?php endforeach;?>
</td>
<?php else:?>
<td colspan="3"></td>

<?php endif;?>
</tr>
</tbody>


</table>
</td>




</tr>
</tbody>
</table>

</td>

<?php if(($oitemCount % 4) == 3):?>
</tr>
<?php elseif($oitemCount === count($v->items)-1):
$remains = 3 - $oitemCount % 4;
while($remains-- > 0):?>
<td></td>
<?php endwhile;?>
</tr>
<?php endif;?>


<?php $oitemCount++; endforeach;?>

<?php endforeach;?>
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