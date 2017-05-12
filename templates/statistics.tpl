<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="<?php echo url('favicon.ico');?>" type="image/x-icon"/>
<link rel="stylesheet" type="text/css"  href="<?php echo url('css/jquery-ui-1.9.2.custom.css');?>" />
<link rel="stylesheet" type="text/css"  href="<?php echo url('css/jquery.selectBox.css');?>" />
<link rel="stylesheet" type="text/css" media="all" href="<?php echo url('css/main.css');?>"/>

<script type="text/javascript" src="<?php echo url("js/jquery-1.9.1.min.js");?>"></script>
<script type="text/javascript" src="<?php echo url("js/jquery-ui-1.9.2.custom.min.js");?>"></script>
<script type="text/javascript" src="<?php echo url("js/jquery.selectBox.min.js");?>"></script>
<script type="text/javascript">
var basePath = '<?php echo plain($basePath) ?>';
</script>

<script type="text/javascript" src="<?php echo url("js/main.js");?>"></script>



<!--Load the AJAX API-->

  </head>
<body class="syn-asfe-body">
<?php $this->render('header.tpl');?>
<!-- content in -->
<div id="content" class="statistics-page">
<!--main content -->
<div id="maincontent">

<?php $this->render('statistics/left-pane.tpl');?>
<div class="right-main">

<div class="main-head">
		<h1><?php echo $statistics_title; ?>(<?php echo isset($pageSiteMark)?$pageSiteMark:'';?>)</h1>
<div  style="background-color: #FFFFFF;position: fixed;right: 0;z-index: 10;">
<form id="statisticsfilter" name="statisticsfilter" method="post" action="">
<?php if($statistics_title == '产品统计'):?>
<div style="width:130px;float:left;padding:3px 10px 0 10px;"><input name="limit_filter" type="text" value="Top <?php echo isset($filters['limit']) ? $filters['limit']:10 ;?> Products" onblur="if(this.value==''){this.value='Top <?php echo isset($filters['limit']) ? $filters['limit']:10 ;?> Products';}" onfocus="if(this.value=='Top <?php echo isset($filters['limit']) ? $filters['limit']:10 ;?> Products'){this.value='';}"/></div>
<div style="width:100px;float:left;padding:3px 10px 0 10px;"><input name="sn_filter" type="text" value="<?php echo isset($filters['sn'])?$filters['sn']:"SN";?>" onblur="if(this.value==''){this.value='SN';}" onfocus="if(this.value=='SN'){this.value='';}"/></div>
<?php endif;?>
<div style="width:100px;float:left;padding:3px 10px 0 10px;"><input type="text" name="createdafter_filter" class="datepicker" value="<?php echo isset($filters['starttime']) ? date('m/d/Y', $filters['starttime']) : date('m/d/Y', (time() - 3600 * 7));?>" /></div>
<div style="width:100px;float:left;padding:3px 10px 0 10px;"><input type="text" name="createdbefore_filter" class="datepicker" value="<?php echo isset($filters['endtime']) ? date('m/d/Y', $filters['endtime']) : date('m/d/Y', time());?>" /></div>
<div style="float:left;padding:3px 10px 0 10px;">
			<select class="site-info" name="site_filter">
				<option value="-1" <?php if(!isset($filters['site'])) echo 'selected'?>>All Sites</option>
			<?php foreach($sites as $k=>$v):?>
				<option value="<?php echo $v->id?>" <?php if(isset($filters['site']) && $filters['site'] == $v->id) echo 'selected'?>><?php echo $v->name?></option>
			<?php endforeach;?>
			</select>
</div>
<div class="functions"  style="float:left;">
<div style="float:left;"><input type="submit" name="apply_filters" class="button filter_order" value="查看数据"/></div>
<div style="float:left;"><input type="submit" name="clear_filters" class="button filter_order" value="重置为默认"/></div>
</div>
</form>
</div>
</div>

<script type="text/javascript">
$('input.datepicker').datepicker();
$( 'button, input[type="submit"], div.button' ).button();
$('select').selectBox();
</script>

<div id="main-body">
    <?php $this->render('statistics/'.$templatefile);?>
</div>
</div>


</div>
</div>

<!-- footer in -->
<?php $this->render('footer.tpl')?>
<!-- footer out -->

</body>

</html>