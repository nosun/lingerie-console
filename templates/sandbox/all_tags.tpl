<?php if(isset($allTags) && count($allTags)>0):?>
<div id="allTags" class="text">
<h2>Tag Cloud</h2>
<?php foreach($allTags as $tagName=>$frequency):
        $styleCode ='';
        if($frequency > 9){
          $styleCode = 'font-weight:bold; font-size:18px';
        }else if($frequency > 6){
          $styleCode = 'font-weight:normal; font-size:18px;';
        }else if($frequency > 2){
          $styleCode = 'font-weight:bold; font-size:12px;';
        }else{
          $styleCode = 'font-weight:normal; font-size:12px;';
        }
?>
	  <a class="tag" style="<?php echo $styleCode;?>" href="<?php echo url('tag/' .str_replace(' ', '-', $tagName). '.html');?>"><?php echo $tagName?></a>
    <?php endforeach;?>
</div>
<?php endif;?>