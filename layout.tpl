<h1>Карта сайта</h1>
{data::rubs}
{rubs:}
{~key!:pages?:heading}
{~conf.sitemap.li?:libr?::item}
{heading:}<h2 class="mt-3">{~key}</h2>
{item:}<a href="/{loc}">{title}</a>, 
{itembr:}<li><a href="/{loc}">{title}</a></li>
{libr:}
	<ul>{::itembr}</ul>
{XML:}<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	{list::xmlgroup}
</urlset>
{xmlgroup:}{::xmlitem}
{xmlitem:}<url>
        <loc>{protocol}://{host}{loc:slstr}</loc>
        <lastmod>{lastmod}</lastmod>
        <changefreq>{changefreq}</changefreq>
        <priority>{priority}</priority>
    </url>
    {slstr:}/{.}