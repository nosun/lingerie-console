<select class="site_other_category" id="here_is_sn">
<?php foreach($category_options as $k=>$v):?>
<option value="<?php echo $v->id?>"><?php echo $v->full_name;?></option>
<?php endforeach;?>
</select>