<?php

	require_once(CORE . '/class.symphony.php');
	require_once(TOOLKIT . '/class.xmldoc.php');	
	require_once(TOOLKIT . '/class.lang.php');
	require_once(TOOLKIT . '/class.manager.php');	
	require_once(TOOLKIT . '/class.frontendpage.php');
		
	Class Frontend extends Symphony{
		
		public $displayProfilerReport;
		
		public static function instance(){
			if(!(self::$_instance instanceof Frontend)) 
				self::$_instance = new self;
				
			return self::$_instance;
		}
				
		protected function __construct(){
			parent::__construct();
			
			$this->Profiler->sample('Engine Initialisation');

			$this->_env = array();
		}

		public function isLoggedIn(){
			if($_REQUEST['auth-token'] && strlen($_REQUEST['auth-token']) == 8) return $this->loginFromToken($_REQUEST['auth-token']);
			
			return parent::isLoggedIn();
		}
		
		public function display($page){
			
			$mode = FrontendPage::FRONTEND_OUTPUT_NORMAL;
			
			if($this->isLoggedIn()){
				if(isset($_GET['debug'])) $mode = FrontendPage::FRONTEND_OUTPUT_DEBUG;
				elseif(isset($_GET['profile'])) $mode = FrontendPage::FRONTEND_OUTPUT_PROFILE;
			}
			
			$oPage =& new FrontendPage($this);

			$output = $oPage->generate($page, $mode);

			return $output;

		}
		
	}
