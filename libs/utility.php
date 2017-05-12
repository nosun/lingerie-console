<?php
function build_site_map(){
	global $db;
	$siteMapFile = DOCROOT . "sitemap.txt";
	$siteMapFileHandle = fopen($siteMapFile, 'w') or die("can't open file");
	$pageInfoInstance = PageInfo_Model::getInstance();
	$pageInfos = $pageInfoInstance->getAllPageInfos();
	
	//add detailed pages and categories
	foreach($pageInfos as $k=>$v){
		fwrite($siteMapFileHandle, DOMAIN_BASE_PATH.$v->urlkey.$v->urlsuffix);
	}
	
	//add tag urls
	$tagInstance = Tag_Model::getInstance();
	$tags = $tagInstance->getAllTags();
	foreach($tags as $k=>$v){
		fwrite($siteMapFileHandle, $stringData);
	}
	fclose($siteMapFileHandle);
}

function build_site_map_from_db(){
	global $db;
	$siteMapFile = DOCROOT . "sitemap.txt";
	$siteMapFileHandle = fopen($siteMapFile, 'w') or die("can't open file");
	$db->select('urlkey, urlsuffix');
	$db->from('pageinfo');
	$result = $db->get();
	$urlInfos = $result->all();
	foreach($urlInfos as $k=>$v){
		fwrite($siteMapFileHandle, url($v->urlkey.$v->urlsuffix));
	}
	fclose($siteMapFileHandle);
}

function add_to_site_map($url){
	$siteMapFile = DOCROOT . "sitemap.txt";
	$siteMapFileHandle = fopen($siteMapFile, 'a') or die("can't open file");
	fwrite($url);
	fclose($siteMapFileHandler);
}

function update_site_map($urls){
	$siteMapFile = DOCROOT . "sitemap.txt";
	$siteMapFileHandle = fopen($siteMapFile, 'a') or die("can't open file");
	foreach($urls as $k=>$v){
		fwrite($v);
	}
	fclose($siteMapFileHandler);
}