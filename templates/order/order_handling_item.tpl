<div class="sortable-item" data-sid="<?php echo $order->sid?>">
<div class='handle'><span class='ui-icon ui-icon-carat-2-n-s'></span></div>
<div id="<?php echo $order->oid ?>" class="pool-order" old="<?php echo $poolName?>" new="">
	<a id="<?php echo $order->oid;?>_detail" class="oitemdetail" href=""><?php echo $order->number;?></a>
<div style="display: inline-block;">缺 <span><?php echo $order->lack_qty; ?></span></div>
<a href="#" class="admin-note" data-type="textarea" data-name="arrangedOrders" data-pk="<?php echo $order->oid;?>"><?php if(isset($order->admin_note))echo $order->admin_note; ?></a>

</div>

<?php if($poolName == 'arrangedOrders'):?>
<div class='update'><span class='update-icon'></span></div>
<?php endif;?>
<div class='lockstate'><span class='lock-icon <?php echo $order->status_locking == '0'? 'unlocked':'locked';?>'></span></div>

<!-- this is sub items -->
<?php $this->render('order/order_handling_subitem.tpl', array ('order'=>$order));?>
<!-- end of sub items -->
</div>