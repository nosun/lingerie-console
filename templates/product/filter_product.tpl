<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php $this->render('before_body.tpl');?>
<body class="syn-asfe-body">
<?php $this->render('header.tpl');?>
<!-- content in -->
<div id="content">
<!--main content -->
<div id="maincontent">
<?php if(isset($cat_header_show) && $cat_header_show == true):?>
<?php $this->render('category_header.tpl');?>
<?php endif;?>
<div id="main-body">
<div class="main-head">
		<h1>Products Filters</h1>
		<div class="button-group">
			<fieldset class="complex-button">
			<div style="float:left;">
			<select class="site-info" name="site_sync">
			<?php foreach($sites as $k=>$v):?>
				<option value="<?php echo $v->id?>"><?php echo $v->name?></option>
			<?php endforeach;?>
			</select>
			</div>
			<div class="button remote_sync_latest">Sync Latest Info from Remote</div>
			</fieldset>
			
			<?php $this->render('onoffswitch.tpl');?>
			<div class="message_info_total"></div>
			
			<fieldset class="complex-button">
			<div style="float:left;">
			<select class="type-info" name="type-select">
			<?php foreach($type_options as $product_type):?>
				<option value="<?php echo $product_type->type?>"><?php echo $product_type->type?></option>
			<?php endforeach;?>
			</select>
			</div>
			<div class="button add_new_product">Add New</div>
			</fieldset>
			
		</div>
</div>

<form id="productfilter" name="productfilter" method="post" action="<?php echo url('product/productfilter');?>">
<table class="filter">
<tr id="product-filter" style="background-color:#ffeeff">
<td class="filter-cell"><b>Filter</b></td>
<td class="f-serial"><input name="sn_filter" type="text" value="<?php echo isset($productfilters['sn'])?$productfilters['sn']:"SN";?>" onblur="if(this.value==''){this.value='SN';}" onfocus="if(this.value=='SN'){this.value='';}"/></td>
<td class="f-name"><input name="name_filter" type="text" style="background-color:#dddddd;color:#999;" value="In Development Progress, Enable later." readonly/></td>
<td class="f-type"><input name="type_filter" type="text" value="<?php echo isset($productfilters['type'])?$productfilters['type']:"TYPE";?>" onblur="if(this.value==''){this.value='TYPE';}" onfocus="if(this.value=='TYPE'){this.value='';}"/></td>
<td class="f-weight"><input name="weight_filter" type="text" value="<?php echo isset($productfilters['wt'])?$productfilters['wt']:"WEIGHT";?>" onblur="if(this.value==''){this.value='WEIGHT';}" onfocus="if(this.value=='WEIGHT'){this.value='';}"/></td>
<!--
<td class="f-colors"><input name="color_filter" type="text" value="<?php echo isset($productfilters['colors'])?$productfilters['colors']:"COLOR";?>" onblur="if(this.value==''){this.value='COLOR';}" onfocus="if(this.value=='COLOR'){this.value='';}"/></td>
<td class="f-sizes"><input name="size_filter" type="text" value="<?php echo isset($productfilters['sizes'])?$productfilters['sizes']:"SIZE";?>"onblur="if(this.value==''){this.value='SIZE';}" onfocus="if(this.value=='SIZE'){this.value='';}" /></td>
-->
<td ><input type="submit" class="button filter_product" value="Filter Product"/></td>
</tr>
</table>
</form>


<table class="t-products"><caption></caption>
<thead>
<tr class="t-header">
<td>ID</td>
<td>SN</td>
<td colspan="2">Images</td>
<td>Type</td>
<td>Weight</td>
<td colspan>Attributes</td>
<!--
<td>Sizes</td>
  -->
<td>Stock</td>
<td>Suppliers</td>

<td>Functions</td>
</tr>
</thead>
<tbody>

<?php $productItem = 0;
foreach($products as $k=>$v):
$productItem++;
?>
<tr class="t-row product-row <?php echo ($productItem % 2 == 0)? 'odd':'even';?>" id="pid_<?php echo $v->id;?>">
<td class="product-id-cell td-label switcher-on-span" rowspan="2"><?php echo $v->id;?></td>
<td class="p-serial td-label switcher-on-span" rowspan="2"><?php echo $v->sn;?></td>


<td class="td-label"><span class="nowrap">Images</span>
<div class="btn btn-success fileinput-button">
   <i class="icon-plus icon-white"></i>
   <input id="fileupload_<?php echo $v->id;?>" type="file" name="files[]" data-url="<?php echo url('image/upload/');?>" multiple=""/>
</div>
<div id="progress_<?php echo $v->id;?>" class="progress_container">
    <div class="bar"></div>
