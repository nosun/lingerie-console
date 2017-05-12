<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php $this->render('before_body.tpl');
$pageLines = 0;

?>
<body class="print">
<!-- content in -->
<!--  <div id="content"> -->
<div>
<!--main content -->
<h1>打单文档</h1>
<?php $pageLines++;?>
<div id="maincontent">
<div id="main-body">

<table class="t-orders print-main"><caption></caption>
<thead>
<tr class="t-header">
<th>订单号</th>
<th>缺货状态</th>
<th><span style="width:200px;display:inline-block;">运输方式</span>国家</th>
<th>备注</th>
</tr>
<?php $pageLines++;?>
</thead>
<tbody>

<?php $orderCount = 0;
foreach($orders as $k=>$v):
$orderCount++;
?>
<tr class="t-row order-row <?php echo ($orderCount % 2 == 0)? 'odd':'even';?> <?php if($pageLines+1 > 31){echo 'pagebreak'; $pageLines = 0;} ?>" id="oid_<?php echo $v->oid;?>" <?php echo ($v->lack_qty == 0) ? '' : 'style="background-color: #FFFFC6;color: #000000;"';?>>
<?php $pageLines++;?>
<td class="o-number" style="width:100px;"><?php echo $v->number;?></td>
<td class="o-status" style="width:50px;"><?php echo ($v->lack_qty == 0) ? '不缺货' : '缺'.strval($v->lack_qty).'件';?></td>
<td class="o-shipping_method"><span style="width:200px;display:inline-block;"><?php echo $v->shipping_method;?></span><?php echo $v->delivery_country;?></td>
<td class="o-admin_note" <?php if(isset($v->admin_note) && $v->admin_note != '' ){echo 'style="background-color: #444;color: #fff;';}?>"><?php echo $v->admin_note;?></td>
</tr>
<?php 
$oitemCount = 0;
foreach($v->items as $k2=>$order_item):
if(($oitemCount % 4) == 0):?>
<tr class="order-row <?php if($pageLines+5 > 31){echo 'pagebreak'; $pageLines = 0;} ?>" >
<?php $pageLines+=5;?>
<?php endif;?>
<td>

<table id="o-<?php echo $v->oid;?>-item-<?php echo $order_item->p_sn;?>" width="200px" class="t-order-item <?php echo ($order_item->qty == $order_item->current_qty) ? '' : 't-order-item-lack';?>">
<tbody>
<tr><th colspan="2">
    <span class="fl">
        <!-- <span>SN:</span> -->
        <span class="oitem-sn"><?php echo $order_item->p_sn;?></span>
    </span>
    <?php   if($order_item->qty == $order_item->current_qty):?>
        <span class="fr">订购<span class="oitem-qty"><?php echo $order_item->qty;?></span>件</span>
    <?php else:?>
        <span class="fr">订<span class="oitem-qty"><?php echo $order_item->qty;?></span>缺<span class="oitem-lackqty"><?php echo ($order_item->qty - $order_item->current_qty);?></span></span>
    <?php endif;?>
</th></tr>
<tr>
<td class="prodImage">


<?php if(isset($order_item->imageSource)):?>
<img src="<?php 
if(startsWith($order_item->imageSource, 'http')){
    echo $order_item->imageSource;
}else{
    echo url(get_thumbnail($order_item->imageSource));
}?>" title="" alt="" class="product_thumb"/>
<?php else:?>
<img src="<?php echo url('files/default.jpg');?>" title="" alt="" class="product_thumb"/>
<?php endif;?>
</td>
<td><?php if(isset($order_item->data) && $order_item->data != false):?>

<?php foreach($order_item->data as $pName=>$pValue):?>
<dl class="prop-list">
<dt class="prop-name"><?php echo translate($pName);?>: </dt>
<dd class="prop-value"><?php echo $pValue;?></dd>
</dl>
<?php endforeach;?>
<?php endif;?>
<dl class="prop-list">
<dt class="prop-name">总价: </dt>
<dd class="prop-value"><?php echo $order_item->total_amount;?></dd>
</dl>
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
