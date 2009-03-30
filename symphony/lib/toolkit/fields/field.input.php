<?php
	
	require_once(TOOLKIT . '/class.xsltprocess.php');
	
	Class fieldInput extends Field {
		function __construct(&$parent){
			parent::__construct($parent);
			$this->_name = __('Text Input');
			$this->_required = true;
			
			$this->set('required', 'no');
		}

		function allowDatasourceOutputGrouping(){
			return true;
		}
		
		function allowDatasourceParamOutput(){
			return true;
		}

		function groupRecords($records){
			
			if(!is_array($records) || empty($records)) return;
			
			$groups = array($this->get('element_name') => array());
			
			foreach($records as $r){
				$data = $r->getData($this->get('id'));
				
				$value = $data['value'];
				$handle = Lang::createHandle($value);
				
				if(!isset($groups[$this->get('element_name')][$handle])){
					$groups[$this->get('element_name')][$handle] = array('attr' => array('handle' => $handle, 'value' => $value),
																		 'records' => array(), 'groups' => array());
				}	
																					
				$groups[$this->get('element_name')][$handle]['records'][] = $r;
								
			}

			return $groups;
		}

		function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL){
			$value = General::sanitize($data['value']);
			$label = Widget::Label($this->get('label'));
			if($this->get('required') != 'yes') $label->appendChild(new XMLElement('i', __('Optional')));
			$label->appendChild(Widget::Input('fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix, (strlen($value) != 0 ? $value : NULL)));

			if($flagWithError != NULL) $wrapper->appendChild(Widget::wrapFormElementWithError($label, $flagWithError));
			else $wrapper->appendChild($label);
		}
		
		function isSortable(){
			return true;
		}
		
		function canFilter(){
			return true;
		}
		
		public function canImport(){
			return true;
		}

		function buildSortingSQL(&$joins, &$where, &$sort, $order='ASC'){
			$joins .= "INNER JOIN `tbl_entries_data_".$this->get('id')."` AS `ed` ON (`e`.`id` = `ed`.`entry_id`) ";
			$sort = 'ORDER BY ' . (in_array(strtolower($order), array('random', 'rand')) ? 'RAND()' : "`ed`.`value` $order");
		}
		
		public function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation = false) {
			$field_id = $this->get('id');
			
			if (self::isFilterRegex($data[0])) {
				$this->_key++;
				$pattern = str_replace('regexp:', '', $this->cleanValue($data[0]));
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND (
						t{$field_id}_{$this->_key}.value REGEXP '{$pattern}'
						OR t{$field_id}_{$this->_key}.handle REGEXP '{$pattern}'
					)
				";
				
			} elseif ($andOperation) {
				foreach ($data as $value) {
					$this->_key++;
					$value = $this->cleanValue($value);
					$joins .= "
						LEFT JOIN
							`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
							ON (e.id = t{$field_id}_{$this->_key}.entry_id)
					";
					$where .= "
						AND (
							t{$field_id}_{$this->_key}.value = '{$value}'
							OR t{$field_id}_{$this->_key}.handle = '{$value}'
						)
					";
				}
				
			} else {
				if (!is_array($data)) $data = array($data);
				
				foreach ($data as &$value) {
					$value = $this->cleanValue($value);
				}
				
				$this->_key++;
				$data = implode("', '", $data);
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND (
						t{$field_id}_{$this->_key}.value IN ('{$data}')
						OR t{$field_id}_{$this->_key}.handle IN ('{$data}')
					)
				";
			}
			
			return true;
		}

		function __applyValidationRules($data){			
			$rule = $this->get('validator');
			return ($rule ? General::validateString($data, $rule) : true);
		}

		function checkPostFieldData($data, &$message, $entry_id=NULL){

			$message = NULL;
			
			$handle = Lang::createHandle($data);
			
			if($this->get('required') == 'yes' && strlen($data) == 0){
				$message = __("'%s' is a required field.", array($this->get('label')));
				return self::__MISSING_FIELDS__;
			}	
			
			if(!$this->__applyValidationRules($data)){
				$message = __("'%s' contains invalid data. Please check the contents.", array($this->get('label')));
				return self::__INVALID_FIELDS__;	
			}
			
			if(!General::validateXML(General::sanitize($data), $errors, false, new XsltProcess)){
				$message = __('"%1$s" contains invalid XML. The following error was returned: <code>%2$s</code>', array($this->get('label'), $errors[0]['message']));
				return self::__INVALID_FIELDS__;
			}
			
			return self::__OK__;
							
		}
		
		public function processRawFieldData($data, &$status, $simulate = false, $entry_id = null) {
			if (trim($data) == '') return array();
			
			$status = self::__OK__;
			
			$result = array(
				'value' => $data,
				'handle' => Lang::createHandle($data)
			);
			
			return $result;
		}

		function canPrePopulate(){
			return true;
		}

		function appendFormattedElement(&$wrapper, $data, $encode=false){
			
			if($this->get('apply_formatting') == 'yes' && isset($data['value_formatted'])) $value = $data['value_formatted'];
			else $value = $data['value'];
			
			$value = General::sanitize($value);
			
			$wrapper->appendChild(new XMLElement($this->get('element_name'), ($encode ? General::sanitize($value) : $value), array('handle' => $data['handle'])));
		}
		
		function getEntryFormatter($entry_id){
			return $this->_engine->Database->fetchVar('formatter', 0, "SELECT `formatter` FROM `tbl_entries` WHERE `id` = '$entry_id' LIMIT 1");
		}
		
		function commit(){

			if(!parent::commit()) return false;
			
			$id = $this->get('id');

			if($id === false) return false;
			
			$fields = array();
			
			$fields['field_id'] = $id;
			$fields['validator'] = ($fields['validator'] == 'custom' ? NULL : $this->get('validator'));
			
			$this->_engine->Database->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '$id' LIMIT 1");
				
			return $this->_engine->Database->insert($fields, 'tbl_fields_' . $this->handle());
					
		}
		
		function setFromPOST($postdata){		
			parent::setFromPOST($postdata);			
			if($this->get('validator') == '') $this->remove('validator');				
		}
		
		function displaySettingsPanel(&$wrapper, $errors=NULL){
			
			parent::displaySettingsPanel($wrapper, $errors);
			
			$this->buildValidationSelect($wrapper, $this->get('validator'), 'fields['.$this->get('sortorder').'][validator]');		
			
			$this->appendRequiredCheckbox($wrapper);
			$this->appendShowColumnCheckbox($wrapper);
						
		}
		
		function createTable(){
			
			return $this->_engine->Database->query(
			
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `entry_id` int(11) unsigned NOT NULL,
				  `handle` varchar(255) default NULL,
				  `value` varchar(255) default NULL,
				  PRIMARY KEY  (`id`),
				  KEY `entry_id` (`entry_id`),
				  KEY `handle` (`handle`),
				  KEY `value` (`value`)
				) TYPE=MyISAM;"
			
			);
		}		

	}

