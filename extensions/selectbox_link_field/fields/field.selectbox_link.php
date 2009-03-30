<?php

	if(!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');
	
	Class fieldSelectBox_Link extends Field{
		
		function __construct(&$parent){
			parent::__construct($parent);
			$this->_name = 'Select Box Link';
			$this->_required = true;
			
	    	// Set default
			$this->set('show_column', 'no'); 
			$this->set('required', 'yes');
			$this->set('limit', 20);
		}

		function canToggle(){
			return ($this->get('allow_multiple_selection') == 'yes' ? false : true);
		}

		function canFilter(){
			return true;
		}

		function allowDatasourceOutputGrouping(){
			return true;
		}
		
		function allowDatasourceParamOutput(){
			return true;
		}

		public function getParameterPoolValue($data){
			return $data['relation_id'];
		}		

		public function set($field, $value){
			if($field == 'related_field_id' && !is_array($value)){
				$value = explode(',', $value);
			}
			$this->_fields[$field] = $value;
		}
		
		public function setArray($array){
			if(empty($array) || !is_array($array)) return;
			foreach($array as $field => $value) $this->set($field, $value);
		}		

		function groupRecords($records){

			if(!is_array($records) || empty($records)) return;

			$groups = array($this->get('element_name') => array());

			foreach($records as $r){
				$data = $r->getData($this->get('id'));

				$value = $data['relation_id'];

				$primary_field = $this->__findPrimaryFieldValueFromRelationID($data['relation_id']);

				if(!isset($groups[$this->get('element_name')][$value])){
					$groups[$this->get('element_name')][$value] = array('attr' => array('link-id' => $data['relation_id'], 'link-handle' => Lang::createHandle($primary_field['value'])),
																		'records' => array(), 'groups' => array());	
				}	

				$groups[$this->get('element_name')][$value]['records'][] = $r;

			}

			return $groups;

		}

		function prepareTableValue($data, XMLElement $link=NULL){
			$result = array();
			
			if(!is_array($data) || (is_array($data) && !isset($data['relation_id']))) return parent::prepareTableValue(NULL);
			
			if(!is_array($data['relation_id'])){
				$data['relation_id'] = array($data['relation_id']);
			}
				
			foreach($data['relation_id'] as $relation_id){
				if((int)$relation_id <= 0) continue;
				
				$primary_field = $this->__findPrimaryFieldValueFromRelationID($relation_id);
				
				if(!is_array($primary_field) || empty($primary_field)){
					continue;
				}
				
				$result[$relation_id] = $primary_field;
				
			}
			
			if(!is_null($link)){
				$label = NULL;
				foreach($result as $item){
					$label .= ' ' . $item['value'];
				}
				$link->setValue(General::sanitize(trim($label)));
				return $link->generate();
			}
			
			$output = NULL;

			foreach($result as $relation_id => $item){
				$link = Widget::Anchor($item['value'], sprintf('%s/symphony/publish/%s/edit/%d/', URL, $item['section_handle'], $relation_id));
					
				$output .= $link->generate() . ' ';
			}
			
			return trim($output);
		}

		private function __findPrimaryFieldValueFromRelationID($entry_id){
			
			$field_id = $this->findFieldIDFromRelationID($entry_id);
			
			$primary_field = $this->Database->fetchRow(0,

				"SELECT `f`.*, `s`.handle AS `section_handle`
				 FROM `tbl_fields` AS `f`
				 INNER JOIN `tbl_sections` AS `s` ON `s`.id = `f`.parent_section
				 WHERE `f`.id = '{$field_id}'
				 ORDER BY `f`.sortorder ASC 
				 LIMIT 1"

			);

			if(!$primary_field) return NULL;

			$field = $this->_Parent->create($primary_field['type']);

			$data = $this->Database->fetchRow(0, 
				"SELECT *
				 FROM `tbl_entries_data_{$field_id}`
				 WHERE `entry_id` = '{$entry_id}' ORDER BY `id` DESC LIMIT 1"
			);

			if(empty($data)) return NULL;

			$primary_field['value'] = $field->prepareTableValue($data);	

			return $primary_field;

		}

		function processRawFieldData($data, &$status, $simulate=false, $entry_id=NULL){

			$status = self::__OK__;
			if(!is_array($data)) return array('relation_id' => $data);

			if(empty($data)) return NULL;
		
			$result = array();

			foreach($data as $a => $value) { 
			  $result['relation_id'][] = $data[$a];
			}
		
			return $result;

		}

		function fetchAssociatedEntrySearchValue($data, $field_id=NULL, $parent_entry_id=NULL){
			
			// We dont care about $data, but instead $parent_entry_id
			if(!is_null($parent_entry_id)) return $parent_entry_id;
			
			if(!is_array($data)) return $data;

			$searchvalue = $this->_engine->Database->fetchRow(0, 
				sprintf("
					SELECT `entry_id` FROM `tbl_entries_data_%d` 
					WHERE `handle` = '%s' 
					LIMIT 1", $field_id, addslashes($data['handle']))
			);

			return $searchvalue['entry_id'];
		}

		function fetchAssociatedEntryCount($value){
			return $this->_engine->Database->fetchVar('count', 0, "SELECT count(*) AS `count` FROM `tbl_entries_data_".$this->get('id')."` WHERE `relation_id` = '$value'");
		}

		function fetchAssociatedEntryIDs($value){
			return $this->_engine->Database->fetchCol('entry_id', "SELECT `entry_id` FROM `tbl_entries_data_".$this->get('id')."` WHERE `relation_id` = '$value'");
		}		

		public function appendFormattedElement(&$wrapper, $data, $encode = false) {
			if (!is_array($data) || empty($data)) return;
			
			$list = new XMLElement($this->get('element_name'));
			
			if (!is_array($data['relation_id'])) {
				$data['relation_id'] = array($data['relation_id']);
			}
			
			foreach ($data['relation_id'] as $relation_id) {
				$field = $this->__findPrimaryFieldValueFromRelationID($relation_id);    
				$section = $this->_engine->Database->fetchRow(0, "SELECT `id`, `handle` FROM `tbl_sections` WHERE `id` = '{$field['parent_section']}' LIMIT 1");
				$handle = Lang::createHandle($field['value']);
				$value = General::sanitize($field['value']);
				
				if ($encode) $item_value = General::sanitize($value);
				
				$list->appendChild(new XMLElement('item', $value, array(
					'handle'	=> $handle,
					'id'		=> $relation_id
				)));
			}
			
			$wrapper->appendChild($list);
		}
		
		public function findFieldIDFromRelationID($id){
			
			## Figure out the section
			$section_id = $this->Database->fetchVar('section_id', 0, "SELECT `section_id` FROM `tbl_entries` WHERE `id` = {$id} LIMIT 1");
			
			## Figure out which related_field_id is from that section
			$field_id = $this->Database->fetchVar('field_id', 0, "SELECT f.`id` AS `field_id`
				FROM `tbl_fields` AS `f` 
				LEFT JOIN `tbl_sections` AS `s` ON f.parent_section = s.id
				WHERE `s`.id = {$section_id} AND f.id IN ('".implode("', '", $this->get('related_field_id'))."') LIMIT 1");
				
			return $field_id;
			
		}
		
		public function findOptions(array $existing_selection=NULL){

			$values = array();
			$limit = $this->get('limit');

			foreach($this->get('related_field_id') as $field_id){
				
				$section = $this->Database->fetchRow(0, "SELECT s.name, s.id
					 									FROM `tbl_sections` AS `s` 
														LEFT JOIN `tbl_fields` AS `f` ON `s`.id = `f`.parent_section
														WHERE `f`.id = '{$field_id}'
														LIMIT 1");
				
				$group = array('name' => $section['name'], 'section' => $section['id'], 'values' => array());
				
				$sql = "SELECT DISTINCT `entry_id` 
						FROM `tbl_entries_data_{$field_id}`
						ORDER BY `entry_id` DESC
						LIMIT 0, {$limit}";

				$results = $this->Database->fetchCol('entry_id', $sql);
	
				if(!is_null($existing_selection) && !empty($existing_selection)){
					foreach($existing_selection as $key => $entry_id){
						$x = $this->findFieldIDFromRelationID($entry_id);

						if($x == $field_id){
							$results[] = $entry_id;
							//unset($existing_selection[$key]);
						}
					}
				}
	
				rsort($results);
	
				if(is_array($results) && !empty($results)){
					foreach($results as $entry_id){
						$value = $this->__findPrimaryFieldValueFromRelationID($entry_id);
						$group['values'][$entry_id] = $value['value'];
					}
				}

				$values[] = $group;
			}

			return $values;

		}		

		function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL){
	
			if(!is_array($data['relation_id'])){
				$entry_ids = array($data['relation_id']);
			}
			
			else{
				$entry_ids = array_values($data['relation_id']);
			}
			
			$states = $this->findOptions($entry_ids);
			
			$options = array();
			
			if($this->get('required') != 'yes') $options[] = array(NULL, false, NULL);
			
			if(!empty($states)){
				foreach($states as $s){
					$group = array('label' => $s['name'], 'options' => array());
					foreach($s['values'] as $id => $v){
						$group['options'][] = array($id, in_array($id, $entry_ids), $v);
					}
					$options[] = $group;
				}
			}

			$fieldname = 'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix;
			if($this->get('allow_multiple_selection') == 'yes') $fieldname .= '[]';
		
			$label = Widget::Label($this->get('label'));
			$label->appendChild(Widget::Select($fieldname, $options, ($this->get('allow_multiple_selection') == 'yes' ? array('multiple' => 'multiple') : NULL)));
		
			if($flagWithError != NULL) $wrapper->appendChild(Widget::wrapFormElementWithError($label, $flagWithError));
			else $wrapper->appendChild($label); 
		}
		
		function commit(){

			if(!parent::commit()) return false;
		
			$id = $this->get('id');

			if($id === false) return false;

			$fields = array();

			$fields['field_id'] = $id;
			if($this->get('related_field_id') != '') $fields['related_field_id'] = $this->get('related_field_id');
			$fields['allow_multiple_selection'] = ($this->get('allow_multiple_selection') ? $this->get('allow_multiple_selection') : 'no');
			$fields['limit'] = max(1, (int)$this->get('limit'));
			$fields['related_field_id'] = implode(',', $this->get('related_field_id'));
			
			$this->Database->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '$id'");

			if(!$this->Database->insert($fields, 'tbl_fields_' . $this->handle())) return false;

			//$sections = $this->get('related_field_id');

			$this->removeSectionAssociation($id);

			//$section_id = $this->Database->fetchVar('parent_section', 0, "SELECT `parent_section` FROM `tbl_fields` WHERE `id` = '".$fields['related_field_id']."' LIMIT 1");
			
			foreach($this->get('related_field_id') as $field_id){
				$this->createSectionAssociation(NULL, $id, $field_id);
			}

			return true;
					
		}

		function buildSortingSQL(&$joins, &$where, &$sort, $order='ASC'){
			$joins .= "INNER JOIN `tbl_entries_data_".$this->get('id')."` AS `ed` ON (`e`.`id` = `ed`.`entry_id`) ";
			$sort = 'ORDER BY ' . (in_array(strtolower($order), array('random', 'rand')) ? 'RAND()' : "`ed`.`relation_id` $order");
		}

		function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation=false){

			$field_id = $this->get('id');

			if($andOperation):

				foreach($data as $key => $bit){
					$joins .= " LEFT JOIN `tbl_entries_data_$field_id` AS `t$field_id$key` ON (`e`.`id` = `t$field_id$key`.entry_id) ";
					$where .= " AND `t$field_id$key`.relation_id = '$bit' ";
				}

			else:

				$joins .= " LEFT JOIN `tbl_entries_data_$field_id` AS `t$field_id` ON (`e`.`id` = `t$field_id`.entry_id) ";
				$where .= " AND `t$field_id`.relation_id IN ('".@implode("', '", $data)."') ";

			endif;

			return true;

		}

		function findDefaults(&$fields){
			if(!isset($fields['allow_multiple_selection'])) $fields['allow_multiple_selection'] = 'no';
		}

		function displaySettingsPanel(&$wrapper, $errors=NULL){		

			parent::displaySettingsPanel($wrapper, $errors);

			$div = new XMLElement('div', NULL, array('class' => 'group'));
			
			$label = Widget::Label('Options');
			
			$sectionManager = new SectionManager($this->_engine);
		  	$sections = $sectionManager->fetch(NULL, 'ASC', 'name');
			$field_groups = array();
			
			if(is_array($sections) && !empty($sections)){
				foreach($sections as $section) $field_groups[$section->get('id')] = array('fields' => $section->fetchFields(), 'section' => $section);
			}
			
			$options = array();
			
			foreach($field_groups as $group){
				
				if(!is_array($group['fields'])) continue;
				
				$fields = array();
				foreach($group['fields'] as $f){
					if($f->get('id') != $this->get('id') && $f->canPrePopulate()){
						$fields[] = array($f->get('id'), @in_array($f->get('id'), $this->get('related_field_id')), $f->get('label'));
					}
				}
				
				if(is_array($fields) && !empty($fields)) $options[] = array('label' => $group['section']->get('name'), 'options' => $fields);
			}

			$label->appendChild(Widget::Select('fields['.$this->get('sortorder').'][related_field_id][]', $options, array('multiple' => 'multiple')));
			
			$div->appendChild($label);
						
			if(isset($errors['related_field_id'])) $wrapper->appendChild(Widget::wrapFormElementWithError($div, $errors['related_field_id']));
			else $wrapper->appendChild($div);
				
			## Maximum entries
			$label = Widget::Label();
			$input = Widget::Input('fields['.$this->get('sortorder').'][limit]', $this->get('limit'));
			$input->setAttribute('size', '3');
			$label->setValue('Limit to the ' . $input->generate() . ' most recent entries');
			$wrapper->appendChild($label);
						
			## Allow selection of multiple items
			$label = Widget::Label();
			$input = Widget::Input('fields['.$this->get('sortorder').'][allow_multiple_selection]', 'yes', 'checkbox');
			if($this->get('allow_multiple_selection') == 'yes') $input->setAttribute('checked', 'checked');			
			$label->setValue($input->generate() . ' Allow selection of multiple options');
			$wrapper->appendChild($label);
			
			$this->appendShowColumnCheckbox($wrapper);
			$this->appendRequiredCheckbox($wrapper);
						
		}


		function createTable(){

			return $this->_engine->Database->query(

				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
				`id` int(11) unsigned NOT NULL auto_increment,
				`entry_id` int(11) unsigned NOT NULL,
				`relation_id` int(11) unsigned NOT NULL,
				PRIMARY KEY  (`id`),
				KEY `entry_id` (`entry_id`),
				KEY `relation_id` (`relation_id`)
				) TYPE=MyISAM;"

			);
		}

		public function getExampleFormMarkup(){
			return Widget::Input('fields['.$this->get('element_name').']', '...', 'hidden');
		}			

	}
