<?php
	Class extension_improvedpageresolve extends Extension{
	
		public function about(){
			return array('name' => __('Improved Page Resolve'),
						 'version' => '1.0',
						 'release-date' => '2009-03-02',
						 'author' => array('name' => 'Marcin Konicki',
										   'website' => 'http://ahwayakchih.neoni.net',
										   'email' => 'ahwayakchih@neoni.net'),
						 'description' => __('Pass parameters to index if none of them selects valid page.')
			);
		}

		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/frontend/',
					'delegate' => 'FrontendPrePageResolve',
					'callback' => '__pagePreResolve'
				),
				array(
					'page' => '/frontend/',
					'delegate' => 'FrontendParamsResolve',
					'callback' => '__pageParamsResolve'
				),
			);
		}

		public function __pagePreResolve($ctx) {
			// context array contains: &$row, $page

			$page = trim($ctx['page'], '/');
			if (!empty($ctx['row']) || empty($page)) return;

			$Frontend = Frontend::instance();

			$nodeCount = substr_count($page, '/') + 1;
			$row = $Frontend->Database->fetchRow(0, "SELECT p.*, t.type FROM `tbl_pages` p
				 LEFT JOIN `tbl_pages_types` t ON p.id = t.page_id AND t.type = 'index'
				 WHERE POSITION(CONCAT_WS('/', p.path, p.handle) IN '".$Frontend->Database->cleanValue($page)."') = 1 OR
				  (t.type = 'index' AND p.params IS NOT NULL AND (LENGTH(p.params)-LENGTH(REPLACE(COALESCE(p.params,''), '/', ''))+1) >= {$nodeCount})
				 ORDER BY (LENGTH(p.path)-LENGTH(REPLACE(COALESCE(p.path,''), '/', ''))+1) DESC, p.sortorder DESC
				 LIMIT 1");
			if(!$row) $row = array();

			$path = ($row['path'] ? $row['path'].'/'.$row['handle'] : $row['handle']);
			$values = trim(str_replace($path, '', $page), '/');

			if(!empty($values)){
				// Try to stay compatible with original by rejecting page if there are too many values passed to it
				if(empty($row['params'])) $row = array();
				else{
					$values = preg_split('/\//', $values, -1, PREG_SPLIT_NO_EMPTY);
					$params = preg_split('/\//', $row['params'], -1, PREG_SPLIT_NO_EMPTY);

					if(count($params) < count($values)) $row = array();
					else if(!empty($values)){
						// There is no way to tell Frontpage to set _env['url'] values
						// (_env is private, and is overwritten with NULL values right after delegate returns).
						// We also can't set Frontpage->_param directly, because it is recreated later (after FrontendPageResolved delegate).
						// Nor we can store params localy, because extension seems to be recreated every time delegate is called.
						// So we store params in global place ($Frontend :) and inject them when FrontendParamsResolve delegate is called.
						$Frontend->__improvedpageresolve['params'] = array_combine($params, array_pad($values, count($params), NULL));
					}
				}
			}

			$ctx['row'] = $row;
		}

		public function __pageParamsResolve($ctx) {
			// context array contains: &$params
			$Frontend = Frontend::instance();

			if (!isset($Frontend->__improvedpageresolve)) return;

			if(!empty($Frontend->__improvedpageresolve['params'])) {
				$ctx['params'] = array_merge($ctx['params'], $Frontend->__improvedpageresolve['params']);
			}

			unset($Frontend->__improvedpageresolve);
		}
	}

