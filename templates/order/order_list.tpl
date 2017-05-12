<table class="t-orders"><caption></caption>
<thead>
<tr class="t-header">
<td style="width:300px;">订单号</td>
<td>下单时间</td>
<td>发货时间</td>
<td>订单状态</td>
<td>付款状态</td>
<td>付款方式</td>
<td>运输状态</td>
<td>运输方式</td>
<td>数量</td>
<td>总价格</td>
<td>说明</td>
<td>选项</td>
</tr>
</thead>
<tbody>

<?php $orderCount = 0;
foreach($orders as $k=>$v):
$orderCount++;
?>
<tr class="t-row order-row <?php echo ($orderCount % 2 == 0)? 'odd':'even';?>" id="oid_<?php echo $v->oid;?>">
<td class="o-number"><?php echo $v->number;?>
<?php if (isset($v->data) && $v->data !== ''):
  	if(!is_array($v->data)):
  		$v->data = unserialize($v->data);
  	endif;?>
	<span class="shipping-no"><?php echo isset($v->data['shipping_no'])? $v->data['shipping_no']:'';?></span>
<?php endif;?>
<a href="#" class="admin-note" data-type="textarea" data-pk="<?php echo $v->oid;?>"><?php if(isset($v->admin_note))echo $v->admin_note; ?></a>
</td>
<td class="o-created"><?php echo date('Y年m月d日',intval($v->created));?></td>
<td class="o-created"><?php if ($v->finished > 0) {echo date('Y年m月d日',intval($v->finished));}?></td>
<td class="o-status">
<?php if($v->sid == 13):?>
<?php
  if($v->status==0): echo '待处理';
  elseif($v->status==1): echo '处理中';
  elseif($v->status==2): echo '已完成';
  elseif($v->status==-1): echo '已取消';
  endif;
?>
<?php else:?>
<a href="#" data-type="select" data-name="status" data-pk="<?php echo $v->oid?>" data-source="<?php echo url('order/getselectoptions/status')?>">
<?php
  if($v->status==0): echo '待处理';
  elseif($v->status==1): echo '处理中';
  elseif($v->status==2): echo '已完成';
  elseif($v->status==-1): echo '已取消';
  endif;
?>
</a>
<?php endif;?>
</td>

<td class="o-status_payment"><?php
  if($v->status_payment==0):
     echo '未付款';
  elseif($v->status_payment==1):
     echo '已付款';
  elseif($v->status_payment==2):
  	 echo 'Partially Refunded';
  elseif($v->status_payment==3):
  	 echo 'Refunded';
  endif;?>
</td>
  
<td class="o-payment_method"><?php echo $v->payment_method;?></td>
<td class="o-status_shipping">

<?php if($v->sid == 13 || $v->finished > 0):?>
<?php
  if($v->status_shipping==0):
     echo '未送货';
  elseif($v->status_shipping==1):
     echo '已送货';
  endif;
?>
<?php else:?>
<a href="#" data-type="select" data-name="status_shipping" data-pk="<?php echo $v->oid?>" data-source="<?php echo url('order/getselectoptions/status_shipping')?>"><?php
  if($v->status_shipping==0):
     echo '未送货';
  elseif($v->status_shipping==1):
     echo '已送货';
  endif;?></a>
<?php endif;?>
</td>
<td class="o-shipping_method"><?php echo $v->shipping_method;?></td>
<td class="o-total_qty"><?php echo $v->total_qty;?></td>
<td class="o-total_amount"><?php echo $v->currency . $v->pay_amount;?></td>
<td class="o-remark"><?php echo $v->remark;?></td>
<td class="o-functions">
<div id="buttons_oid_<?php echo $v->oid;?>">
<div class="button view_address">查看地址</div>
<div class="message_info" style="display:none;"></div>
</div>
</td>
</tr>
<tr class="order-row switcher-support">
<td colspan="11">

<?php foreach($v->items as $k2=>$order_item):?>
<div class="order-item-grid">
<table id="o-<?php echo $v->oid;?>-item-<?php echo $order_item->p_sn;?>" width="200px" class="t-order-item <?php echo ($order_item->qty == $order_item->current_qty) ? '' : 't-order-item-lack';?>">
<tbody>
<tr>
	<th colspan="2"><span class="fl"><span class="oitem-sn"><?php echo $order_item->p_sn;?></span></span> <span class="fr"><span class="oitem-qty">订<?php echo $order_item->qty;?></span><span class="oitem-lack_qty">缺<?php echo ($order_item->qty - $order_item->current_qty);?></span></span>
	</th>
</tr>
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
<dd class="prop-value"><?php echo $v->currency . $order_item->total_amount;?></dd>
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
<script>
$(function(){
	$('.admin-note').editable({
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
});
</script>