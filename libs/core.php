<?php
final class MD_Core
{
  private static $_uri;
  private static $_url_key;

  private static $_router = array(
    'folder'=>'default',
    'controller' => 'index',
    'action'     => 'show',
    'arguments'  => array(),
  );
  private static $_instances = array();

  private static function getLoadFilename($name)
  {
    return trim(strtolower(strtr($name, '_', '/')), '/') . '.php';
  }

  public static function loadLibrary($library)
  {
    $filename = self::getLoadFilename($library);
    if (is_file(LIBPATH . '/' . $filename)) {
      require_once LIBPATH . '/' . $filename;
    }
  }

  public static function loadModel($model)
  {
    if (strcasecmp(substr($model, -6), '_Model') == 0) {
      $model = substr($model, 0, -6);
    }
    $filename = self::getLoadFilename($model);
    if (is_file(DOCROOT . '/libs/model/' . $filename)) {
      require_once DOCROOT . '/libs/model/' . $filename;
    }
  }
  public static function init()
  {
    global $db, $user, $basePath;
    
    MD_Core::loadLibrary('common');
    //include config
    //MD_Core::loadLibrary('pageinfo');
    timer();
    spl_autoload_register('MD_Core::loadModel');
    MD_Config::load();
    MD_Core::loadLibrary(MD_Config::get('cache.type', 'cache.file'));
    
    if ($path = trim(dirname($_SERVER['SCRIPT_NAME']), '\\/')) {
      $basePath = '/' . $path . '/';
    } else {
      $basePath = '/';
    }
    
    date_default_timezone_set('Asia/Shanghai');
    
    //Get current URL
    if (isset($_SERVER['PATH_INFO'])) {
      self::$_uri = ltrim($_SERVER['PATH_INFO'], '/');
      $splits = preg_split('/\./', self::$_uri);
      self::$_url_key = $splits[0];
    } else if(isset($_SERVER['REQUEST_URI'])) {
      self::$_uri = ltrim($_SERVER['REQUEST_URI'], '/');
      $splits = preg_split('/\./', self::$_uri);
      self::$_url_key = $splits[0];
    }else{
      self::$_uri = 'index.html';
      self::$_url_key = 'index';
    }
    define('CURRENTURI', self::$_uri);
    MD_Core::loadLibrary('database');
    $db = new MD_Database();
    $db->connect(MD_Config::get('db'));
    
    MD_Core::loadLibrary('log');
    
    define('IN_ADMIN', isset($paths[0]) && $paths[0] == 'mdadmin');
    
    session_name(MD_Config::get('session.name', 'sid'));
    session_set_cookie_params(null, $basePath);
    MD_Core::loadLibrary(MD_Config::get('session.type', 'session.db'));
    
    require_once LIBPATH . '/thirdparty/phprpc_client.php';
    
    
    if (is_file(LIBPATH . '/hook.php')) {
      require_once LIBPATH . '/hook.php';
    }
    

    if(MD_Config::get('session_enable', false) == true){
	    session::init();
	    session_start();
    }
  }

  public static function run()
  {
    self::staticRouter();
  }
  
  public static function staticRouter(){
  	
  	global $db;
  	

  	
    $pathSegs = preg_split('/\//', self::$_url_key);

    //log::save('DEBUG', 'pathSegs', $pathSegs);
    
    if(isset($pathSegs[0]) && $pathSegs[0] == trim(base_relative_path(), '/')){
    	array_shift($pathSegs);
    }
    
    $controllerPath = DOCROOT . '/libs/controller';
    if (isset($pathSegs[0]) && is_dir($controllerPath . '/' . strtolower($pathSegs[0]))) {
      self::$_router['folder'] = strtolower($pathSegs[0]);
      array_shift($pathSegs);
    }

    if(count($pathSegs)> 0){
    	self::$_router['controller'] = $pathSegs[0];
    	array_shift($pathSegs);
    }
    
    if(count($pathSegs) > 0){
    	if($pathSegs[0] != ''){
    		self::$_router['action'] = $pathSegs[0];
    	}
    	array_shift($pathSegs);
    }
    
    if(self::$_router['controller'] == ''){
    	self::$_router['controller'] = 'index';
    }
        while(count($pathSegs) > 0){
        if($pathSegs[0] !== ''){
        	self::$_router['arguments'][] = $pathSegs[0];
        }
        array_shift($pathSegs);
        }

    self::dispatch(self::$_router['controller'], self::$_router['action'], self::$_router['arguments']);
    
    if (MD_Config::get('compress', false) && !IN_ADMIN) {
      echo preg_replace('/\s+/', ' ', ob_get_clean());
    } else {
      echo ob_get_clean();
    }
  }
  
