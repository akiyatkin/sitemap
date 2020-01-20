<?php
use infrajs\ans\Ans;
use infrajs\path\Path;
use infrajs\rest\Rest;
use akiyatkin\sitemap\Sitemap;


return Rest::get( function () {
	$data = Sitemap::data();
	return Ans::ans($data);
});


