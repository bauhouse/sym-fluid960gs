<?php

	define_safe('DS_FILTER_AND', 1);
	define_safe('DS_FILTER_OR', 2);
	
	##Interface for datasouce objects
	Class DataSource{
		
		var $_env;
		var $_Parent;
		var $_param_output_only;
		var $_dependencies;
		var $_force_empty_result;
		
		const CRLF = "\r\n";
		
		function __construct(&$parent, $env=NULL, $process_params=true){
			$this->_Parent = $parent;
			$this->_force_empty_result = false;

			if($process_params){ 
				$this->processParameters($env);
			}
		}
		
		function processParameters($env=NULL){
									
			if($env) $this->_env = $env;
			
			if((isset($this->_env) && is_array($this->_env)) && is_array($this->dsParamFILTERS) && !empty($this->dsParamFILTERS)){
				foreach($this->dsParamFILTERS as $key => $value){
					$value = stripslashes($value);
					$new_value = $this->__processParametersInString($value, $this->_env);

					if(strlen(trim($new_value)) == 0) unset($this->dsParamFILTERS[$key]);
					else $this->dsParamFILTERS[$key] = $new_value;
					
				}
			}

			if(isset($this->dsParamORDER)) $this->dsParamORDER = $this->__processParametersInString($this->dsParamORDER, $this->_env);
			
			if(isset($this->dsParamSORT)) $this->dsParamSORT = $this->__processParametersInString($this->dsParamSORT, $this->_env);

			if(isset($this->dsParamSTARTPAGE)) $this->dsParamSTARTPAGE = $this->__processParametersInString($this->dsParamSTARTPAGE, $this->_env);
		
			if(isset($this->dsParamLIMIT)) $this->dsParamLIMIT = $this->__processParametersInString($this->dsParamLIMIT, $this->_env);
		
			if(isset($this->dsParamREQUIREDPARAM) && $this->__processParametersInString($this->dsParamREQUIREDPARAM, $this->_env, false) == '') $this->_force_empty_result = true;
			
			$this->_param_output_only = ((!is_array($this->dsParamINCLUDEDELEMENTS) || empty($this->dsParamINCLUDEDELEMENTS)) && !isset($this->dsParamGROUP));
			
			if($this->dsParamREDIRECTONEMPTY == 'yes' && $this->_force_empty_result) $this->__redirectToErrorPage();

					
		}
		
		function __redirectToErrorPage(){
			$page_id = $this->_Parent->Database->fetchVar('page_id', 0, "SELECT `page_id` FROM `tbl_pages_types` WHERE tbl_pages_types.`type` = '404' LIMIT 1");
			
			if(!$page_id) $this->_Parent->customError(E_USER_ERROR, __('Page Not Found'), __('The page you requested does not exist.'), false, true, 'error', array('header' => 'HTTP/1.0 404 Not Found'));
			else{
				$url = URL . '/' . $this->_Parent->resolvePagePath($page_id) . '/';
				redirect($url);
			}
			
		}
		
		function emptyXMLSet(){
			$xml = new XMLElement($this->dsParamROOTELEMENT);
			$xml->appendChild($this->__noRecordsFound());
			
			return $xml;
		}
		
		function __appendIncludedElements(&$wrapper, $fields){
			if(!isset($this->dsParamINCLUDEDELEMENTS) || !is_array($this->dsParamINCLUDEDELEMENTS) || empty($this->dsParamINCLUDEDELEMENTS)) return;
			
			foreach($this->dsParamINCLUDEDELEMENTS as $index) {
				
				if(!is_object($fields[$index])){
					trigger_error(__('%s is not a valid object. Failed to append to XML.', array($index)), E_USER_WARNING);
					continue;
				}
				$wrapper->appendChild($fields[$index]);
			}	
		}
		
		function __determineFilterType($value){
			return (false === strpos($value, '+') ? DS_FILTER_OR : DS_FILTER_AND);
		}
		
		function __noRecordsFound(){
			return new XMLElement('error', __('No records found.'));
		}

		function __processParametersInString($value, $env, $includeParenthesis=true, $escape=false){
			if(trim($value) == '') return NULL;

			if(!$includeParenthesis) $value = '{'.$value.'}';

			if(preg_match_all('@{([^}]+)}@i', $value, $matches, PREG_SET_ORDER)){

				foreach($matches as $match){
					
					list($source, $cleaned) = $match;
					
					$replacement = NULL;
					
					$bits = preg_split('/:/', $cleaned, -1, PREG_SPLIT_NO_EMPTY);
					
					foreach($bits as $param){
						
						if($param{0} != '$'){
							$replacement = $param;
							break;
						}
						
						$param = trim($param, '$');
						
						$replacement = $this->__findParameterInEnv($param, $env);
						
						if(is_array($replacement)){
							if(count($replacement) > 1) $replacement = implode(',', $replacement);
							else $replacement = end($replacement);
						}
						
						if(!empty($replacement)) break;
						
					}
					
					if($escape == true) $replacement = urlencode($replacement);
					$value = str_replace($source, $replacement, $value);
					
				}
			}

			return $value;
		}
		
		function __findParameterInEnv($needle, $env){

			if(isset($env['env']['url'][$needle])) return $env['env']['url'][$needle];

			if(isset($env['env']['pool'][$needle])) return $env['env']['pool'][$needle];

			if(isset($env['param'][$needle])) return $env['param'][$needle];

			return NULL;
						
		}

		## This function is required in order to edit it in the data source editor page. 
		## Do not overload this function if you are creating a custom data source. It is only
		## used by the data source editor
		function allowEditorToParse(){
			return false;
		}
				
		## This function is required in order to identify what type of data source this is for
		## use in the data source editor. It must remain intact. Do not overload this function into
		## custom data sources.
		function getSource(){
			return NULL;
		}
				
		function getDependencies(){
			return $this->_dependencies;
		}
				
		##Static function
		function about(){		
		}

		function grab($param=array()){
		}
	
	}
	
