<?php

	Class Widget{
	
		function Label($name=NULL, XMLElement $child=NULL, $class=NULL, $id=NULL){
			$label = new XMLElement('label', ($name ? $name : NULL));
			
			if(is_object($child)) $label->appendChild($child);

			if($class) $label->setAttribute('class', $class);
			if($id) $label->setAttribute('id', $id);
			
			return $label;
		}

		function Input($name, $value=NULL, $type='text', $attributes=NULL){
			$obj = new XMLElement('input');		
			$obj->setAttribute('name', $name);
			
			if(is_array($attributes) && !empty($attributes)){
				foreach($attributes as $key => $val)
					$obj->setAttribute($key, $val);	
			}
			
			if($type) $obj->setAttribute('type', $type);
			
			if(strlen($value) != 0) $obj->setAttribute('value', $value);
			
			return $obj;
		}
		
		function Textarea($name, $rows, $cols, $value=NULL, $attributes=NULL){
			$obj = new XMLElement('textarea', $value);	
			
			$obj->setSelfClosingTag(false);
				
			$obj->setAttribute('name', $name);
			$obj->setAttribute('rows', $rows);
			$obj->setAttribute('cols', $cols);
			
			if(is_array($attributes) && !empty($attributes)){
				foreach($attributes as $key => $val)
					$obj->setAttribute($key, $val);	
			}
			
			return $obj;		
		}
		
		function Anchor($value, $href, $title=NULL, $class=NULL, $id=NULL){
			$a = new XMLElement('a', $value);
			$a->setAttribute('href', $href);
			
			if($title) $a->setAttribute('title', $title);
			if($class) $a->setAttribute('class', $class);
			if($id) $a->setAttribute('id', $id);
			
			return $a;		
		}
		
		function Form($action, $method, $class=NULL, $id=NULL, $attributes=NULL){
			$form = new XMLElement('form');
			$form->setAttribute('action', $action);
			$form->setAttribute('method', $method);
			
			if(is_array($attributes) && !empty($attributes)){
				foreach($attributes as $key => $val)
					$form->setAttribute($key, $val);	
			}			
			
			if($class) $form->setAttribute('class', $class);
			if($id) $form->setAttribute('id', $id);	
			
			return $form;		
		}
		
		###
		# Simple way to create generic Symphony table wrapper
		function Table($head=NULL, $foot=NULL, $body=NULL, $class=NULL, $id=NULL){
 			$table = new XMLElement('table');

			if($class) $table->setAttribute('class', $class);
			if($id) $table->setAttribute('id', $id);
			
			if($head) $table->appendChild($head);
			if($foot) $table->appendChild($foot);
			if($body) $table->appendChild($body);
			
			return $table;			
		}
		
		## Provided with an array of colum names, this will return an XML object
		function TableHead($array){
			
			$thead = new XMLElement('thead');
			$tr = new XMLElement('tr');
			
			foreach($array as $col){
				
				$th = new XMLElement('th');
				
				list($value, $scope, $attr) = $col;
				
				if(is_object($value)) $th->appendChild($value);
				else $th->setValue($value);
			
				if($scope && $scope != '') $th->setAttribute('scope', $scope);
				
				if(is_array($attr) && !empty($attr)) $th->setAttributeArray($attr);
				
				$tr->appendChild($th);
			}
			
			$thead->appendChild($tr);
			
			return $thead;
			
		}
		
		function TableBody($rows, $class=NULL, $id=NULL){
			$tbody = new XMLElement('tbody');
			
			if($class) $tbody->setAttribute('class', $class);
			if($id) $tbody->setAttribute('id', $id);
			
			foreach($rows as $r) $tbody->appendChild($r);
			
			return $tbody;			
		}
		
		function TableData($value, $class=NULL, $id=NULL, $colspan=NULL, array $attr=NULL){

			if(is_object($value)){
				$td = new XMLElement('td');
				$td->appendChild($value);
			}
			
			else $td = new XMLElement('td', $value);

			if($class) $td->setAttribute('class', $class);
			if($id) $td->setAttribute('id', $id);
			if($colspan) $td->setAttribute('colspan', $colspan);
			
			if($attr) $td->setAttributeArray($attr);		
						
			return $td;
		}
	
		function TableRow($data, $class=NULL, $id=NULL, $rowspan=NULL){
			$tr = new XMLElement('tr');

			if($class) $tr->setAttribute('class', $class);
			if($id) $tr->setAttribute('id', $id);
			if($rowspan) $tr->setAttribute('rowspan', $rowspan);
			
			foreach($data as $d) $tr->appendChild($d);
			
			return $tr;
		}
	
		function Select($name, $options, $attributes=NULL){
			$obj = new XMLElement('select');
			$obj->setAttribute('name', $name);	
			
			$obj->setSelfClosingTag(false);
			
			if(is_array($attributes) && !empty($attributes)){
				foreach($attributes as $key => $value)
					$obj->setAttribute($key, $value);	
			}
		
			if(!is_array($options) || empty($options)){
				if(!isset($attributes['disabled'])) $obj->setAttribute('disabled', 'disabled');
				
				//$option = new XMLElement('option', ' ');
				//$option->setAttribute('value', '');
				//$obj->appendChild($option);
				
				return $obj;
			}
		
			foreach($options as $o){
				
				## Opt Group
				if(isset($o['label'])){

					$optgroup = new XMLElement('optgroup');
					$optgroup->setAttribute('label', $o['label']);

					foreach($o['options'] as $opt){					
						$optgroup->appendChild(Widget::__SelectBuildOption($opt));		
					}
					
					$obj->appendChild($optgroup);
				}
				
				## Non-Opt group
				else $obj->appendChild(Widget::__SelectBuildOption($o));

			}

			return $obj;
		}
		
		function __SelectBuildOption($option){
			
			list($value, $selected, $desc, $class, $id, $attr) = $option;
			if(!$desc) $desc = $value;
			
			$obj = new XMLElement('option', "$desc");
			$obj->setSelfClosingTag(false);
			$obj->setAttribute('value', "$value");
			
			if(!empty($class)) $obj->setAttribute('class', $class);
			
			if(!empty($id)) $obj->setAttribute('id', $id);
			
			if(!empty($attr)) $obj->setAttributeArray($attr);
			
			if($selected) $obj->setAttribute('selected', 'selected');
			
			return $obj;
			
		}
		
		function wrapFormElementWithError($element, $message){
			$div = new XMLElement('div');
			$div->setAttributeArray(array('id' => 'error', 'class' => 'invalid'));
			
			$div->appendChild($element);
			$div->appendChild(new XMLElement('p', $message));
			
			return $div;
			
		}
		
	}

