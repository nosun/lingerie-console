<! DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" >
<html xmlns="http://www.w3.org/1999/xhtml">
<?php $this ->render( 'before_body.tpl'); ?>
<body class="syn-asfe-body">

<?php $this ->render('header.tpl');?>
<!-- content in -->

<div id="content"><!--main content -->
<div id="maincontent">

<div id="main-body">
<div class="main-head">
<h1>订单处理</h1>
<div class="button-group">
<div class="button remote_sync_latest_order"><a
	href="<?php echo url( 'order/syncorders'); ?>">同步订单</a></div>
<div class="message_info_total"></div>
</div>

<div class="distribution-group">
<div class="button adjust_stock">库存校准</div>
<div class="button refresh_and_compute">更新并计算订单</div>
</div>
</div>

<div id="ajax-refresh-container">
<?php $this->render('order/order_handling_pools.tpl');?>
</div>

</div>
</div>
<!--end main content --></div>
<!-- end content -->
<!-- content out -->
<!-- end wrapper -->
<!-- footer in -->
<script type="text/javascript" src="<?php echo url("js/jquery.multisortable.js");?>"></script>
<script type="text/javascript" src="<?php echo url("js/jqueryui-editable.min.js");?>"></script>
<script type="text/javascript" src="<?php echo url("js/orderhandling.js");?>"></script>

<?php $this ->render( 'footer.tpl'); ?>
<!-- footer out -->
</body>
</html>
