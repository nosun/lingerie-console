<?php global $user;?>
<div class="header" id="header">
<div id="fixed-head">
<div></div>
<div class="header-container"> 
<h1> <a class="logo-anchor" href="<?php echo DOMAIN_BASE_PATH;?>"><img alt="Mingda Trade" src="<?php echo url('images/mingdatrade-logo.png');?>"></a> </h1>  
<ul class="info-bar">
<li title="admin email"><?php echo $user->name;?></li>  
<li> <a href="<?php echo url('user/logout');?>"> Log Out </a> </li>
</ul>
</div>
<div class="navbar-1 navbar-2">
<ul class="nav-list" id="gwt-uid-3">
<li class="nav-item"><a class="gux-tab-anchor" href="<?php echo url('');?>" id="homeTab">Home</a></li>
<li class="nav-item"><a class="gux-tab-anchor <?php if($pageLabel == 'product' ) echo "gux-tab-selected"?>" href="<?php echo url('product');?>">产品编辑</a></li>
<li class="nav-item"><a class="gux-tab-anchor <?php if($pageLabel == 'productfilter' ) echo "gux-tab-selected"?>" href="<?php echo url('product/productfilter');?>">产品过滤</a></li>
<?php if($user->name != 'view@mingdabeta.com'):?>
<li class="nav-item"><a class="gux-tab-anchor <?php if($pageLabel == 'order' ) echo "gux-tab-selected"?>" href="<?php echo url('order');?>">订单查看</a></li>
<li class="nav-item"><a class="gux-tab-anchor <?php if($pageLabel == 'orderfilter' ) echo "gux-tab-selected"?>" href="<?php echo url('order/orderfilter');?>">订单过滤</a></li>
<li class="nav-item"><a class="gux-tab-anchor <?php if($pageLabel == 'orderhandling' ) echo "gux-tab-selected"?>" href="<?php echo url('order/orderhandling');?>">订单处理</a></li>
<li class="nav-item"><a class="gux-tab-anchor <?php if($pageLabel == 'orderprocessed' ) echo "gux-tab-selected"?>" href="<?php echo url('order/processed');?>">出单查询</a></li>
<li class="nav-item"><a class="gux-tab-anchor <?php if($pageLabel == 'uploadordershippingno' ) echo "gux-tab-selected"?>" href="<?php echo url('order/uploadordershippingno');?>">运单号上传</a></li>
<li class="nav-item"><a class="gux-tab-anchor <?php if($pageLabel == 'stock' ) echo "gux-tab-selected"?>" href="<?php echo url('stock');?>">库存管理</a></li>
<li class="nav-item"><a class="gux-tab-anchor <?php if($pageLabel == 'statistics' ) echo "gux-tab-selected"?>" href="<?php echo url('statistics');?>">统计信息</a></li>
<li class="nav-item"><a class="gux-tab-anchor <?php if($pageLabel == 'ordersearch' ) echo "gux-tab-selected"?>" href="<?php echo url('order/ordersearch');?>">订单搜索</a></li>
<!--<li class="nav-item"><a class="gux-tab-anchor <?php if($pageLabel == 'wholesale_user' ) echo "gux-tab-selected"?>" href="<?php echo url('user/wholesale');?>">Wholesale客户</a></li-->
<?php endif;?>
</ul>
</div> 
</div>
</div>


<!-- header out -->