  private static function dispatch($controller, $action, $arguments, $log = true)
  {
  	
    $controllerClass = ucwords($controller).'_Controller';
    $controllerPath = DOCROOT . '/libs/controller';
    if (!class_exists($controllerClass, false)) {
      if (isset(self::$_router['folder']) && self::$_router['folder'] != 'default') {
      	$controllerPath .= '/' .  self::$_router['folder'] ;
      }
      $controllerFile = $controllerPath . '/' . $controller . '.php';
      if (!is_file($controllerFile)) {
        die('Controller file ' . $controllerFile . ' not found.');
      }
      require_once $controllerFile;
      if (!class_exists($controllerClass)) {
        die('Controller class ' . $controllerClass . ' not found.');
      }
    }
    if (!isset(self::$_instances[$controllerClass])) {
      self::$_instances[$controllerClass] = new $controllerClass();
    }
    $controllerInstance = self::$_instances[$controllerClass];
    $actionMethod = $action . 'Action';

    call_user_func_array(array($controllerInstance, $actionMethod), $arguments);
  }

  
  public static function errorDispatch(Exception $ex)
  {
    ob_clean();
    try {
      MD_Core::dispatch('error','error',array($ex), false);
    } catch (Exception $ignoreEx) {
      die($ex->getMessage());
    }
  }

  public static function getUri()
  {
    return self::$_uri;
  }

  public static function getRouter()
  {
    return self::$_router;
  }

  
  
  //insert page info to db.
  public static function insertPageInfoToDB(){
  	global $db, $PAGE_VARIABLES;
  	foreach($PAGE_VARIABLES as $k=>$v){
  		//insert into database.
  		//first only handles categories.
  		if($v['type'] == 'category'){
	  		$url_key = $k;
	  		$type = $v['type'];
	  		$title = $v['title'];
	  		$description = $v['description'];
	  		$keywords = implode(',', $v['keywords']);
	  		
	  		$cat_image = '';
	  		if(key_exists('categoryimage', $v)){
	  			$cat_image = $v['categoryimage'];
	  		}
	  		$url_suffix = '.html';
	  		if(key_exists('url_suffix', $v)){
	  			$url_suffix = $v['url_suffix'];
	  		}
	  		
	  		$catgory = array(
	  		'parentid' => '0',
	  		'title' => $title,
	  		'cat_image' => $cat_image
	  		);
	  		
	  		$db->insert('category', $catgory);
	  		$cid = $db->lastInsertId();
	  		//insert into pageInfo table.
	  		$pageInfo = array(
	  		'urlkey'=>$url_key,
	  		'type'=>$type,
	  		'refid'=>$cid,
	  		'pagetitle'=>$title,
	  		'keywords' =>$keywords,
	  		'description' =>$description,
	  		'urlsuffix' =>$url_suffix,
	  		);
	  		
	  		$db->insert('pageinfo', $pageInfo);
  		}else if($v['type'] == 'detail'){
  			$url_key = $k;
	  		$type = $v['type'];
	  		$title = $v['title'];
	  		$description = $v['description'];
	  		$keywords = implode(',', $v['keywords']);
	  		$categories = $v['categories'];
	  		
	  		$references = '';
	  		if(key_exists('references', $v)){
	  			$references = implode('#,#', $v['references']);
	  		}
	  		
	  		if(key_exists('tags', $v)){
	  			$tags = $v['tags'];
	  		}else{
	  			$tags = array();
	  		}
	  		
	  		$specials = '';
	  		if(key_exists('specials', $v)){
	  			$specials = implode(',', $v['specials']);
	  		}
	  		
	  		//here we don't use #, need refine.
	  		$category = endc($categories);

  			$url_suffix = '.html';
	  		if(key_exists('url_suffix', $v)){
	  			$url_suffix = $v['url_suffix'];
	  		}

	  		$db->select('cid');
	  		$db->where('title', $category);
	  		$result = $db->get('category');
	  		$category = $result->row();
	  		
	  		$content = file_get_contents(TEMPLATEPATH .'/contents/'.$url_key . '.tpl');
	  		
	  		$article = array(
	  		'title' => $title,
	  		'summary' => $description,
	  		'contents' => $content,
	  		'author' =>'', 
	  		'source' =>'',
	  		'references' => $references,
	  		'specials' => $specials,
	  		'created' => time(),
	  		'updated' => time(),
	  		);
	  		
	  		$db->insert('article', $article);
	  		$aid = $db->lastInsertId();

	  		//insert into article_category table.
	  		$acrelation = array(
	  			'aid' => $aid,
	  			'cid' => $category->cid,
	  		);
	  		$db->insert('article_category', $acrelation);
	  		
	  		//after insert the articles.
	  		//insert tags

	  		foreach($tags as $k=>$v){
	  				$db->select('tid');
	  				$db->where('name', $v);
	  				$result = $db->get('tag');
	  				$tid_std = $result->row();
	  				if($tid_std){
	  					$tid = $tid_std->tid;
	  				}else{
	  					$db->insert('tag', array('name'=> $v));
	  					$tid = $db->lastInsertId();
	  				}
	  			$db->insert('article_tag', array('aid'=>$aid, 'tid'=>$tid));
	  		}
	  		//after all the tags was inserted.
	  		$tags = $db->get('tag');
	  		
	  		//insert into pageInfo table.
	  		$pageInfo = array(
	  		'urlkey'=>$url_key,
	  		'type'=>$type,
	  		'refid'=>$aid,
	  		'pagetitle'=>$title,
	  		'keywords' =>$keywords,
	  		'description' =>$description,
	  		'urlsuffix' =>$url_suffix,
	  		);
	  		$db->insert('pageinfo', $pageInfo);
  		}
  	}
  }
  
  
  
}

