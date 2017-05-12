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
		<h1>所有订单</h1>
		<div class="button-group">
			<div class="button remote_sync_latest_order"><a href="<?php echo url('user/syncWholesaleUser');?>">Sync Wholesale Users</a></div>
			<div class="message_info_total"></div>
		</div>
</div>

<?php $this->render('user/wholesale_user_list.tpl');?>

<div class="pagination"><?php echo $pagination;?></div>
</div>
</div>
<!--end main content -->

<div id="showaddress-dialog" title="Shipping Information"></div>


</div><!-- end content -->
<!-- content out -->
<!-- end wrapper -->
<!-- footer in -->
<?php $this->render('footer.tpl')?>
<script type="text/javascript" src="<?php echo url("js/jqueryui-editable.min.js");?>"></script>
<!-- footer out -->
</body>
</html>