</div>
<script type="text/javascript">
$(function () {
    $("#fileupload_<?php echo $v->id;?>").fileupload({
        dataType: 'json',
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                $('td#images_<?php echo $v->id;?>').append(
'<a href="'+file.url+'"><img src="' + file.thumbnail_url +'" title="' + file.name + '" alt="' + file.name + '" class="product_thumb"/> </a>'
                        );
            });
        },
        formData: {sn: "<?php echo $v->sn;?>"},
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress_<?php echo $v->id;?> .bar').css(
                'width',
                progress + '%'
            );
        }
    });    
});
</script>
</td>
<td id="images_<?php echo $v->id;?>" class="image_gallary">
<?php foreach($v->imageSources as $k3=> $v3):?>
<a href="<?php echo url($v3);?>"><img src="<?php echo url(get_thumbnail($v3));?>" title="" alt="" class="product_thumb"/></a>
<?php endforeach;?>
</td>


<td class="p-type"><?php echo $v->type;?></td>
<td class="p-weight"><?php echo $v->wt;?></td>
<!--
<td class="p-colors"><?php echo $v->colors;?></td>
<td class="p-sizes"><?php echo $v->sizes;?></td>
-->
<td class="p-attributes">
<?php if($v->attributes):?>
<?php foreach($v->attributes as $aName=>$aValue):?>
	<dl class="p-<?php echo $aName?>">
		<dt><?php echo $aName;?> :</dt>
		<dd class="av-<?php echo $aName?>"><?php echo $aValue;?></dd>
	</dl>
<?php endforeach;?>
<?php endif;?>
</td>

<td class="p-stock"><div id="editstock_<?php echo $v->id;?>" class="button edit-stock">查看/编辑库存</div> 

<div id="stockdialog_<?php echo $v->id;?>" title="查看/编辑库存" class="stock-dialog">
<!-- dialog contents -->

<div class="button stock_edit">编辑库存</div>
<div class="button stock_apply">完成编辑</div>

<dl style="overflow:hidden;">
<?php if(count($v->imageSources) > 0):?>
<dt><a href="<?php echo url($v->imageSources[0]);?>"><img src="<?php echo url(get_thumbnail($v->imageSources[0]));?>" title="" alt="" class="product_thumb"/></a></dt>
<?php endif;?>
<dd><h3>编号: <?php echo $v->sn;?></h3></dd>
<?php if($v->attributes):?>
<?php foreach($v->attributes as $aName=>$aValue):?>
<dd class="av-<?php echo $aName?>"><?php echo translate($aName);?>: <?php echo $aValue;?></dd>
<?php endforeach;?>
<?php endif;?>
</dl>

<table class="t-stock">
<thead>
<tr>
<th>库存ID</th>
<th>属性值</th>
<th>库存数量</th>
<th>购买价格</th>
<th>供应商编号</th>
</tr>
</thead>

<tbody>
<?php foreach($v->stocks as $stock):
if(count($stock->parameters) > 0):?>
<tr id="stock-<?php echo $stock->stock_id;?>">
<td><?php echo $stock->stock_id;?></td>
<td class="stock-attributes"><?php foreach($stock->parameters as $aName=>$aValue):?>
<p><?php echo translate($aName);?>: <?php echo $aValue;?></p>
<?php endforeach;?>
</td>
<td class="stock-qty"><?php echo $stock->stock_qty;?></td>
<td class="stock-bought_price"><?php echo $stock->bought_price;?></td>
<td class="suppliers-sn"><?php echo $v->suppliers_sn; ?></td>
</tr>
<?php endif;
endforeach;?>
</tbody>

</table>
</div>

</td>
<td class="p-suppliers"><?php echo $v->suppliers;?></td>

<td><div id="buttons_pid_<?php echo $v->id;?>">
<div class="button edit">Edit</div>
<div class="button apply">Apply</div>
<!--  <div class="button edit_all_sites">Edit All Sites</div>-->
<div class="message_info" style="display:none;"></div>
</div>
</td>
</tr>
<tr class="product-row <?php echo ($productItem % 2 == 0)? 'odd':'even';?> switcher-support">

<td class="td-label">Product Sites Properties</td>
<td colspan="9" class="sites-container">
<dl>
<dd>
<?php $this->render('product/p_site_specific.tpl', array('v'=>$v));?>
</dd>
</dl>
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
<?php $this->render('footer.tpl')?>
<!-- footer out -->
<script type="text/javascript">
$( 'button, input[type="submit"], div.button' ).button();
$('select').selectBox();
</script>
</body>
</html>