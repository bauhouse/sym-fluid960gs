<?php

	header('Expires: Mon, 12 Dec 1982 06:14:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-cache, must-revalidate, max-age=0');
	header('Pragma: no-cache');

	function __errorHandler($errno=NULL, $errstr, $errfile=NULL, $errline=NULL, $errcontext=NULL){
		return;
	}

	error_reporting(E_ALL ^ E_NOTICE);
	set_error_handler('__errorHandler');

	define('kBUILD', '<!-- BUILD -->');
	define('kVERSION', '<!-- VERSION -->');
	define('kINSTALL_ASSET_LOCATION', './symphony/assets/installer');	
	define('kINSTALL_FILENAME', basename(__FILE__));
	
	## Show PHP Info
	if(isset($_REQUEST['info'])){
		phpinfo(); 
		exit();
	}
	
	function setLanguage() {
		require_once('symphony/lib/toolkit/class.lang.php');
		$lang = NULL;

		if(!empty($_REQUEST['lang'])){
			$l = preg_replace('/[^a-zA-Z\-]/', '', $_REQUEST['lang']);
			if(file_exists("./symphony/lib/lang/lang.{$l}.php")) $lang = $l;
		}

		if($lang === NULL){
			foreach(Lang::getBrowserLanguages() as $l){
				if(file_exists("./symphony/lib/lang/lang.{$l}.php")) $lang = $l;
				break;
			}
		}

		## none of browser accepted languages is available, get first available
		if($lang === NULL){
			## default to English
			if(file_exists('./symphony/lib/lang/lang.en.php')) $lang = 'en';
			else{
				$l = Lang::getAvailableLanguages();
				if(is_array($l) && count($l) > 0) $lang = $l[0];
			}
		}

		if($lang === NULL) return NULL;

		try{
			Lang::init('./symphony/lib/lang/lang.%s.php', $lang);
		}
		catch(Exception $s){
			return NULL;
		}

		define('__LANG__', $lang);
		return $lang;
	}

	
	/***********************
	         TESTS
	************************/

	// Check for PHP 5.1+

	if(version_compare(phpversion(), '5.1.3', '<=')){

		$code = '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>Outstanding Requirements</title>
		<link rel="stylesheet" type="text/css" href="'.kINSTALL_ASSET_LOCATION.'/main.css"/>
		<script type="text/javascript" src="'.kINSTALL_ASSET_LOCATION.'/main.js"></script>
	</head>
		<body>
			<h1>Install Symphony <em>Version '.kVERSION.'</em></h1>
			<h2>Outstanding Requirements</h2>
			<p>Symphony needs the following requirements satisfied before installation can proceed.</p>

			<dl>
				<dt><abbr title="PHP: Hypertext Pre-processor">PHP</abbr> 5.1.3 or above</dt>
				<dd>Symphony needs a recent version of <abbr title="PHP: Hypertext Pre-processor">PHP</abbr>.</dd>
			</dl>

		</body>

</html>';

		die($code);

	}

	// Check and set language
	if(setLanguage() === NULL){

		$code = '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>Outstanding Requirements</title>
		<link rel="stylesheet" type="text/css" href="'.kINSTALL_ASSET_LOCATION.'/main.css"/>
		<script type="text/javascript" src="'.kINSTALL_ASSET_LOCATION.'/main.js"></script>
	</head>
		<body>
			<h1>Install Symphony <em>Version '.kVERSION.'</em></h1>
			<h2>Outstanding Requirements</h2>
			<p>Symphony needs at least one language file to be present before installation can proceed.</p>

		</body>

</html>';

		die($code);

	}

	// Check if Symphony is already installed

	if(file_exists('manifest/config.php')){

		$code = '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>'.__('Existing Installation').'</title>
		<link rel="stylesheet" type="text/css" href="'.kINSTALL_ASSET_LOCATION.'/main.css"/>
		<script type="text/javascript" src="'.kINSTALL_ASSET_LOCATION.'/main.js"></script>
	</head>
		<body>
			<h1>'.__('Install Symphony <em>Version %s</em>', array(kVERSION)).'</h1>
			<h2>'.__('Existing Installation').'</h2>
			<p>'.__('It appears that Symphony has already been installed at this location.').'</p>

		</body>

</html>';

		die($code);

	}
		
	/////////////////////////
	
	function getDynamicConfiguration(){
	
		$conf = array();
	
		<!-- CONFIGURATION -->
	
		return $conf;
	
	}	
	
	function getTableSchema(){
		$sql = <<<SQL

		<!-- ENCODED SQL SCHEMA DUMP -->

SQL;

		return base64_decode(trim($sql));

	}

	function getWorkspaceData(){
		$sql = <<<SQL

		<!-- ENCODED SQL DATA DUMP -->

SQL;

		return base64_decode(trim($sql));

	}
		
	define('INSTALL_REQUIREMENTS_PASSED', true);
	include_once('./symphony/lib/toolkit/include.install.php');

