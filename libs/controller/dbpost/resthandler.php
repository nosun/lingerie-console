<?php
class Resthandler_Controller extends MD_Controller  {

	private $_articleInstance;
	private $_pageInfoInstance;
	private $_categoryInstance;
	private $_tagInstance;
	
  public function init()
  {
    $this->_articleInstance = Article_Model::getInstance();
    $this->_pageInfoInstance = PageInfo_Model::getInstance();
    $this->_categoryInstance = Category_Model::getInstance();
    $this->_tagInstance = Tag_Model::getInstance();
  }
  
  public function getSysDateAction(){
  	date_default_timezone_set("Etc/GMT-8");
  	echo date('Ymd');
  }
  
  public function postAction(){
  	 if(!$this->verifyAuthentication()){
  		echo 'Authentication error!';
  		return;
  	}
  	//if passed the authentication.
  	$post = $_POST;

  	if(!key_exists('urlkey', $post)){
  		echo 'Bad Posting Data';
  		return;
  	}
  	try{
  	//firstly post the article.
  	$article = $this->_articleInstance->getArticleInfoByUrlKey($post['urlkey']);
  	
  	if(!isset($post['title'])){$post['title'] = '';}
  	if(!isset($post['description'])){$post['description'] = '';}
  	if(!isset($post['contents'])){$post['contents'] = '';}
  	if(!isset($post['author'])){$post['author'] = '';}
  	if(!isset($post['source'])){$post['source'] = '';}
  	if(!isset($post['specials'])){$post['specials'] = '';}
  	if(!isset($post['references'])){$post['references'] = '';}
  	if($article){
  		$this->_articleInstance->updateArticle($article->aid, $post['title'], $post['description'], $post['contents'],
  			$post['author'], $post['source'], $post['specials'], $post['references']);
		$article_id = $article->aid;
  	}else{
  		$article_id = $this->_articleInstance->insertArticle($post['title'], $post['description'], $post['contents'],
  			$post['author'], $post['source'], $post['specials'], $post['references']);
  	}
  	//then post to the pageinfo table for the article.
    $pageInfo = $this->_pageInfoInstance->getPageInfo($post['urlkey']);
  	if($pageInfo){
  		$this->_pageInfoInstance->updatePageInfo($post['urlkey'], 'detail',
  			$post['title'], $post['keywords'], $post['description']);
  	}
  	else{
  		$this->_pageInfoInstance->insertPageInfo($post['urlkey'], 'detail',
  			$article_id, $post['title'], $post['keywords'], $post['description']);
  	  	$urlsuffix = key_exists('urlsuffix', $post) ? $post['urlsuffix']:'.html';
  		//add_to_site_map(DOMAIN_BASE_PATH.$post['urlkey'].$urlsuffix);
  		build_site_map_from_db();
  	}
  	
  	log::save('DEBUG', 'completed page info table', $_POST);
  	//then post the category of the article.
  	$category = $this->_categoryInstance->getCategoryByTitle($post['cname']);
  	if($category){
  		log::save('DEBUG', 'update category', $post['cname']);
  		$this->_categoryInstance->updateCategory($category->cid, $post['cname']);
  		$category_id = $category->cid;
  	}else{
  		log::save('DEBUG', 'insert category', $post['cname']);
  		$category_id = $this->_categoryInstance->insertCategory($post['cname']);
  	}
  	//then post to the article_category table.
  	$this->_categoryInstance->addArticle($category_id, $article_id);
  	
  	//then post to the pageinfo table for the category.
  	$category_url_key = getUrlKeyByName($post['cname']);
    $pageInfo = $this->_pageInfoInstance->getPageInfo($category_url_key);
  	if($pageInfo){
  		$this->_pageInfoInstance->updatePageInfo($category_url_key, 'category', $post['cname'], '', '');
  	}
  	else{
  		$this->_pageInfoInstance->insertPageInfo($category_url_key, 'category', $category_id, $post['cname'], '', '');
  		//add url to site map.
  		//add_to_site_map(DOMAIN_BASE_PATH.$category_url_key.'.html');
  		build_site_map_from_db();
  	}
  	
  	//then post tag infomation.
  	if(isset($post['tags'])){
  		$tags = explode('#,#', $post['tags']);
	  	foreach($tags as $k=>$v){
	  		if($v != ''){
	  			//log::save('Debug','current tag is', $v);
	  			$tag_id = $this->_tagInstance->insertTag($v);
	  			//log::save('Debug','current tag id is', $tag_id);
	  			if($tag_id){
	  				$this->_tagInstance->addTagToArticle($tag_id, $article_id);
	  				//log::save('Debug','tag-article pair', $tag_id.'-'. $article_id);
	  			}
	  		}
	  	}
  	}
  	cache::clean();
  	echo 'Post Success!';
  	}catch(Exception $e){
  		echo $e->getMessage();
  	}
  }
  
