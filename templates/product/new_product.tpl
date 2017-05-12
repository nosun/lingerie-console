<tr class="t-row" id="pid_<?php echo $nextProduct->id;?>">
<td class="product-id-cell td-label switcher-on-span" rowspan="2"><?php echo $nextProduct->id;?></td>
<td class="p-serial td-label switcher-on-span" rowspan="2"><input value="<?php echo $nextProduct->sn;?>" name="p-serial"></td>
<td class="td-label"><span class="nowrap">Images</span>
<div class="btn btn-success fileinput-button">
   <i class="icon-plus icon-white"></i>
   <input id="fileupload_<?php echo $nextProduct->id;?>" type="file" name="files[]" data-url="<?php echo url('image/upload/');?>" multiple=""/>
</div>
<div id="progress_<?php echo $nextProduct->id;?>" class="progress_container">
    <div class="bar"></div>
</div>
</td>
<td id="images_<?php echo $nextProduct->id;?>" class="image_gallary">
<?php foreach($nextProduct->imageSources as $k3=> $v3):?>
<a href="<?php echo url($v3);?>"><img src="<?php echo url(get_thumbnail($v3));?>" title="" alt="" class="product_thumb"/></a>
<?php endforeach;?>
</td>

<td class="p-type"><?php echo $nextProduct->type;?></td>

<td class="p-weight"><input type="text" value="<?php echo $nextProduct->wt;?>" name="p-weight"></td>


<td class="p-attributes">
<?php foreach($nextProduct->attributes as $aName=>$aValue):?>
	<dl class="p-<?php echo $aName?>">
		<dt><?php echo $aName;?> :</dt>
		<dd class="av-<?php echo $aName?>" autocompletedata="<?php echo $type_attributes[$aName]->data;?>"><input value="<?php echo $aValue;?>" name="av-<?php echo $aName?>" class="ui-autocomplete-input" autocomplete="off">
		</dd>
	</dl>
<?php endforeach;?>
</td>
<td class="p-stock"><div id="editstock_<?php echo $nextProduct->id;?>" class="button edit-stock">查看/编辑库存</div> 


<div id="stockdialog_<?php echo $nextProduct->id;?>" title="查看/编辑库存" class="stock-dialog">
<!-- dialog contents -->

<div class="button stock_edit">编辑库存</div>
<div class="button stock_apply">完成编辑</div>

<dl style="overflow:hidden;">
<?php if(count($nextProduct->imageSources) > 0):?>
<dt><a href="<?php echo url($nextProduct->imageSources[0]);?>"><img src="<?php echo url(get_thumbnail($nextProduct->imageSources[0]));?>" title="" alt="" class="product_thumb"/></a></dt>
<?php endif;?>
<dd><h3>编号: <?php echo $nextProduct->sn;?></h3></dd>
<?php foreach($nextProduct->attributes as $aName=>$aValue):?>
<dd class="av-<?php echo $aName?>"><?php echo translate($aName);?>: <?php echo $aValue;?></dd>
<?php endforeach;?>
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
<?php if(isset($nextProduct->stocks)):
foreach($nextProduct->stocks as $stock):
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
<?php 
endif;
endforeach;
endif;?>
</tbody>

</table>
</div>

</td>
<td class="p-suppliers" autocompletedata="<?php echo $name_suppliers?>">
	<input type="text" value="<?php echo $nextProduct->suppliers;?>" name="p-suppliers" class="ui-autocomplete-input" autocomplete="off"/>
</td>
<td>
<div id="buttons_pid_<?php echo $nextProduct->id;?>">
<div class="button edit">Edit</div>
<div class="button apply">Apply</div>
<!--  <div class="button edit_all_sites">Edit All Sites</div>-->
<div class="message_info"></div>
</div>
</td>
</tr>


<tr class="product-row switcher-support">

<td class="td-label">Product Sites Properties</td>
<td colspan="9" style="vertical-align:top" class="sites-container">
<dl>
<dd>
<table class="site_specific" cellspacing="0">
<thead>
<tr>
<td>Sites(Add New)</td>
<td>Product Name</td>

<!--<td>Image Relative Dir</td>-->
<td>List Price</td>
<td>Price</td>
<td>Category</td>
<td colspan="2">Other Categories</td>
<td>Functions</td>
</tr>
</thead>
<tbody>
<?php $countSite = 0;
foreach($nextProduct->site_details as $k2=>$v2):
$countSite += 1;
?>
<tr id="p_s_id_<?php echo $nextProduct->id?>_<?php echo $v2->sid;?>" class="<?php echo ($countSite % 2 == 0)? 'odd':'even';?>">
<td class="site_url"><a href="<?php echo $v2->url;?>"><?php echo $v2->url;?></a></td>
<td class="site_pname"><input type="text" value="<?php echo $v2->site_pname;?>" name="site_pname"></td>
<td class="site_listprice"><input type="text" value="<?php echo $v2->listprice;?>" name="site_listprice"></td>
<td class="site_price"><input type="text" value="<?php echo $v2->price;?>" name="site_price"></td>
<td class="site_category">
<select class="site_category">
<?php foreach($v2->site_category as $cid=>$categoryInfo):?>
<option value="<?php echo $cid;?>" <?php echo ($v2->cid == $cid)?'selected':'';?>><?php echo $categoryInfo->full_name;?></option>
<?php endforeach;?>
</select>
</td>
<td class="site_add_category">
<?php if (strcmp($v2->site_name, "lingeriemore.com") != 0) :?>
    <div class="btn btn-add cat-add"><i class="icon-plus icon-white"></i></div>
<?php endif;?>
</td>
<td class="site_other_categories">

<!-- here we need an apply button for apply the settings. -->
<div class="button update_categories">Update Categories</div>
<input type="hidden" name="added_categories" value=""/>
</td>



<td><div id="buttons_sid_<?php echo $v2->sid;?>" class="functions_td">
<div class="refresh_layer"></div>
<div class="button edit">Edit</div>
<div class="button apply">Apply</div>
<div class="buttonmd sync">Sync to Site</div>
</div>
</td>
</tr>
<?php endforeach;?>
</tbody>
</table>
</dd>
</dl>
</td>
</tr>
