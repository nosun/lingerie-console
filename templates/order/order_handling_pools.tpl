<table width="100%" class="order-polls-table">
	<thead>
		<tr>
			<th id="readyExportOrders-th">准备出货订单 <div class="button print print-order">打单文档</div></th>
			<th id="needImportOrders-th">需要调货订单<div class="button print print-order">打单文档</div><div class="button print print-stockout">缺货文档</div><div class="button export-stock-lack" style="float:right;">导出缺货表</div></th>
			<th id="arrangedOrders-th">已做安排订单<div class="button print print-order">打单文档</div><!--  <div class="button refresh-lock-all" style="float:right;">补充所有订单</div>--></th>
			<th id="ommitedOrders-th">暂不处理订单</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
			
			<div class="sort-container">
			<?php $count_amazon = (isset($amazonOrders) ? count($amazonOrders) : 0);
			if($count_amazon > 0):?>
			<table class="amazon-container" style="width:100%;border-collapse: collapse;">
			<tbody>
			<tr>
			<td style="width:70%;padding:0;border-bottom:4px solid green;">
			<div id="amazonOrders">
			<?php foreach ($amazonOrders as $order): $count_amazon--;?>
			<div class="sortable-item" style="<?php echo ($count_amazon == 0)? 'border-bottom:none;':'';?>cursor:default;" data-sid="<?php echo $order->sid?>">
			<div class='handle'><span class='ui-icon amazon-icon'></span></div>
			<div id="<?php echo $order->oid ?>" class="pool-order"><a id="<?php echo $order->oid;?>_detail" class="oitemdetail" href=""> <?php echo $order->number; ?></a>
			<div style="display: inline-block;">缺 <span> <?php echo $order->lack_qty; ?></span></div>

			<a href="#" class="admin-note" data-type="textarea" data-name="amazonOrders" data-pk="<?php echo $order->oid;?>">
				<?php if(isset($order->admin_note))echo $order->admin_note; ?></a>
			</div>
			<div class="button order_out_stock out_amazon_stock">出库</div>
			</div>
			<?php endforeach ;
			?></div>
			</td>
			<td style="width:10%;padding:0;border-bottom:4px solid green;border-left:1px solid #aaa;text-align:center;">
			<div id="amazon-functions">
					<div class="button amazon_csv" style="float:none;margin:5px;">下单表</div>
			</div>
			</td>
			</tr>
			</tbody>
			</table>
			<?php endif;?>
			
			<div id="readyExportOrders" class="sortable-list" style="min-height:100px;"><?php if ($orders_pools['readyExportOrders']):
			foreach ($orders_pools['readyExportOrders'] as $order): ?>
			<div class="sortable-item" data-sid="<?php echo $order->sid?>">
			<div class='handle'><span class='ui-icon ui-icon-carat-2-n-s'></span></div>
			<div id="<?php echo $order->oid ?>" class="pool-order" old="readyExportOrders" new="" ><a
				id="<?php echo $order->oid;?>_detail" class="oitemdetail" href=""> <?php echo $order->number; ?></a>
			<div style="display: inline-block;">缺 <span> <?php echo $order->lack_qty; ?></span></div>
			
			<a href="#" class="admin-note" data-type="textarea" data-name="readyExportOrders" data-pk="<?php echo $order->oid;?>"><?php if(isset($order->admin_note))echo $order->admin_note; ?></a>

			</div>
			<div class="button order_out_stock out_own_stock">出库</div>
			<div class='lockstate'><span class='lock-icon <?php echo $order->status_locking == '0'? 'unlocked':'locked';?>'></span></div>
			</div>
			<?php endforeach ;
			endif ;
			?></div>
			</div>
			</td>
			<td>
			<div id="needImportOrders" class="sort-container sortable-list"><?php if ($orders_pools['needImportOrders']):
			foreach ($orders_pools['needImportOrders'] as $order):?>
			<div class="sortable-item" data-sid="<?php echo $order->sid?>">
			<div class='handle'><span class='ui-icon ui-icon-carat-2-n-s'></span></div>

			<div id="<?php echo $order->oid?>" class="pool-order" old="needImportOrders" new=""><a
				id="<?php echo $order->oid;?>_detail" class="oitemdetail" href=""> <?php echo $order-> number; ?></a>
			<div style="display: inline-block;">缺 <span><?php echo $order-> lack_qty;?></span></div>
			<a href="#" class="admin-note" data-type="textarea" data-name="needImportOrders" data-pk="<?php echo $order->oid;?>"><?php if(isset($order->admin_note))echo $order->admin_note; ?></a>

			</div>
			<div class='lockstate'><span class='lock-icon unlocked'></span></div>
			<!-- this is sub items -->
			<?php //$this->render('order/order_handling_subitem.tpl', array('order'=>$order));?>
			<!-- end of sub items --></div>
			<?php endforeach ;
			endif ;
			?></div>
			</td>
			<td>
			<div id="arrangedOrders" class="sort-container sortable-list"><?php if($orders_pools['arrangedOrders']):
			foreach($orders_pools['arrangedOrders'] as $order): ?>
			<div class="sortable-item" data-sid="<?php echo $order->sid?>" <?php if ($order->communicate_time > 0){echo 'style="background-color:#6495ed;"';}?>>
			<div class='handle'><span class='ui-icon ui-icon-carat-2-n-s'></span></div>
			<div id="<?php echo $order->oid ?>" class="pool-order" old="arrangedOrders" new="">
				<a id="<?php echo $order->oid;?>_detail" class="oitemdetail" href=""><?php echo $order->number;?></a>
			<div style="display: inline-block;">缺 <span><?php echo $order-> lack_qty; ?></span></div>
			
			<a href="#" class="admin-note" data-type="textarea" data-name="arrangedOrders" data-pk="<?php echo $order->oid;?>"><?php if(isset($order->admin_note))echo $order->admin_note; ?></a>
			<?php if ($order->communicate_time > 0): ?>
			<span><?php echo date("Y/m/d:H", $order->communicate_time);?></span>
			<?php endif; ?>
			</div>
			<?php if ($order->communicate_time == 0): ?>
			<div class="button communicate">沟通</div>
			<?php endif; ?>
			<div class='update'><span class='update-icon'></span></div>
			<!-- <div class='lockstate'><span class='lock-icon locked'></span></div> -->

			<!-- this is sub items -->
			<?php //$this->render('order/order_handling_subitem.tpl', array ('order'=>$order));?>
			<!-- end of sub items -->
			</div>
			<?php endforeach ;
			endif ;
			?>
			<?php ?>
			</div>
			</td>
			<td>

			<div id="ommitedOrders" class="sort-container sortable-list"><?php if ($orders_pools['ommitedOrders']):
			foreach ($orders_pools['ommitedOrders'] as $order): ?>
			<div class="sortable-item" data-sid="<?php echo $order->sid?>">
			<div class='handle'><span class='ui-icon ui-icon-carat-2-n-s'></span></div>
			<div id="<?php echo $order->oid;?>" class="pool-order" old="ommitedOrders" new="">
				<a id="<?php echo $order->oid;?>_detail" class="oitemdetail" href=""><?php echo $order->number;?></a>
				
				<a href="#" class="admin-note" data-type="textarea" data-name="ommitedOrders" data-pk="<?php echo $order->oid;?>"><?php if(isset($order->admin_note))echo $order->admin_note; ?></a>
			</div>
			<div class="trash"><span class="trash-icon"></span></div>
			<!-- this is sub items -->
			<?php //$this->render('order/order_handling_subitem.tpl', array ('order'=>$order));?>
			<!-- end of sub items -->
			</div>
			<?php endforeach ;
			endif ;?></div>
			</td>
		</tr>
	</tbody>
</table>
<?php $this->render('order/order_handling_subitem.tpl', array ('order'=>null));?>