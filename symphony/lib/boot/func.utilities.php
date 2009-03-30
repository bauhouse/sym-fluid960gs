<?php

	/***
	
	Method: redirect
	Description: redirects the browser to a specified location. Safer than using a direct header() call
	Param: $url - location to redirect to
	
	***/		
   	function redirect ($url){
		
		$url = str_replace('Location:', '', $url); //Just make sure.
		
		if(headers_sent($filename, $line)){
			print "<h1>Error: Cannot redirect to <a href=\"$url\">$url</a></h1><p>Output has already started in $filename on line $line</p>";
			exit();
		}
		
		header('Expires: Mon, 12 Dec 1982 06:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
        header("Location: $url");
        exit();	
    }

	function array_union_simple($xx, $yy){
		return                   
	        array_merge(
	            array_intersect($xx, $yy),
	            array_diff($xx, $yy),
	            array_diff($yy, $xx)
	        );
	}

	function symphony_request_uri(){
		
		if(isset($_SERVER['REQUEST_URI'])) return $_SERVER['REQUEST_URI'];
		
		return str_replace('index.php', '', $_SERVER['PHP_SELF']) . trim($_REQUEST['page'], '/') . '/?' . preg_replace('@&?page='.$_REQUEST['page'].'&?@i', '', $_SERVER['QUERY_STRING']);
	}

	function strallpos($haystack, $needle, &$count, $offset=0) {
		$match = array();
		
		if($offset > strlen($haystack)) return $match; 
			
		for ($count=0; (($pos = strpos($haystack, $needle, $offset)) !== false); $count++) {
			$match[] = $pos;
			$offset = $pos + strlen($needle);
		}
		
		return $match;
	}
	
	function getcwd_safe(){
		return str_replace('\\', '/', getcwd());
	}
	
	function define_safe($name, $val){
		if(!defined($name)) define($name, $val);
	}
	
	function getCurrentPage($page=NULL){
		if(!$page) $page = $_GET['page'];
		return (trim($page, '/') != '' ? '/' . trim($page, '/') . '/' : NULL);
	}
	
	function precision_timer($action = 'start', $start_time = null){		
		list($time, $micro) = explode(' ', microtime());
		
		$currtime = $time + $micro;
		
		if(strtolower($action) == 'stop')
			return number_format(abs($currtime - $start_time), 4, '.', ',');	
		
		return $currtime;
	}
	
	## 'sys_get_temp_dir' doesnt exist in PHP 5.2 or lower.
	## minghong at gmail dot com
	## http://au2.php.net/sys_get_temp_dir
	if (!function_exists('sys_get_temp_dir')){
		function sys_get_temp_dir(){
			
			## Try to get from environment variable
			if(!empty($_ENV['TMP'])): return realpath($_ENV['TMP']);
			elseif(!empty($_ENV['TMPDIR'])): return realpath($_ENV['TMPDIR']);
			elseif(!empty($_ENV['TEMP'])): return realpath($_ENV['TEMP']);

			## Try creating a temporary file instead
			else:
		
				$temp_file = tempnam(md5(uniqid(rand(), TRUE)), NULL);
			
				if(!$temp_file) return FALSE;

				$temp_dir = realpath(dirname($temp_file));
				unlink($temp_file);
				return $temp_dir;
		
			endif;
		
		}
	}	
