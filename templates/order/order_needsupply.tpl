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
 <div class="button edit" style="float:right;">Edit</div>
<div class="button apply" style="float:right;">Apply</div><h1>缺货文档 </h1>
<?php $pageLines++;?>
<div id="maincontent">
<div id="main-body">

<table class="t-orders print-main"><caption></caption>

<tbody>

<?php 
$supplierCount = 0;
foreach($goodsShortage as $k=>$v):
$supplierCount++;?>
<tr class="t-row order-row <?php echo ($supplierCount % 2 == 0)? 'odd':'even';?> <?php if($pageLines+1 > 31){echo 'pagebreak'; $pageLines = 0;} ?>">
<?php $pageLines++;?>
<td class="supplier-label" colspan="2">供货商:</td>
<td class="supplier-name" colspan="2"><?php echo $k;?></td>
</tr>
<?php 
$oitemCount = 0;
foreach($v as $p_sn=>$orderSum):
if($oitemCount > 0 && ($oitemCount % 4) == 0):?>
<tr class="order-row <?php if($pageLines+5 > 31){echo 'pagebreak'; $pageLines = 0;} ?>" >
<?php $pageLines+=5;?>
<?php endif;?>

<?php $clearanceList = MD_Config::get('clearance', array());?>
<?php foreach($orderSum->requirements as $index=>$requirement):?>
<?php if($requirement->lack_qty > 0):?>
<td>
<table width="200px" class="t-order-item">
<tbody>
<tr>
    <th colspan="2">
        <span class="fl">
            <span class="oitem-sn"><?php echo $p_sn;?></span>
            <?php if (isset($orderSum->product->suppliers_sn)) :?>
            <span>供应商编号：<?php echo $orderSum->product->suppliers_sn; ?></span>
            <?php endif;?>
        </span>
        <span class="fr" style="margin-left:20px">
        	调货
        	<span class="oitem-qty">
        		<?php echo $requirement->predict_qty;?>
        	</span>件<?php if (in_array($p_sn, $clearanceList)){echo "(清仓产品，不多调)";}?>
        </span>
        <span class="fr">缺货<span class="oitem-qty"><?php echo $requirement->lack_qty;?></span>件</span>
</th></tr>

<tr>
<td class="prodImage">

<?php if(isset($orderSum->imageSource)):?>
<img src="<?php 
if(startsWith($orderSum->imageSource, 'http')){
	echo $orderSum->imageSource;
}else{
	echo url(get_thumbnail($orderSum->imageSource));
}?>" title="" alt="" class="product_thumb"/>
<?php else:?>
<img src="<?php echo url('files/default.jpg');?>" title="" alt="" class="product_thumb"/>
<?php endif;?>
</td>
<td><?php if(isset($requirement->data) && $requirement->data != false):?>

<?php foreach($requirement->data as $pName=>$pValue):?>
<dl class="prop-list">
<dt class="prop-name"><?php echo translate($pName);?>: </dt>
<dd class="prop-value"><?php echo $pValue;?></dd>
</dl>
<?php endforeach;?>

<?php endif;?>
</td>
</tr>
</tbody>
</table>
</td>
<?php $oitemCount++; endif;?>


<?php if(($oitemCount % 4) == 0):?>
</tr>
<?php elseif($oitemCount === $totalLackStylesCounts[$k]):
$remains = 4 - $oitemCount % 4;
while($remains-- > 0):?>
<td></td>
<?php endwhile;?>
</tr>
<?php endif;?>
<?php endforeach;?>
<?php endforeach;?>
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

<script type="text/javascript">
$('div.button.apply').button().hide();
$('div.button.edit').button().show();
$('div.button.apply').click(function(){
	$('table.t-order-item tr th').each(function(){
		var newValue = getNewValue('oitem-qty', 'span', this, 'input');
		edit2View('oitem-qty', newValue, 'span', this, 'input');
	});
	$(this).hide();
	$('div.button.edit').show();
});
$('div.button.edit').click(function(){
	$('table.t-order-item tr dd').each(function(){
		view2Edit('prop-value', 'span', this);
	});
	$(this).hide();
	$('div.button.apply').show();
});
</script>
</body>
</html>