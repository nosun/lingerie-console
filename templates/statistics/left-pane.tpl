<div style="width:100%;">
<div class="left-pane">
<div class="left-pane-bar"></div>
<div class="navigation" style="">
<h3>统计报告</h3>
<ul>
<!-- 
<li><a href="<?php echo url('statistics/show');?>">汇总</a></li>
 -->
<li class="<?php if($statistics_title == '产品统计') echo 'selected'?>"><a href="<?php echo url('statistics/product');?>">产品统计</a></li>
<li class="<?php if($statistics_title == '订单统计') echo 'selected'?>"><a href="<?php echo url('statistics/order');?>">订单统计</a></li>
<li class="<?php if($statistics_title == '用户统计') echo 'selected'?>"><a href="<?php echo url('statistics/user');?>">用户统计</a></li>
</ul>
</div>
</div>
</div>