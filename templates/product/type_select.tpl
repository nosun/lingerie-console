<select class="p-type">
<?php foreach($type_options as $k=>$v):?>
<option value="<?php echo $v->type;?>" <?php echo ($current_type == $v->type)?'selected':''?>><?php echo $v->type;?></option>
<?php endforeach;?>
</select>