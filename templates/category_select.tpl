<select class="site_category">
<?php foreach($category_options as $k=>$v):?>
<option value="<?php echo $v->id?>" <?php echo ($current_full_name == $v->full_name)?'selected':''?>><?php echo $v->full_name;?></option>
<?php endforeach;?>
</select>