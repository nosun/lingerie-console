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
<?php $pageLines++;?>
<div id="maincontent">
<div id="main-body">

<table class="t-orders print-main"><caption></caption>
<tbody>
<?php 
$oitemCount = 0;
$itemsPerPage = 3;
foreach($skuItemList as $skuItem):
if($oitemCount > 0 && ($oitemCount % $itemsPerPage) == 0):?>
<tr class="order-row <?php if($pageLines+5 > 31){echo 'pagebreak'; $pageLines = 0;} ?>">
<?php $pageLines+=5;?>
<?php endif;?>
<td style="border-bottom:none">
<table width="200px" class="t-order-item">
<tbody>
<tr>
    <th colspan="3">
        <span class="fl">
            <span class="oitem-sn"><?php echo $skuItem->p_sn;?></span>
        </span>
        <div class="fr">
        	<span>上次校准时间：</span><span id="p_sn_<?php echo $skuItem->p_sn;?>_avid_<?php echo $skuItem->avid; ?>" style="font-weight:bold;"><?php if($skuItem->adjust_time > 0){echo date("Y-m-d H:i:s", $skuItem->adjust_time);}else{ echo "未校准";}?></span>
        	<div class="button adjust_stock_item hidden_if_print" style="float:none">更新</div>
        	<form>
                <input name="p_sn" type="hidden" value="<?php echo $skuItem->p_sn; ?>"></input>
                <input name="avid" type="hidden" value="<?php echo $skuItem->avid; ?>"></input>
            </form>
        </div>
    </th>
</tr>

<tr>
<td class="prodImage">
<?php if(isset($skuItem->imageSource)):?>
<img src="<?php 
if(startsWith($skuItem->imageSource, 'http')){
    echo $skuItem->imageSource;
}else{
    echo url(get_thumbnail($skuItem->imageSource));
}?>" title="" alt="" class="product_thumb"/>
<?php else:?>
<img src="<?php echo url('files/default.jpg');?>" title="" alt="" class="product_thumb"/>
<?php endif;?>
</td>
<td><?php if(isset($skuItem->data) && $skuItem->data != false):?>
<?php foreach($skuItem->data as $pName=>$pValue):?>
<?php if(!in_array(strtolower($pName), array('color','size'))){continue;} ?>
<dl class="prop-list">
<dt class="prop-name"><?php echo translate($pName);?>: </dt>
<dd class="prop-value"><?php echo $pValue;?></dd>
</dl>
<?php endforeach;?>

<?php endif;?>
</td>
<td style="width:200px;">
    <dl class="prop-list" style="margin-top:5px">
        <dt class="prop-name" style="font-size:25px;width:auto;">库存件数：</dt>
        <dd class="prop-value" style="font-size:25px;">
            <a href="#" class="adjust_stock_input" data-pk = "<?php echo $skuItem->p_sn; ?>" data-params = "{p_sn:'<?php echo $skuItem->p_sn;?>', avid:'<?php echo $skuItem->avid; ?>'}" data-type="text" data-name="stock_qty" data-url="<?php echo url('stock/ajaxadjustproductstock/'); ?>"><?php echo $skuItem->stock_qty;?></a>
        </dd>
    </dl>

<!-- 
    <dl class="prop-list">
        <dt class="prop-name">需求</dt>
        <dd class="prop-value"><?php echo $skuItem->real_qty + $skuItem->lack_qty;?>件</dd>
    </dl>
    <dl class="prop-list" style="margin-top:5px">
        <dt class="prop-name">库存</dt>
        <dd class="prop-value">
            <a href="#" class="adjust_stock_input" data-pk = "<?php echo $skuItem->p_sn; ?>" data-params = "{p_sn:'<?php echo $skuItem->p_sn;?>', avid:'<?php echo $skuItem->avid; ?>'}" data-type="text" data-name="stock_qty" data-url="<?php echo url('stock/ajaxadjustproductstock/'); ?>"><?php echo $skuItem->real_qty;?></a>件
                            
        </dd>
    </dl>
 -->

    <!-- <div class="button adjust_stock_item hidden_if_print">校准库存</div> -->
</td>
</tr>
</tbody>
</table>
</td>
<?php $oitemCount++;?>
<?php if(($oitemCount % $itemsPerPage) == 0):?>
</tr>
<?php elseif($oitemCount === count($skuItemList)):
$remains = $itemsPerPage - $oitemCount % $itemsPerPage;
while($remains-- > 0):?>
<td></td>
<?php endwhile;?>
</tr>
<?php endif;?>
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

<script type="text/javascript" src="<?php echo url("js/jqueryui-editable.min.js");?>"></script>
<script type="text/javascript">
$('div.button.adjust_stock_item').button().show();
$(function(){
	$.fn.editable.defaults.mode = 'popup';     
    $('.adjust_stock_input').editable({
    	validate: function(value) {
        	if($.trim(value) == '') {
            	return 'This field is required';
        	}
        	var integerRegex = new RegExp("^([1-9]+\d*|0)$");
        	if (!integerRegex.test(value)) {
            	return 'please input interger greater or equal to 0';
        	}
    	},
    	success: function(response, newValue) {
    		ret = $.parseJSON(response);
	        if (ret.success) {
				id = '#p_sn_' + ret.p_sn + '_avid_' + ret.avid;
				$(id).text(ret.adjust_time);
		    } else {
		        return ret.error;
	        }
    	}
    });
    
	$('div.button.adjust_stock_item').click(function(){
		var button = $(this);
		var form = $(this).parent().find('form');
		
		$.ajax({
		    url: url('stock/ajaxrefreshproductsstock/'),
		    type: 'post',
            data: form.serialize(),
            success: function(response) {
		        ret = $.parseJSON(response);
		        if (ret.success) {
					id = '#p_sn_' + ret.p_sn + '_avid_' + ret.avid;
					$(id).text(ret.adjust_time);
					button.text('已更新').button("disable");
			    } else {
			        alert(ret.error);
		        }
            },
            error: function() {
                alert('failed to adjust stock');
            }
		});
    });
});
</script>
</body>
</html>