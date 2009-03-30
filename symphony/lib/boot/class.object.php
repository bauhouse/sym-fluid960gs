<?php

	Class Object{
		
		protected $_ParentCatalogue;
		
	    function __construct() {	
			$this->_ParentCatalogue = array();
	    }

		// Special function for autodiscovery of parent objects and their type
		protected function catalogueParentObjects(){
			
			$ref = $this->_Parent;
			$classname = strtolower(get_class($ref));
			
			do{	
				$this->_ParentCatalogue[$classname] = $ref;
				$ref = $ref->_Parent;
				$lastClassname = $classname;
				$classname = strtolower(get_class($ref));	
								
			}while($lastClassname != $classname);

		}
						
	}
	
