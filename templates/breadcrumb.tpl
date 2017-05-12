<?php if(isset($breadcrumbs)):?>
<div class="breadcrumbs">
<ul>
<?php 
$bccount = count($breadcrumbs);
foreach($breadcrumbs as $bcname=>$bcurl):
$bccount --;
if($bccount > 0):
?>
<li><a href="<?php echo $bcurl;?>"><?php echo $bcname;?></a> <?php echo '&nbsp;>&nbsp;'?></li>
<?php else:?>
<li class="emphasis"><?php echo $bcname;?></li>
<?php endif;?>
<?php endforeach;?>
</ul>
</div>
<?php endif;?>