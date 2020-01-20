<?php
use infrajs\ans\Ans;
use infrajs\path\Path;
use infrajs\rest\Rest;
use infrajs\template\Template;
use akiyatkin\sitemap\Sitemap;


return Rest::get( function () {
	$data = Sitemap::data();
	return Ans::ans($data);
},'xml', function () {
	$list = Sitemap::data();
	$data = [];
	$data['list'] = $list;
	$data['host'] = $_SERVER['HTTP_HOST'];
	$data['protocol'] = 'https';
	$html = Template::parse('-sitemap/layout.tpl', $data, 'XML');
	header('Content-type:application/xml; charset=utf-8');
	return Ans::html($html);
});


