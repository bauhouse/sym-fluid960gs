<?php

	Class extension_selectbox_link_field extends Extension{
	
		public function about(){
			return array('name' => 'Field: Select Box Link',
						 'version' => '1.9',
						 'release-date' => '2009-03-03',
						 'author' => array('name' => 'Symphony Team',
										   'website' => 'http://www.symphony21.com',
										   'email' => 'team@symphony21.com')
				 		);
		}
		
		public function uninstall(){
			$this->_Parent->Database->query("DROP TABLE `tbl_fields_selectbox_link`");
		}
		
		public function update($previousVersion){
			
			if(version_compare($previousVersion, '1.6', '<')){

				$this->_Parent->Database->query("ALTER TABLE `tbl_fields_selectbox_link` ADD `limit` INT(4) UNSIGNED NOT NULL DEFAULT '20'");
			}

			$this->_Parent->Database->query("ALTER TABLE `tbl_fields_selectbox_link` CHANGE `related_field_id` `related_field_id` VARCHAR(255) NOT NULL");
			

			return true;
		}

		public function install(){

			return $this->_Parent->Database->query("CREATE TABLE `tbl_fields_selectbox_link` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `field_id` int(11) unsigned NOT NULL,
				  `allow_multiple_selection` enum('yes','no') NOT NULL default 'no',
				  `related_field_id` VARCHAR(255) NOT NULL,
				  `limit` int(4) unsigned NOT NULL default '20',
			  PRIMARY KEY  (`id`),
			  KEY `field_id` (`field_id`)
			)");

		}
			
	}

