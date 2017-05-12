<?php 
define('DOMAIN_BASE_PATH', 'http://www.dearlover-corsets.com/');
define('SITE_NAME', 'Mingda Trade Management System');
define('SITE_TITLE', 'Mingda Trade Console');
define('SITE_SLOGAN', 'Mingda foreign trade console platform');

$config = array();
$config['db'] = array(
  'host' => 'localhost',
  'user' => 'admin',
  'passwd' => 'trade@mingDA123',
  'name' => 'mdtradeconsole',
);

$config['log'] = array(
  'error' =>false,
  '404' => false,
);

//Whether enable session.
$config['session_enable'] = true;
//Whether enable cache.
$config['cache_enable'] = true;

$config['product_properties'] = array(
'colors'=>array('Red', 'Yellow', 'Blue', 'Black', 'White', 'Green', 'Purple', 'Pink', 'Silver', 'Gold', 'As Shown'),
'sizes'=>array('XXL', 'XXL', 'XL', 'L', 'M', 'S', 'One Size')
);

$config['sitesdb'] = array(
'iwantlingerie.com'=>array('host'=>'localhost', 'user'=>'admin', 'passwd'=>'trade@mingDA123','name'=>'iwantlingerie'),
'bogolingerie.com'=>array('host'=>'localhost', 'user'=>'admin', 'passwd'=>'trade@mingDA123','name'=>'bogolingerie'),
);
