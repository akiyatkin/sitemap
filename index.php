<?php
use infrajs\ans\Ans;
use infrajs\path\Path;
use infrajs\rest\Rest;
use infrajs\template\Template;
use akiyatkin\sitemap\Sitemap;


return Rest::get( function () {
	$data = Sitemap::data(Ans::REQ('lang'));
	return Ans::ans($data);
},'xml', function () {
	$list = Sitemap::data(Ans::REQ('lang'));
	$data = [];
	$data['list'] = $list;
	$data['host'] = $_SERVER['HTTP_HOST'];
	$data['protocol'] = Sitemap::$conf['protocol'];
	$html = Template::parse('-sitemap/layout.tpl', $data, 'XML');
	
	if (Ans::isReturn()) return $html;
	
	header('Content-type:application/xml; charset=utf-8');
	echo $html;
	
});


