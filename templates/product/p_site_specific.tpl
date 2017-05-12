<table class="site_specific" cellspacing="0">
<thead>
<tr>
<td>Sites</td>
<td>Product Name</td>
<td>List Price</td>
<td>Price</td>
<td>Category</td>
<td colspan="2">Other Categories</td>
<!--<td>Image Relative Dir</td>-->

<td>Functions</td>
</tr>
</thead>
<tbody>
<?php $countSite = 0;
foreach($v->site_details as $k2=>$v2):
$countSite += 1;
?>
<tr id="p_s_id_<?php echo $v->id?>_<?php echo $v2->sid;?>" class="<?php echo ($countSite % 2 == 0)? 'odd':'even';?>">

<td class="site_url"><a href="<?php echo $v2->url;?>"><?php echo $v2->site_name;?></a></td>
<td class="site_pname">
<?php if(isset($v2->site_purl) && $v2->site_purl != ''):?>
<a target="_blank" href="<?php echo $v2->url .'/' . $v2->site_purl;?>"><?php echo $v2->site_pname;?></a>
<?php else:?>
<?php echo $v2->site_pname;?>
<?php endif;?>
</td>
<td class="site_listprice"><?php echo $v2->listprice;?></td>
<td class="site_price"><?php echo $v2->price;?></td>
<td class="site_category"><?php if($v2->category){echo $v2->category->full_name;}?></td>
<td class="site_add_category">
<?php if(!$v2->readonly && strcmp($v2->site_name, "lingeriemore.com") != 0):?>
<div class="btn btn-add cat-add"><i class="icon-plus icon-white"></i></div>
<?php endif;?>
</td>

<td class="site_other_categories">
<?php if(!$v2->readonly):?>
<?php if($v2->alt_categories && count($v2->alt_categories) > 0):
	foreach($v2->alt_categories as $alt_key=> $alt_category):?>
	<div id="<?php echo $alt_key;?>" class="alt_category"><?php echo $alt_category->full_name;?></div>
<?php endforeach;
endif;?>
<!-- here we need an apply button for apply the settings. -->
<div class="button update_categories">Update Categories</div>
<input type="hidden" name="added_categories" value=""/>
<?php endif;?>
</td>

<!--  <td class="site_image_dir"><?php echo $v2->image_dir;?></td>-->
<td>
<div id="buttons_sid_<?php echo $v2->sid;?>" class="functions_td">
	<div class="refresh_layer"></div>
	
	<?php if($v2->readonly):?>
	<div class="button shelf <?php echo $v2->shelfstate == '1' ? 'off-shelf':'on-shelf';?>" style="display:block"><?php echo $v2->shelfstate  == '1' ? '下架':'上架';?></div>
	<?php else:?>
	<div class="button edit">Edit</div>
	<div class="button apply">Apply</div>
	<div class="button shelf <?php echo $v2->shelfstate == '1' ? 'off-shelf':'on-shelf';?>" style="display:<?php echo $v2->is_sync? 'block':'none';?>"><?php echo $v2->shelfstate  == '0' ? '上架':'下架';?></div>
	<div class="buttonmd sync <?php echo $v2->is_sync? 'resync':'';?>"><?php echo $v2->is_sync? 'Update Info':'Sync to Site';?></div>
	<?php endif?>
</div>
</td>
</tr>
<?php endforeach;?>
</tbody>
</table>