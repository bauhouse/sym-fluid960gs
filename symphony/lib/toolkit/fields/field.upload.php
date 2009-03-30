<?php
	
	Class fieldUpload extends Field {
		public function __construct(&$parent){
			parent::__construct($parent);
			
			$this->_name = __('File Upload');
			$this->_required = true;
			
			$this->set('required', 'yes');
		}
		
		public function canFilter() {
			return true;
		}
		
		public function canImport(){
			return true;
		}
		
		public function buildSortingSQL(&$joins, &$where, &$sort, $order='ASC'){
		    $joins .= "INNER JOIN `tbl_entries_data_".$this->get('id')."` AS `ed` ON (`e`.`id` = `ed`.`entry_id`) ";
		    $sort = 'ORDER BY ' . (in_array(strtolower($order), array('random', 'rand')) ? 'RAND()' : "`ed`.`file` $order");
		}
		
		public function buildDSRetrivalSQL($data, &$joins, &$where, $andOperation = false) {
			$field_id = $this->get('id');
			
			if (preg_match('/^mimetype:/', $data[0])) {
				$data[0] = str_replace('mimetype:', '', $data[0]);
				$column = 'mimetype';
				
			} else if (preg_match('/^size:/', $data[0])) {
				$data[0] = str_replace('size:', '', $data[0]);
				$column = 'size';
				
			} else {
				$column = 'file';
			}
			
			if (self::isFilterRegex($data[0])) {
				$this->_key++;
				$pattern = str_replace('regexp:', '', $this->cleanValue($data[0]));
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND t{$field_id}_{$this->_key}.{$column} REGEXP '{$pattern}'
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
						AND t{$field_id}_{$this->_key}.{$column} = '{$value}'
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
					AND t{$field_id}_{$this->_key}.{$column} IN ('{$data}')
				";
			}
			
			return true;
		}
		
		public function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL){

			if(!$flagWithError && !is_writable(DOCROOT . $this->get('destination') . '/')) 
				$flagWithError = __('Destination folder, <code>%s</code>, is not writable. Please check permissions.', array($this->get('destination')));
			
			$label = Widget::Label($this->get('label'));
			$class = 'file';
			$label->setAttribute('class', $class);
			if($this->get('required') != 'yes') $label->appendChild(new XMLElement('i', __('Optional')));
			
			$span = new XMLElement('span');
			if($data['file']) $span->appendChild(Widget::Anchor('/workspace' . $data['file'], URL . '/workspace' . $data['file']));
			
			$span->appendChild(Widget::Input('fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix, $data['file'], ($data['file'] ? 'hidden' : 'file')));
			
			$label->appendChild($span);
			
			if($flagWithError != NULL) $wrapper->appendChild(Widget::wrapFormElementWithError($label, $flagWithError));
			else $wrapper->appendChild($label);

		}
		
		function isSortable(){
			return true;
		}
		
		public function entryDataCleanup($entry_id, $data){
			$file_location = WORKSPACE . '/' . ltrim($data['file'], '/');
			
			if(file_exists($file_location)) General::deleteFile($file_location);
			
			parent::entryDataCleanup($entry_id);
			
			return true;
		}		

		public function checkFields(&$errors, $checkForDuplicates=true){
			
			if(!is_writable(DOCROOT . $this->get('destination') . '/'))
				$errors['destination'] = __('Folder is not writable. Please check permissions.');
			
			parent::checkFields($errors, $checkForDuplicates);
		}
		
		function commit(){
			
			if(!parent::commit()) return false;
			
			$id = $this->get('id');

			if($id === false) return false;
			
			$fields = array();
			
			$fields['field_id'] = $id;
			$fields['destination'] = $this->get('destination');
			$fields['validator'] = ($fields['validator'] == 'custom' ? NULL : $this->get('validator'));
			
			$this->_engine->Database->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '$id' LIMIT 1");		
			return $this->_engine->Database->insert($fields, 'tbl_fields_' . $this->handle());
					
		}		
		
		function prepareTableValue($data, XMLElement $link=NULL){
			if(!$file = $data['file']) return NULL;
					
			if($link){
				$link->setValue(basename($file));
				//$view_link = Widget::Anchor('(view)', URL . '/workspace' . $file);
				return $link->generate(); // . ' ' . $view_link->generate();
			}
			
			else{
				$link = Widget::Anchor(basename($file), URL . '/workspace' . $file);
				return $link->generate();
			}
			
		}

		function appendFormattedElement(&$wrapper, $data){
			$item = new XMLElement($this->get('element_name'));
			
			$item->setAttributeArray(array(
				'size' => General::formatFilesize(filesize(WORKSPACE . $data['file'])),
			 	'path' => str_replace(WORKSPACE, NULL, dirname(WORKSPACE . $data['file'])),
				'type' => $data['mimetype'],
			));
			
			$item->appendChild(new XMLElement('filename', General::sanitize(basename($data['file']))));
						
			$m = unserialize($data['meta']);
			
			if(is_array($m) && !empty($m)){
				$item->appendChild(new XMLElement('meta', NULL, $m));
			}
					
			$wrapper->appendChild($item);
		}
		
		function displaySettingsPanel(&$wrapper, $errors=NULL){
			
			parent::displaySettingsPanel($wrapper, $errors);

			## Destination Folder
			$ignore = array('events', 'data-sources', 'text-formatters', 'pages', 'utilities');
			$directories = General::listDirStructure(WORKSPACE, true, 'asc', DOCROOT, $ignore);	   	
	
			$label = Widget::Label(__('Destination Directory'));

			$options = array();
			$options[] = array('/workspace', false, '/workspace');
			if(!empty($directories) && is_array($directories)){
				foreach($directories as $d) {
					$d = '/' . trim($d, '/');
					if(!in_array($d, $ignore)) $options[] = array($d, ($this->get('destination') == $d), $d);
				}	
			}

			$label->appendChild(Widget::Select('fields['.$this->get('sortorder').'][destination]', $options));
				
			if(isset($errors['destination'])) $wrapper->appendChild(Widget::wrapFormElementWithError($label, $errors['destination']));
			else $wrapper->appendChild($label);
			
			$this->buildValidationSelect($wrapper, $this->get('validator'), 'fields['.$this->get('sortorder').'][validator]', 'upload');
			
			$this->appendRequiredCheckbox($wrapper);
			$this->appendShowColumnCheckbox($wrapper);
			
		}
		
		function checkPostFieldData($data, &$message, $entry_id=NULL){
			
			/*
				UPLOAD_ERR_OK
				Value: 0; There is no error, the file uploaded with success.

				UPLOAD_ERR_INI_SIZE
				Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini.

				UPLOAD_ERR_FORM_SIZE
				Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.

				UPLOAD_ERR_PARTIAL
				Value: 3; The uploaded file was only partially uploaded.

				UPLOAD_ERR_NO_FILE
				Value: 4; No file was uploaded.

				UPLOAD_ERR_NO_TMP_DIR
				Value: 6; Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.

				UPLOAD_ERR_CANT_WRITE
				Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0.

				UPLOAD_ERR_EXTENSION
				Value: 8; File upload stopped by extension. Introduced in PHP 5.2.0.
			*/

		//	Array
		//	(
		//	    [name] => filename.pdf
		//	    [type] => application/pdf
		//	    [tmp_name] => /tmp/php/phpYtdlCl
		//	    [error] => 0
		//	    [size] => 16214
		//	)

			$message = NULL;
			
			if(empty($data) || $data['error'] == UPLOAD_ERR_NO_FILE) {
				
				if($this->get('required') == 'yes'){
					$message = __("'%s' is a required field.", $this->get('label'));
					return self::__MISSING_FIELDS__;		
				}
				
				return self::__OK__;
			}
				
			## Its not an array, so just retain the current data and return
			if(!is_array($data)) return self::__OK__;

			if(!is_writable(DOCROOT . $this->get('destination') . '/')){
				$message = __('Destination folder, <code>%s</code>, is not writable. Please check permissions.', array($this->get('destination')));
				return self::__ERROR__;
			}

			if($data['error'] != UPLOAD_ERR_NO_FILE && $data['error'] != UPLOAD_ERR_OK){
				
				switch($data['error']){

					case UPLOAD_ERR_INI_SIZE:
						$message = __('File chosen in "%1$s" exceeds the maximum allowed upload size of %2$s specified by your host.', array($this->get('label'), (is_numeric(ini_get('upload_max_filesize')) ? General::formatFilesize(ini_get('upload_max_filesize')) : ini_get('upload_max_filesize'))));
						break;
						
					case UPLOAD_ERR_FORM_SIZE:
						$message = __('File chosen in "%1$s" exceeds the maximum allowed upload size of %2$s, specified by Symphony.', array($this->get('label'), General::formatFilesize($this->_engine->Configuration->get('max_upload_size', 'admin'))));
						break;

					case UPLOAD_ERR_PARTIAL:
						$message = __("File chosen in '%s' was only partially uploaded due to an error.", array($this->get('label')));
						break;

					case UPLOAD_ERR_NO_TMP_DIR:
						$message = __("File chosen in '%s' was only partially uploaded due to an error.", array($this->get('label')));
						break;

					case UPLOAD_ERR_CANT_WRITE:
						$message = __("Uploading '%s' failed. Could not write temporary file to disk.", array($this->get('label')));
						break;

					case UPLOAD_ERR_EXTENSION:
						$message = __("Uploading '%s' failed. File upload stopped by extension.", array($this->get('label')));
						break;

				}
				
				return self::__ERROR_CUSTOM__;
				
			}

			## Sanitize the filename
			$data['name'] = Lang::createFilename($data['name']);
			
			if($this->get('validator') != NULL){
				$rule = $this->get('validator');
				
				if(!General::validateString($data['name'], $rule)){
					$message = __("File chosen in '%s' does not match allowable file types for that field.", array($this->get('label')));
					return self::__INVALID_FIELDS__;
				}
				
			}
			
			$abs_path = DOCROOT . '/' . trim($this->get('destination'), '/');
			$new_file = $abs_path . '/' . $data['name'];
			$existing_file = NULL;
			
			if($entry_id){
				$row = $this->Database->fetchRow(0, "SELECT * FROM `tbl_entries_data_".$this->get('id')."` WHERE `entry_id` = '$entry_id' LIMIT 1");
				$existing_file = $abs_path . '/' . trim($row['file'], '/');
			}
			
			if(($existing_file != $new_file) && file_exists($new_file)){
				$message = __('A file with the name %1$s already exists in %2$s. Please rename the file first, or choose another.', array($data['name'], $this->get('destination')));
				return self::__INVALID_FIELDS__;				
			}
			
			return self::__OK__;		
						
		}
		
		function processRawFieldData($data, &$status, $simulate=false, $entry_id=NULL){

			$status = self::__OK__;
			
			## Its not an array, so just retain the current data and return
			if(!is_array($data)){
				
				$status = self::__OK__;
				
				// Do a simple reconstruction of the file meta information. This is a workaround for
				// bug which causes all meta information to be dropped
				return array(
					'file' => $data,
					'mimetype' => self::__sniffMIMEType($data),
					'size' => filesize(WORKSPACE . $data),
					'meta' => serialize(self::getMetaInfo(WORKSPACE . $data, self::__sniffMIMEType($data)))
				);
	
			}

			if($simulate) return;
			
			if($data['error'] == UPLOAD_ERR_NO_FILE || $data['error'] != UPLOAD_ERR_OK) return;
			
			## Sanitize the filename
			$data['name'] = Lang::createFilename($data['name']);
			
			## Upload the new file
			$abs_path = DOCROOT . '/' . trim($this->get('destination'), '/');
			$rel_path = str_replace('/workspace', '', $this->get('destination'));

			if(!General::uploadFile($abs_path, $data['name'], $data['tmp_name'], $this->_engine->Configuration->get('write_mode', 'file'))){
				
				$message = __('There was an error while trying to upload the file <code>%1$s</code> to the target directory <code>%2$s</code>.', array($data['name'], 'workspace/'.$rel_path));
				$status = self::__ERROR_CUSTOM__;
				return;
			}

			if($entry_id){
				$row = $this->Database->fetchRow(0, "SELECT * FROM `tbl_entries_data_".$this->get('id')."` WHERE `entry_id` = '$entry_id' LIMIT 1");
				$existing_file = $abs_path . '/' . basename($row['file']);

				General::deleteFile($existing_file);
			}

			$status = self::__OK__;
			
			$file = rtrim($rel_path, '/') . '/' . trim($data['name'], '/');
			
			return array(
				'file' => $file,
				'size' => $data['size'],
				'mimetype' => $data['type'],
				'meta' => serialize(self::getMetaInfo(WORKSPACE . $file, $data['type']))
			);
			
		}
		
		private static function __sniffMIMEType($file){
			
			$imageMimeTypes = array(
				'image/gif',
				'image/jpg',
				'image/jpeg',
				'image/png',
			);
			
			if(in_array('image/' . General::getExtension($file), $imageMimeTypes)) return 'image/' . General::getExtension($file);
			
			return 'unknown';
		}
		
		public static function getMetaInfo($file, $type){

			$imageMimeTypes = array(
				'image/gif',
				'image/jpg',
				'image/jpeg',
				'image/png',
			);
			
			$meta = array();
			
			$meta['creation'] = DateTimeObj::get('c', filemtime($file));
			
			if(in_array($type, $imageMimeTypes) && $array = @getimagesize($file)){
				$meta['width']    = $array[0];
				$meta['height']   = $array[1];
			}
			
			return $meta;
			
		}
		

		function createTable(){
			
			return $this->_engine->Database->query(
			
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `entry_id` int(11) unsigned NOT NULL,
				  `file` varchar(255) default NULL,
				  `size` int(11) unsigned NOT NULL,
				  `mimetype` varchar(50) NOT NULL,
				  `meta` varchar(255) default NULL,
				  PRIMARY KEY  (`id`),
				  KEY `entry_id` (`entry_id`),
				  KEY `file` (`file`),
				  KEY `mimetype` (`mimetype`)
				) TYPE=MyISAM ;	"
			
			);
		}		

		public function getExampleFormMarkup(){
			$label = Widget::Label($this->get('label'));
			$label->appendChild(Widget::Input('fields['.$this->get('element_name').']', NULL, 'file'));
			
			return $label;
		}

	}

