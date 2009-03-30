<?php

	require_once(TOOLKIT . '/class.page.php');

	Class HTMLPage extends Page{
	
		var $Head;
		var $Html;
		var $Body;
		var $Form;
		var $_title;
		var $_head;
		var $_headers;
			
		function __construct(){
			$this->Html = new XMLElement('html');
			$this->Html->setIncludeHeader(false);
		
			$this->Head = new XMLElement('head');
		
			$this->_head = array();
		
			$this->Body = new XMLElement('body');
			$this->Form = NULL;
			
			$this->_headers = array();
		}
	
		function __build(){
			$this->__generateHead();
			$this->Html->appendChild($this->Head);
			if(is_object($this->Form)) $this->Body->appendChild($this->Form);
			$this->Html->appendChild($this->Body);
		}
		
		function generate(){
			$this->__build();	
			parent::generate();			
			return $this->Html->generate(true);
		}

		function __buildQueryString($exclude=array()){
			static $q;
			if (!is_array($q)) {
				$q = array();
				foreach($_GET as $k => $v){
					if (is_array($v)) $q[$k] = $this->__flattenQueryArray($v, $k);
					else $q[$k] = "{$k}={$v}";
				}
			}
			$exclude[] = 'page';
			return implode('&', array_diff_key($q, array_fill_keys($exclude, true)));
		}

		function __flattenQueryArray(&$array, $parent){
			$values = array();
			foreach($array as $k => $v){
				if(is_array($v)) $values[] = $this->__flattenQueryArray($v, $parent."[{$k}]");
				else $values[] = "{$parent}[{$k}]={$v}";
			}
			return implode('&', $values);
		}
		
		function setTitle($val){
			return $this->addElementToHead(new XMLElement('title', $val));
		}
		
		function addElementToHead($obj, $position=NULL){
			if(($position && isset($this->_head[$position]))) $position = General::array_find_available_index($position);
			elseif(!$position) $position = max(0, count($this->_head));			
			$this->_head[$position] = $obj;
			return $position;
		}
		
		function addScriptToHead($path, $position=NULL){			
			$script = new XMLElement('script');
			$script->setSelfClosingTag(false);
			$script->setAttributeArray(array('type' => 'text/javascript', 'src' => $path));
			return $this->addElementToHead($script, $position);
		}

		function addStylesheetToHead($path, $type='screen', $position=NULL){
			$link = new XMLElement('link');
			$link->setAttributeArray(array('rel' => 'stylesheet', 'type' => 'text/css', 'media' => $type, 'href' => $path));
			return $this->addElementToHead($link, $position);
		}

		function __generateHead(){
			
			ksort($this->_head);

			foreach($this->_head as $position => $obj){
				if(is_object($obj)) $this->Head->appendChild($obj);
			}

		}

		function removeFromHead($kind, $val, $type='screen'){
	
			switch($kind){
		
				case 'js':
				case 'string':
					if(is_array($this->_head[$kind]) && !empty($this->_head[$kind])){
						foreach($this->_head[$kind] as $k => $v){
							if($v == $val){
								unset($this->_head[$kind][$k]);
								return true;
							}
						}
					}
				
					break;
			
				case 'css':
					if(is_array($this->_head['css'][$type]) && !empty($this->_head['css'][$type])){
						foreach($this->_head['css'][$type] as $k => $v){
							if($v == $val){
								unset($this->_head['css'][$type][$k]);
								return true;
							}
						}
					}
			
					break;
			
			}
	
			return false;
		}

	
	}

?>