<head>
<title><?php echo isset($baseVariables->pagetitle)? $baseVariables->pagetitle:SITE_TITLE;?> | <?php echo SITE_TITLE;?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<meta name="keywords" content="<?php echo isset($baseVariables->keywords)? $baseVariables->keywords:'';?>" />
<meta name="description" content="<?php echo isset($baseVariables->description)? $baseVariables->description:'';?>"/>
<link rel="shortcut icon" href="<?php echo url('favicon.ico');?>" type="image/x-icon"/>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo url('css/main.css');?>"/>
</head>