  public function putAction(){
    parse_str(file_get_contents("php://input"),$put);
    if(key_exists('realm', $put)){
    	$verifyResult = $this->verifyAuthentication($put['realm']);
    }else{
    	$verifyResult = $this->verifyAuthentication();
    }
  	if(!$verifyResult){
  		echo 'Authentication error!';
  		return;
  	}
  	//if passed the authentication.
  	if(!key_exists('urlkey', $put)){
  		echo 'Bad Posting Data';
  		return;
  	}
  	
    if(!isset($put['title'])){$put['title'] = '';}
  	if(!isset($put['description'])){$put['description'] = '';}
  	if(!isset($put['contents'])){$put['contents'] = '';}
  	if(!isset($put['author'])){$put['author'] = '';}
  	if(!isset($put['source'])){$put['source'] = '';}
  	if(!isset($put['specials'])){$put['specials'] = '';}
  	if(!isset($put['references'])){$put['references'] = '';}
  	
  	try{
  	//firstly post the article.
  	$article = $this->_articleInstance->getArticleInfoByUrlKey($put['urlkey']);

  	if($article){
  		//need to get old category id.
  		$old_article_category = $this->_articleInstance->getArticleCategory($article->aid);
  		
  		$this->_articleInstance->updateArticle($article->aid, $put['title'], $put['description'], $put['contents'],
  			$put['author'], $put['source'], $put['specials'], $put['references']);
		$article_id = $article->aid;
  	}else{
  		echo 'Article Not found!';
  		return;
  	}
  	//then post to the pageinfo table for the article.
    $pageInfo = $this->_pageInfoInstance->getPageInfo($put['urlkey']);
  	if($pageInfo){
  		$this->_pageInfoInstance->updatePageInfo($put['urlkey'], 'detail',
  			$put['title'], $put['keywords'], $put['description']);
  	}
  	else{
  		$this->_pageInfoInstance->insertPageInfo($put['urlkey'], 'detail',
  			$article_id, $put['title'], $put['keywords'], $put['description']);
  		$urlsuffix = key_exists('urlsuffix', $put) ? $put['urlsuffix']:'.html';
  		//add_to_site_map(DOMAIN_BASE_PATH.$post['urlkey'].$urlsuffix);
  		build_site_map_from_db();
  	}
  	//then post the category of the article.
  	if($old_article_category->title != $put['cname']){
  		//remove article from old article category
  		$this->_categoryInstance->deleteArticleCategory($article->aid, $old_article_category->cid);
  		//article-category map is changed.
  	  	$category = $this->_categoryInstance->getCategoryByTitle($put['cname']);
	  	if($category){
	  		$this->_categoryInstance->updateCategory($category->cid, $put['cname']);
	  		$category_id = $category->cid;
	  	}else{
	  		$category_id = $this->_categoryInstance->insertCategory($put['cname']);
	  	}
	  	//then post to the article_category table.
	  	$this->_categoryInstance->addArticle($category_id, $article_id);
  	  	//then post to the pageinfo table for the category.
	  	$category_url_key = getUrlKeyByName($put['cname']);
	      $pageInfo = $this->_pageInfoInstance->getPageInfo($category_url_key);
	  	if($pageInfo){
	  		$this->_pageInfoInstance->updatePageInfo($category_url_key, 'category', $put['cname'], '', '');
	  	}
	  	else{
	  		$this->_pageInfoInstance->insertPageInfo($category_url_key, 'category', $category_id, $put['cname'], '', '');
	  		//add url to site map.
	  		//add_to_site_map(DOMAIN_BASE_PATH.$category_url_key.'.html');
	  		build_site_map_from_db();
	  	}
  	}
  	echo 'Update Success!';
  	}catch(Exception $e){
  		echo $e->getMessage();
  	}
  }
  
