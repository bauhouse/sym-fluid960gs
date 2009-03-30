<?php
	
	Class fieldCheckbox extends Field {
		function __construct(&$parent){
			parent::__construct($parent);
			$this->_name = __('Checkbox');
		}

		function canToggle(){
			return true;
		}
		
		function allowDatasourceOutputGrouping(){
			return true;
		}
		
		function groupRecords($records){
			
			if(!is_array($records) || empty($records)) return;
			
			$groups = array($this->get('element_name') => array());
			
			foreach($records as $r){
				$data = $r->getData($this->get('id'));
				
				$value = $data['value'];
				
				if(!isset($groups[$this->get('element_name')][$handle])){
					$groups[$this->get('element_name')][$handle] = array('attr' => array('value' => $value),
																		 'records' => array(), 'groups' => array());
				}	
																					
				$groups[$this->get('element_name')][$handle]['records'][] = $r;
								
			}

			return $groups;
		}		
		
		function canFilter(){
			return true;
		}
		
		public function canImport(){
			return true;
		}
						
		function getToggleStates(){
			return array('yes' => __('Yes'), 'no' => __('No'));
		}
		
		function toggleFieldData($data, $newState){
			$data['value'] = $newState;
			return $data;
		}

		function processRawFieldData($data, &$status, $simulate=false, $entry_id=NULL){
			
			$status = self::__OK__;

			return array(
				'value' => (strtolower($data) == 'yes' ? 'yes' : 'no')
			);
			
		}
		
		function buildSortingSQL(&$joins, &$where, &$sort, $order='ASC'){
			$joins .= "INNER JOIN `tbl_entries_data_".$this->get('id')."` AS `ed` ON (`e`.`id` = `ed`.`entry_id`) ";
			$sort = 'ORDER BY ' . (in_array(strtolower($order), array('random', 'rand')) ? 'RAND()' : "`ed`.`value` $order");
		}


		public function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation = false) {
			$field_id = $this->get('id');
			$value = $this->cleanValue($data[0]);
			$this->_key++;
			$joins .= "
				LEFT JOIN
					`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
					ON (e.id = t{$field_id}_{$this->_key}.entry_id)
			";
			$where .= "
				AND t{$field_id}_{$this->_key}.value = '{$value}'
			";
			
			return true;
		}

		function displayDatasourceFilterPanel(&$wrapper, $data=NULL, $errors=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL){
			
			parent::displayDatasourceFilterPanel($wrapper, $data, $errors, $fieldnamePrefix, $fieldnamePostfix);

			$existing_options = array('yes', 'no');

			if(is_array($existing_options) && !empty($existing_options)){
				$optionlist = new XMLElement('ul');
				$optionlist->setAttribute('class', 'tags');
				
				foreach($existing_options as $option) $optionlist->appendChild(new XMLElement('li', $option));
						
				$wrapper->appendChild($optionlist);
			}
					
		}

		function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL){
			
			if(!$data){
				## TODO: Don't rely on $_POST
				if(isset($_POST) && !empty($_POST)) $value = 'no';
				elseif($this->get('default_state') == 'on') $value = 'yes';
				else $value = 'no';
			}
			
			else $value = ($data['value'] == 'yes' ? 'yes' : 'no');
						
			$label = Widget::Label();
			$input = Widget::Input('fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix, 'yes', 'checkbox', ($value == 'yes' ? array('checked' => 'checked') : NULL));

			$label->setValue($input->generate(false) . ' ' . ($this->get('description') != NULL ? $this->get('description') : $this->get('label')));

			$wrapper->appendChild($label);			
		}
		
		function prepareTableValue($data, XMLElement $link=NULL){
			return ($data['value'] == 'yes' ? __('Yes') : __('No'));
		}

		function isSortable(){
			return true;
		}
		
		function commit(){
			
			if(!parent::commit()) return false;
			
			$id = $this->get('id');

			if($id === false) return false;
			
			$fields = array();
			
			$fields['field_id'] = $id;
			$fields['default_state'] = ($this->get('default_state') ? $this->get('default_state') : 'off');
			if(trim($this->get('description')) != '') $fields['description'] = $this->get('description');
			
			$this->_engine->Database->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '$id' LIMIT 1");		
			return $this->_engine->Database->insert($fields, 'tbl_fields_' . $this->handle());
					
		}	
		
		function findDefaults(&$fields){
			if(!isset($fields['default_state'])) $fields['default_state'] = 'off';
		}
				
		function displaySettingsPanel(&$wrapper){
			
			parent::displaySettingsPanel($wrapper);
			
			## Long Description		
			$label = Widget::Label(__('Long Description <i>Optional</i>'));
			$label->appendChild(Widget::Input('fields['.$this->get('sortorder').'][description]', $this->get('description')));
			$wrapper->appendChild($label);			
		
			## Checkbox Default State
			$label = Widget::Label();
			$input = Widget::Input('fields['.$this->get('sortorder').'][default_state]', 'on', 'checkbox');
			if($this->get('default_state') == 'on') $input->setAttribute('checked', 'checked');			
			$label->setValue(__('%s Checked by default', array($input->generate())));
			$wrapper->appendChild($label);

			$this->appendShowColumnCheckbox($wrapper);			
		}
		
		function createTable(){
			
			return $this->_engine->Database->query(
			
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `entry_id` int(11) unsigned NOT NULL,
				  `value` enum('yes','no') NOT NULL default '".($this->get('default_state') == 'on' ? 'yes' : 'no')."',
				  PRIMARY KEY  (`id`),
				  KEY `entry_id` (`entry_id`),
				  KEY `value` (`value`)
				) TYPE=MyISAM;"
			
			);
		}		

		public function getExampleFormMarkup(){
			$label = Widget::Label($this->get('label'));
			$label->appendChild(Widget::Input('fields['.$this->get('element_name').']', NULL, 'checkbox', ($this->get('default_state') == 'on' ? array('checked' => 'checked') : NULL)));
			
			return $label;
		}

	}

