<?php if (isset($order)): ?>
<div class="subitems-dialog" id="<?php echo $order->oid;?>_detail_dialog">
<ul class="order-price-info">
    <li>订单编号:<?php echo $order->number; ?></li>
    <li>订单总价:<?php echo $order->currency . $order->pay_amount; ?></li>
    <li>产品总价:<?php echo $order->currency . $order->total_amount; ?></li>
    <li>运费总价:<?php echo $order->currency . $order->fee_amount; ?></li>
    <li>运送方式:<?php echo $order->shipping_method; ?></li>
    <li>总件数:<?php $total_qty = 0; foreach($order->items as $order_item){$total_qty += $order_item->qty;} echo $total_qty; ?>件</li>
</ul>
<?php if (isset($order->refundAmount)): ?>
<ul class="order-price-info">
    <li>退款总额(不含运费):<?php echo $order->currency . $order->refundAmount->product /* + $order->refundAmount->shipping;*/ ?></li>
</ul>
<?php endif;?>
<div class="subitems">
<?php foreach($order->items as $k2=>$order_item):?>
<div class="order-item-grid">
<table id="o-<?php echo $order->oid;?>-item-<?php echo $order_item->p_sn;?>" width="200px" class="t-order-item <?php echo ($order_item->qty == $order_item->current_qty) ? '' : 't-order-item-lack';?>">
<tbody>
<tr><th colspan="2"><span class="fl"><span class="oitem-sn"><?php echo $order_item->p_sn;?></span></span> <span class="fr"><span class="oitem-qty">订<?php echo $order_item->qty;?></span><span class="oitem-lack_qty">缺<?php echo ($order_item->qty - $order_item->current_qty);?></span></span>
</th></tr>
<tr>
<td class="prodImage">
<?php if(isset($order_item->imageSource)):?>
<img original="<?php 
if(startsWith($order_item->imageSource, 'http')){
	echo $order_item->imageSource;
}else{
	echo url(get_thumbnail($order_item->imageSource));
}?>" title="" alt="" class="product_thumb"/>
<?php else:?>
<img original="<?php echo url('files/default.jpg');?>" title="" alt="" class="product_thumb"/>
<?php endif;?>
</td>	
<td><?php if(isset($order_item->data) && $order_item->data != false):?>
<?php 
$order_item->data = $order_item->data;
foreach($order_item->data as $pName=>$pValue):?>
<?php if (strcasecmp($pName, "color") != 0):?>
<dl class="prop-list">
<dt class="prop-name"><?php echo translate($pName);?>: </dt>
<dd class="prop-value"><?php echo $pValue;?></dd>
</dl>
<?php endif;?>
<?php endforeach;?>

<dl class="prop-list">
<dt class="prop-name">单价: </dt>
<dd class="prop-value"><?php echo $order->currency . ($order_item->total_amount / $order_item->qty);?></dd>
</dl>
<dl class="prop-list">
<dt class="prop-name">供商: </dt>
<dd class="prop-value"><?php $product = Product_Model::getInstance()->getProductBySn($order_item->p_sn); echo empty($product->suppliers) ? '' : $product->suppliers;?></dd>
</dl>
<?php endif;?>
</td>
</tr>
</tbody>
</table>
</div>
<?php endforeach;?>
</div>
</div>
<?php else: ?>
<div class="subitems-dialog"></div>
<?php endif; ?>