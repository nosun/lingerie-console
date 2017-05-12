<?php global $user;?>
<!DOCTYPE html>
<html lang="zh-CN">
  <head>
  <meta charset="utf-8">

  <link rel="stylesheet" type="text/css" media="all" href="<?php echo url('css/home.css');?>"/>
  <title><?php echo SITE_TITLE;?></title>
  </head>
  <body>
  <div class="wrapper">
  <div class="google-header-bar">
  
<div class="header content clearfix">
  <img alt="Mingda Trade" src="<?php echo url('/images/mingdatrade-logo.png');?>" class="logo">
  </div>
  </div>
  <div class="main content clearfix">

<div class="sign-in">

<div class="signin-box">
<?php if (!$user->uid):?>
  <h2>Log In</h2>
  <form novalidate id="gaia_loginform" action="<?php echo url('user/login');?>" method="post">
  <div class="email-div">
	  <label for="Email"><strong class="email-label">E-mail</strong></label>
	  <input  type="email" name="email" id="email" value="">
  </div>
<div class="passwd-div">
  <label for="Passwd"><strong class="passwd-label">Password</strong></label>
  <input  type="password" name="passwd" id="passwd">
</div>
  <input type="submit" class="g-button g-button-submit" name="signIn" id="signIn"
      value="submit">
  </form>

<?php else:?>
<div>You are already signed in, go to <a href="<?php echo url('product')?>">Product</a> page.</div>

<?php endif;?>
</div>
  </div>


  <div class="product-info adsense">
<div class="product-headers">
  <h1 class="redtext"><?php echo SITE_TITLE;?></h1>
  <h2><?php echo SITE_SLOGAN; ?></h2>
</div>
  <p>
  Please give feedbacks to improve this products. Thank you dude!
  </p>
  <ul class="features clearfix">
  <li>
  <img src="//ssl.gstatic.com/images/icons/product/adsense_for_search-64.png" alt="">
  <p class="title">
  Easy Management
  </p>
  <p>
  Manage all product easily, upload product images with just one click.
  </p>
  </li>
  <li>
  <img src="//ssl.gstatic.com/images/icons/product/adsense-64.png" alt="">
  <p class="title">
  Multi Website
  </p>
  <p>
  You can manage multi website only if you subscribed that website.
  </p>
  </li>
  <li>
  <img src="//ssl.gstatic.com/images/icons/product/adsense_for_mobile-64.png" alt="">
  <p class="title">
  View Smoothly
  </p>
  <p>
  You can view this management console from any where, any device!
  </p>
  </li>
  </ul>
  </div>
  </div>

  </div>
<?php $this->render('footer.tpl')?>
  </body>
</html>
