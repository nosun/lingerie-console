<?php if($newCategories && count($newCategories) > 0):
foreach($newCategories as $k=>$v):?>
<div id="<?php echo $k;?>" class="alt_category"><?php echo $v->full_name;?></div>
<?php endforeach;endif;?>

<div class="button update_categories">Update Categories</div>
<input type="hidden" name="added_categories" value=""/>