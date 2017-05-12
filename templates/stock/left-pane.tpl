<div style="width:100%;">
<div class="left-pane">
<div class="left-pane-bar"></div>
<div class="navigation" style="">
<h3>库存管理</h3>
<ul>
<!-- 
<li><a href="<?php echo url('statistics/show');?>">汇总</a></li>
 -->
<li class="<?php if($stock_title == '增加库存') echo 'selected';?>"><a href="<?php echo url('stock');?>">增加库存</a></li>
<li class="<?php if($stock_title == '查看库存') echo 'selected';?>"><a href="<?php echo url('stock/view');?>">查看库存</a></li>
<li class="<?php if($stock_title == '替换库存') echo 'selected';?>"><a href="<?php echo url('stock/stockreplace');?>">替换库存</a></li>
</ul>
</div>
</div>
</div>