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
		<h1>出单查询</h1>
		<div class="button-group">
			<div class="button remote_sync_latest_order"><a href="<?php echo url('order/syncorders');?>">Sync Orders</a></div>
			<?php $this->render('onoffswitch.tpl');?>
			<div class="message_info_total"></div>
		</div>
		
		<div class="distribution-group">
			<div class="button print print-orderaddress">生成地址文档</div>
			<div class="button print print-orderaddress-inline">生成地址文档2</div>
			<div class="button updatestock"><a href="<?php echo url('order/updatestock');?>" style="color:white;">出库（不可重复）</a></div>
		</div>
</div>


<form id="orderfilter" name="orderfilter" method="post" action="<?php echo url('order/processed');?>">
<table class="filter">

<tbody>
<tr id="order-filter" style="background-color:#ffeeff">
<th class="filter-cell" style="width:120px;"><b>发货时间：</b></th>
<td class="f-updated w100"><input type="text" name="updated_filter" class="datepicker" value="<?php echo isset($filters['updated']) ? date('m/d/Y', $filters['updated']) : date('m/d/Y', time());?>" /></td>

<td class="f-inventory w100">
<select name="outinventory_filter" id="outinventory_filter">
<?php foreach($outInventoryStatuses as $outinventoryOpt=>$outinventoryVal):?>
<option value="<?php echo $outinventoryOpt?>" <?php echo (isset($filters['outinventory']) && $filters['outinventory'] === strval($outinventoryOpt))?'selected':'';?>><?php echo $outinventoryVal;?></option>
<?php endforeach;?>
</select>
</td>

<td class="f-shipping_method w100">
<select name="shipping_method_filter" id="shipping_method_filter">
<?php foreach($orderShippingMethods as $shippingMethodOpt=>$shippingMethodVal):?>
<option value='<?php echo $shippingMethodOpt?>' <?php echo (isset($filters['shipping_method']) && $filters['shipping_method'] === $shippingMethodOpt)?'selected':'';?>><?php echo $shippingMethodVal;?></option>
<?php endforeach;?>
</select>
</td>


<td ><input type="submit" name="apply_filters" class="button filter_order" value="查看当日处理的订单" style="width:120px;"/>
<input type="submit" name="clear_filters" class="button filter_order" value="重置日期"/>
</td>
</tr>
</tbody>
</table>
</form>

<table class="t-orders"><caption></caption>
<thead>
<tr class="t-header">
<td>订单号(追踪单号)</td>
<td>运费</td>
<td>下单时间</td>
<td>发货时间</td>
<td>订单状态</td>
<td>付款状态</td>
<td>付款方式</td>
<td>运输状态</td>
<td>运输方式</td>
<td>数量</td>
<td>总价格</td>
<td>客户</td>
<td>是否出库</td>
<td>说明</td>
</tr>
</thead>
<tbody>

<?php $orderCount = 0;
foreach($orders as $k=>$v):
$orderCount++;
?>
<tr class="t-row order-row <?php echo ($orderCount % 2 == 0)? 'odd':'even';?>" id="oid_<?php echo $v->oid;?>" <?php if($v->status_handling == '3'):?> style="background-color:#ffffff;"<?php endif;?>>
<td class="o-number">
<?php if($v->status_handling == '3'):?>
<span class="ui-icon amazon-icon" style="display:inline-block;"></span>
<?php endif;?>
<?php echo $v->number;?> <a href="#" class="shipping-no" data-name="shipping_no" data-type="text" data-pk="<?php echo $v->oid?>" data-placement="right" data-placeholder="Required" data-title="Enter Shipping Number"><?php if (isset($v->data) && $v->data !== ''){
  	if(!is_array($v->data)){
  		$v->data = unserialize($v->data);
  	}
	echo isset($v->data['shipping_no'])? $v->data['shipping_no']:'';
  }?>
</a></td>
<td class="o-actual_shipping_fee">
	<a class="actual-shipping-fee" href="#" data-type="text" data-name="actual_shipping_fee" data-pk = "<?php echo $v->oid;?>"><?php echo isset($v->actual_shipping_fee) ? $v->actual_shipping_fee : '';?></a>
</td>
<td class="o-created"><?php echo date('Y年m月d日',intval($v->created));?></td>
<!-- <td class="o-finished"><?php echo isset($filters['updated']) ? date('Y年m月d日', $filters['updated']) : date('Y年m月d日', time());?></td>-->
<td class="o-finished"><?php echo date('Y年m月d日',intval($v->finished));?></td>
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
<td class="o-payment_method"><?php echo $v->payment_method;?></td>
<td class="o-status_shipping"><?php
  if($v->status_shipping==0):
     echo '未送货';
  elseif($v->status_shipping==1):
     echo '已送货';
  endif;?></td>
<td class="o-shipping_method"><?php echo $v->shipping_method;?></td>

<td class="o-total_qty"><?php echo $v->total_qty;?></td>
<td class="o-total_amount"><?php echo $v->pay_amount;?></td>
<td class="o-user"><?php echo $v->delivery_email;?></td>
<td class="o-inventory"><?php
  if($v->outinventory==0):
     echo '未出库';
  elseif($v->outinventory==1):
     echo '已出库';
  endif;?></td>

<td class="o-remark"><?php echo $v->remark;?></td>

</tr>
<tr class="order-row switcher-support">
<td colspan="11">

<?php foreach($v->items as $k2=>$order_item):?>
<div class="order-item-grid">
<table id="o-<?php echo $v->oid;?>-item-<?php echo $order_item->p_sn;?>" width="200px" class="t-order-item <?php echo ($order_item->qty == $order_item->current_qty) ? '' : 't-order-item-lack';?>">
<tbody>
<tr><th colspan="2"><span class="fl"><span class="oitem-sn"><?php echo $order_item->p_sn;?></span></span> <span class="fr"><span class="oitem-qty">订<?php echo $order_item->qty;?></span><span class="oitem-lack_qty">缺<?php echo ($order_item->qty - $order_item->current_qty);?></span></span>
</th></tr>
<tr>
<td class="prodImage">
<?php if(isset($order_item->imageSource)):?>
<a href="<?php echo url($order_item->imageSource);?>"><img src="<?php 
if(startsWith($order_item->imageSource, 'http')){
	echo $order_item->imageSource;
}else{
	echo url(get_thumbnail($order_item->imageSource));
}?>" title="" alt="" class="product_thumb"/></a>
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
</div>
<?php endforeach;?>
</td>
</tr>
<?php endforeach;?>
</tbody>
</table>
<div class="pagination"><?php echo $pagination;?></div>
</div>
</div>
<!--end main content -->
</div><!-- end content -->
<!-- content out -->
<!-- end wrapper -->
<!-- footer in -->
<script language="javascript">
	$('.f-updated input.datepicker').datepicker();
</script>
<?php $this->render('footer.tpl')?>
<script type="text/javascript" src="<?php echo url("js/jqueryui-editable.min.js");?>"></script>
<script type="text/javascript" src="<?php echo url("js/order.js?v2");?>"></script>
<!-- footer out -->
</body>
</html>