<?php
namespace akiyatkin\sitemap;
use infrajs\ans\Ans;
use infrajs\once\Once;
use infrajs\path\Path;
use infrajs\config\Config;
use infrajs\rubrics\Rubrics;
use akiyatkin\showcase\Showcase;
use akiyatkin\showcase\Data;
use infrajs\access\Access;

class Sitemap {
	public static $conf = array();
	public static function data() {
		return Access::func(function (){
			return Sitemap::_data();
		});
	}
	public static function _data() {
		$ans = array();
		$conf = Sitemap::$conf;
		$ans['pages'] = [];
		$ans['pages'][] = [
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
					$ans['pages'][] = [
						'title' => $p['heading'] ? $p['heading']: $p['title'],
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
					$ans['pages'][] = [
						'title' => $opt['title'],
						'loc' => 'files'
					];
				}
				if ($opt['type'] != 'list') continue;
				$ans[$opt['title']] = [];
				
				if (!empty($opt['dir'])) $dir = $opt['dir'];
				else $dir = '~'.$key.'/';


				$list = Rubrics::list($dir);
				foreach ($list as $p) {
					$ans[$opt['title']][] = [
						'title' => $p['heading'] ? $p['heading']: $p['title'],
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
			foreach ($conf['plugins']['pages'] as $page => $opt) {
				$ans['pages'][] = [
					'title' => $opt['title'],
					'loc' => $page,
					'changefreq' => "yearly",
				];		
			}
		}
		if ($conf['plugins']['sitemap']) {
			$ans['pages'][] = [
				'title' => 'Карта сайта',
				'loc' => $conf['plugins']['sitemap'],
				'time' => Access::adminTime(),
				'lastmod' => date('Y-m-d',Access::adminTime()),
				'changefreq' => "weekly",
				'priority' => 1
			];
		}
		if ($conf['plugins']['showcase'] && class_exists("Data")) {
			$opt = $conf['plugins']['showcase'];
			//$producers = Showcase::getProducers();
			//Дата группы
			//Дата производителя
			//Дата Модели - готово (каталог и прайс) (При связи с файлами можно проверять ещё)
			//$groups = Showcase::getGroups();
			
			
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

			$ans['Производители'] = [];
			$ans['Группы'] = [];
			
			$ans['Модели'] = [];
			
			
			foreach ($list as $pos) {
				$title = $pos['producer'];
				if (empty($ans['Производители'][$title])) {
					$ans['Производители'][$title] = [
						'title' => $title,
						'loc' => $opt['url'].'/'.urlencode($pos['producer_nick']),
						'time' => $pos['time'],
						'lastmod' => date('Y-m-d',$pos['time']),
						'changefreq' => "monthly",
						'priority' => $opt['priority'] + (1 - $opt['priority'])/2
					];
				}
				if ($ans['Производители'][$title]['time'] < $pos['time']) {
					$ans['Производители'][$title]['time'] = $pos['time'];
					$ans['Производители'][$title]['lastmod'] = date('Y-m-d', $pos['time']);
				}


				$title = $pos['parent'].' / '.$pos['group'];
				if (empty($ans['Группы'][$title])) {
					if (!$pos['parent']) $loc = $opt['url'];
					else $loc = $opt['url'].'/'.urlencode($pos['group_nick']);
					$ans['Группы'][$title] = [
						'title' => $pos['group'],
						'loc' => $loc,
						'time' => $pos['time'],
						'lastmod' => date('Y-m-d',$pos['time']),
						'changefreq' => "monthly",
						'priority' => $opt['priority'] + (1 - $opt['priority'])/2
					];
				}
				if ($ans['Группы'][$title]['time'] < $pos['time']) {
					$ans['Группы'][$title]['time'] = $pos['time'];
					$ans['Группы'][$title]['lastmod'] = date('Y-m-d', $pos['time']);
				}

				$title = $pos['producer'].' '.$pos['article'];
				$ans['Модели'][$title] = [
					'title' => $pos['article'],
					'loc' => $opt['url'].'/'.urlencode($pos['producer_nick']).'/'.urlencode($pos['article_nick']),
					'time' => $pos['time'],
					'lastmod' => date('Y-m-d',$pos['time']),
					'changefreq' => "yearly",
					'priority' => $opt['priority']
				];	
			}
			ksort($ans['Группы']);
			$ans['Группы'] = array_values($ans['Группы']);

			ksort($ans['Производители']);
			$ans['Производители'] = array_values($ans['Производители']);

			ksort($ans['Модели']);
			$ans['Модели'] = array_values($ans['Модели']);

		}
		return $ans;
	}
}