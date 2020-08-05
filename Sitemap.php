<?php
namespace akiyatkin\sitemap;
use infrajs\ans\Ans;
use infrajs\path\Path;
use infrajs\config\Config;
use infrajs\rubrics\Rubrics;
use akiyatkin\showcase\Showcase;
use akiyatkin\showcase\Data;
use infrajs\access\Access;

class Sitemap {
	public static $conf = array();
	public static function data() {
		return Access::cache(__FILE__, function (){
			return Sitemap::_data();
		});
	}
	public static function _data() {
		$ans = array();
		$conf = Sitemap::$conf;
		$ans['pages'] = ['list'=>[],'title'=>"Страницы"];
		$ans['pages']['list'][] = [
			'title' => 'Главная',
			'loc' => '',
			'time' => Access::adminTime(),
			'lastmod' => date('Y-m-d',Access::adminTime()),
			'changefreq' => "daily",
			'priority' => 1
		];
		
		
		
		if ($conf['plugins']['rubrics']) {
			//Дата изменения фала
			if (Rubrics::$conf['main']) {
				$key = Rubrics::$conf['main'];
				$opt = Rubrics::$conf['list'][$key];

				if (!empty($opt['dir'])) $dir = $opt['dir'];
				else $dir = '~'.$key.'/';


				$list = Rubrics::list($dir);
				foreach ($list as $p) {
					$ans['pages']['list'][] = [
						'title' => isset($p['heading']) ? $p['heading']: $p['title'],
						'loc' => $p['name'],
						'time' => $p['modified'],
						'lastmod' => date('Y-m-d', $p['modified']),
						'changefreq' => "yearly",
						'priority' => 1
					];
					
				}
			}
			foreach(Rubrics::$conf['list'] as $key=>$opt) {
				if ($opt['type'] == 'files') {
					$ans['pages']['list'][] = [
						'title' => $opt['title'],
						'loc' => 'files',
						'time' => Access::adminTime(),
						'lastmod' => date('Y-m-d',Access::adminTime()),
						'changefreq' => "yearly",
						'priority' => 0.5
					];
				}
				if ($opt['type'] != 'list') continue;
				$ans[$opt['title']] = ['list'=>[],'src'=>$key,'title'=>$opt['title']];
				
				if (!empty($opt['dir'])) $dir = $opt['dir'];
				else $dir = '~'.$key.'/';


				$list = Rubrics::list($dir);
				foreach ($list as $p) {
					$ans[$opt['title']]['list'][] = [
						'title' => isset($p['heading']) ? $p['heading']: $p['title'],
						'loc' => $key.'/'.$p['name'],
						'time' => $p['modified'],
						'lastmod' => date('Y-m-d', $p['modified']),
						'changefreq' => "yearly",
						'priority' => 1
					];
					
				}
			}
		}

		if ($conf['plugins']['pages']) {
			//if (empty($ans['Страницы'])) = ['list'=>[],'title'=>'Страницы'];
			foreach ($conf['plugins']['pages'] as $page => $opt) {
				$ans['pages']['list'][] = [
					'title' => $opt['title'],
					'loc' => $page,
					'time' => Access::adminTime(),
					'lastmod' => date('Y-m-d', Access::adminTime()),
					'changefreq' => "yearly",
					'priority' => 0.5
				];
			}
		}

		
		if (!empty($conf['plugins']['showcase']) && class_exists("\\akiyatkin\\showcase\\Data")) {
			$opt = $conf['plugins']['showcase'];
			
			$ans['pages']['list'][] = [
				'title' => 'Каталог',
				'loc' => 'catalog',
				'time' => Access::adminTime(),
				'lastmod' => date('Y-m-d',Access::adminTime()),
				'changefreq' => "monthly",
				'priority' => 0.25
			];
			
			$list = Data::all('SELECT 
				m.article_nick, 
				m.article,
				p.producer_nick, 
				p.producer,
				unix_timestamp(m.time) as time,
				g.group_nick,
				g.group,
				gp.group as parent
				from showcase_models m 
				left join showcase_producers p on p.producer_id = m.producer_id
				left join showcase_groups g on g.group_id = m.group_id
				left join showcase_groups gp on g.parent_id = gp.group_id');

			$ans['Производители'] = ['list'=>[],'title'=>'Производители'];
			$ans['Группы'] = ['list'=>[],'title'=>'Группы'];
			
			$ans['Модели'] = ['list'=>[],'title'=>'Модели'];
			
			
			foreach ($list as $pos) {
				$title = $pos['producer'];
				if (empty($ans['Производители']['list'][$title])) {
					$ans['Производители']['list'][$title] = [
						'title' => $title,
						'loc' => $opt['url'].'/'.urlencode($pos['producer_nick']),
						'time' => $pos['time'],
						'lastmod' => date('Y-m-d',$pos['time']),
						'changefreq' => "monthly",
						'priority' => $opt['priority'] + (1 - $opt['priority'])/2
					];
				}
				if ($ans['Производители']['list'][$title]['time'] < $pos['time']) {
					$ans['Производители']['list'][$title]['time'] = $pos['time'];
					$ans['Производители']['list'][$title]['lastmod'] = date('Y-m-d', $pos['time']);
				}


				$title = $pos['parent'].' / '.$pos['group'];
				if (empty($ans['Группы']['list'][$title])) {
					if (!$pos['parent']) $loc = $opt['url'];
					else $loc = $opt['url'].'/'.urlencode($pos['group_nick']);
					$ans['Группы']['list'][$title] = [
						'title' => $pos['group'],
						'loc' => $loc,
						'time' => $pos['time'],
						'lastmod' => date('Y-m-d',$pos['time']),
						'changefreq' => "monthly",
						'priority' => $opt['priority'] + (1 - $opt['priority'])/2
					];
				}
				if ($ans['Группы']['list'][$title]['time'] < $pos['time']) {
					$ans['Группы']['list'][$title]['time'] = $pos['time'];
					$ans['Группы']['list'][$title]['lastmod'] = date('Y-m-d', $pos['time']);
				}

				$title = $pos['producer'].' '.$pos['article'];
				$ans['Модели']['list'][$title] = [
					'title' => $pos['article'],
					'loc' => $opt['url'].'/'.urlencode($pos['producer_nick']).'/'.urlencode($pos['article_nick']),
					'time' => $pos['time'],
					'lastmod' => date('Y-m-d',$pos['time']),
					'changefreq' => "yearly",
					'priority' => $opt['priority']
				];	
			}
			ksort($ans['Группы']['list']);
			$ans['Группы']['list'] = array_values($ans['Группы']['list']);

			ksort($ans['Производители']['list']);
			$ans['Производители']['list'] = array_values($ans['Производители']['list']);

			ksort($ans['Модели']['list']);
			$ans['Модели']['list'] = array_values($ans['Модели']['list']);

		}
		if ($conf['plugins']['sitemap']) {
			$ans['pages']['list'][] = [
				'title' => 'Карта сайта',
				'loc' => $conf['plugins']['sitemap'],
				'time' => Access::adminTime(),
				'lastmod' => date('Y-m-d',Access::adminTime()),
				'changefreq' => "weekly",
				'priority' => 1
			];
		}
		return $ans;
	}
}