  public function getAction($url_key){
  	if(!$this->verifyAuthentication()){
  		echo 'Authentication error!';
  		return;
  	}
  	//if passed the authentication.
  	try{
	  	$articleInfo = $this->_pageInfoInstance->getDetailedInfoByUrlKey($url_key);
	  	if(!$articleInfo || !isset($articleInfo->aid)){
	  		echo 'Article Not Found';
	  		return;
	  	}
	  	$category = $this->_articleInstance->getArticleCategory($articleInfo->aid);
	  	$articleInfo->cname = $category->title;
	  	unset($articleInfo->aid);
	  	unset($articleInfo->refid);
	  	echo json_encode($articleInfo);
  	}catch(Exception $e){
  		echo $e->getMessage();
  	}
  }
  
  public function deleteAction($url_key){
  	if(!$this->verifyAuthentication()){
  		echo 'Authentication error!';
  		return;
  	}
  	//if passed the authentication.
  	try{
	  	//find the refid from the pageinfo
	  	$pageInfo = $this->_pageInfoInstance->getPageInfo($url_key);
	  	if(!$pageInfo || $pageInfo->type != 'detail'){
	  		echo 'Nothing can be deleted by the provide information';
	  		return;
	  	}
	  	$aid = $pageInfo->refid;
	  	$category = $this->_articleInstance->getArticleCategory($aid);
	  	
	  	if($category){
	  		$cid = $category->cid;
		  	//remove $category if there is no article associated to it any longer.
		  	$this->_categoryInstance->deleteArticleCategory($aid, $cid);
	  		if(!$this->_categoryInstance->getCategoryArticles($cid)){
		  		//delete this category
		  		$this->_pageInfoInstance->deletePageInfo(array('refid'=>$cid, 'type'=>'category'));
		  		$this->_categoryInstance->deleteCategory($cid);
	  		}
	  	}
	  	$this->_articleInstance->deleteArticle($aid);
	  	$this->_pageInfoInstance->deletePageInfo(array('urlkey'=>$url_key, 'type'=>'detail'));
	  	echo 'Delete Success!';
	  	build_site_map_from_db();
	  	cache::clean();
	  	
    }catch(Exception $e){
  		echo 'Delete Partially Failed!';
  	}
  }
  
  public function verifyAuthentication($realm=''){
  	if($realm !== ''){
  	  	$realm = base64_decode($realm);
  	  	list($uname, $pass)= split(':', $realm);
  	}else if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
  		$uname = $_SERVER['PHP_AUTH_USER'];
  		$pass = $_SERVER['PHP_AUTH_PW'];
  	}else if(isset($_POST['realm'])){
  		//using fastCGI.
		$realm = base64_decode($_POST['realm']);
		list($uname, $pass)= split(':', $realm);
  	}else if(isset($_GET['realm'])){
		$realm = base64_decode($_GET['realm']);
		list($uname, $pass)= split(':', $realm);
  	}else{
  	  	parse_str(file_get_contents("php://input"),$clientData);
  	  	$realm = base64_decode($clientData['realm']);
  	  	list($uname, $pass)= split(':', $realm);
  	}
  	
  	if($uname == 'mdadmin'){
  		$pass_params = array();
  		date_default_timezone_set("Etc/GMT-8");
  		$pass_params[] = date('Ymd');
  		$pass_params[] = 'articles';
  		$pass_params[] = 'mingdabeta';
  		$new_pass = $this->_simple_transform($pass_params);
  		if($new_pass == $pass){
  			return true;
  		}
  	}
  	return $new_pass;
  }

  private function _simple_transform($pass_params){
  	$count = count($pass_params);
  	$pass_str = implode('', $pass_params);
  	$new_pass = $this->_resort_order($pass_str);
  	$new_pass = gzcompress($new_pass, 9);
  	//get the last 8 character.
  	$new_pass = substr($new_pass, 0, 8);
  	return $new_pass;
  }
  
  private function _resort_order($pass_str){
  	$new_pass_array = array(0=>'', 1=>'', 2=>'');
  	for($i = 0; $i < strlen($pass_str); $i++){
  		if($i%3 == 0){
  			$new_pass_array[0] = $new_pass_array[0]. $pass_str[$i];
  		}else if($i%3 == 1){
  			$new_pass_array[1] = $new_pass_array[1]. $pass_str[$i];
  		}else{
  			$new_pass_array[2] = $new_pass_array[2]. $pass_str[$i];
  		}
  	}
  	$new_pass = implode('', $new_pass_array);
  	//rotate string with str_rot13
  	$new_pass = str_rot13($new_pass);
  	
  	return $new_pass;
  }
}