abstract class MD_Controller
{
  /**
   * @var MD_View
   */
  public $view = null;

  public function __construct()
  {
    $this->view = new MD_View();
    $this->init();
  }

  public function init()
  {
    
  }

  public function isPost()
  {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }
  
  public function assignPageVariables(){
  	//In the future need put this function into a new class.
  }
}

abstract class MD_Model
{
  protected static function getInstance($className)
  {
    static $_instances = array();
    if (!isset($_instances[$className])) {
      $_instances[$className] = new $className;
    }
    return $_instances[$className];
  }

  public function callFunction($func)
  {
    $args = func_get_args();
    array_shift($args);
    $className = get_class($this);
    if (strcasecmp(substr($className, -6), '_Model') == 0) {
      $hookFunc = strtolower(substr($className, 0, -6)) . '_' . $func;
    }
    if (method_exists($this, $func)) {
      return call_user_func_array(array($this, $func), $args);
    }
  }
}

final class MD_View
{
  private $_data = array();

  public function render($templateFile, $variables = null)
  {
    global $basePath;
    
    static $_theme;
    if (!isset($_theme)) {
      if (isset($_SESSION['preview'])) {
        $_theme = $_SESSION['preview'];
      } else {
        $_theme = MD_Config::get('theme', 'default');
      }
    }
    $this->assign('themedir', $_theme ? ($basePath . 'themes/' . $_theme) : '');
    $this->assign('scripts', $this->renderJs());
    $this->assign('styles', $this->renderCss());

    
    if (isset($variables) && is_array($variables)) {
      $this->assign($variables);
    }
    extract($this->_data, EXTR_OVERWRITE);
    
    if(is_file(DOCROOT . '/' .$this->_data['themedir'] . '/templates/' .$templateFile)){
    	include DOCROOT . '/' .$this->_data['themedir'] . '/templates/' .$templateFile;
    }else if (is_file(DOCROOT . '/templates/' . $templateFile)) {
      include DOCROOT . '/templates/' . $templateFile;
    }else{
      throw new MD_General_Exception('View file <em>' . $templateFile . '</em> not found.');
    }
  }

  public function assign($key, $value = null)
  {
    if (is_array($key)) {
      foreach ($key as $k => $value) {
        $this->_data[$k] = $value;
      }
    } else {
      $this->_data[$key] = $value;
    }
    return $this;
  }

  public function setTitle($title, $keywords = '', $description = '', $var1 = '', $var2 = '', $var3 = '', $var4 = '', $var5 = '', $var6 = '')
  {
    $this->assign('docTitle', $title);
    $this->assign('docKeywords', $keywords);
    $this->assign('docDescription', $description);
    $this->assign('docvar1', $var1);
    $this->assign('docvar2', $var2);
    $this->assign('docvar3', $var3);
    $this->assign('docvar4', $var4);
    $this->assign('docvar5', $var5);
    $this->assign('docvar6', $var6);
    return $this;
  }

  public function addJs($path)
  {
    if (!isset($this->_data['scripts'])) {
      $this->_data['scripts'] = array();
    }
    $this->_data['scripts'][] = $path;
    return $this;
  }

  public function renderJs()
  {
    static $scripts;
    if (!isset($scripts)) {
      $scripts = '';
      if (isset($this->_data['scripts'])) {
        foreach ($this->_data['scripts'] as $path) {
          $scripts .= '<script type="text/javascript" src="' . $path . '"></script>' . PHP_EOL;
        }
      }
    }
    return $scripts;
  }

  public function addCss($path)
  {
    if (!isset($this->_data['styles'])) {
      $this->_data['styles'] = array();
    }
    $this->_data['styles'][] = $path;
    return $this;
  }

  public function renderCss()
  {
    static $styles;
    if (!isset($styles)) {
      $styles = '';
      if (isset($this->_data['styles'])) {
        foreach ($this->_data['styles'] as $path) {
          $styles .= '<link rel="stylesheet" href="' . $path . '" type="text/css">' . PHP_EOL;
        }
      }
    }
    return $styles;
  }
}


final class MD_Config
{
  private static $_config;
  private static $_pageInfos;

  public static function load()
  {
    global $domainUrl;
    if (!isset(self::$_config)) {
      if (is_file(DOCROOT . '/config.php') && (require_once DOCROOT . '/config.php') && isset($config)) {
        self::$_config = $config;
        unset($config);
      } else {
        self::$_config = array();
      }
    }
    return self::$_config;
  }
  public static function get($key = null, $default = null)
  {
    if(isset($key)){
      $segments = explode(".", $key);
      
      $config = self::$_config;
      foreach($segments as $k=>$v){
        if(!key_exists($v, $config)){
          return $default;
        }
        $config = $config[$v];
      }
      return $config;
    }
    /*else if (isset($key)) {
      return key_exists($key, self::$_config) ? self::$_config[$key] : $default;
    } 
    */
    else {
      return self::$_config;
    }
  }
}