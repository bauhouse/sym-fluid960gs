<?php

	Class Image{
		
		private $_resource;
		private $_meta;
		
		const DEFAULT_QUALITY = 80;
		const DEFAULT_INTERLACE = true;
		//const DEFAULT_OUTPUT_TYPE = IMAGETYPE_JPEG;
		
		private function __construct($resource, stdClass $meta){
			$this->_resource = $resource;
			$this->_meta = $meta;
		}
		
		public function __destruct(){
			@imagedestroy($this->_resource);
		}
		
		public static function height($res){
			return @imagesy($res);
		}
		
		public static function width($res){
			return @imagesx($res);
		}
		
		public function Resource(){
			return $this->_resource;
		}
		
		public static function loadExternal($uri){
			
			if(function_exists('curl_init')){
				$ch = curl_init();
				
				curl_setopt($ch, CURLOPT_URL, $uri);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				
				$tmp = curl_exec($ch);
				curl_close($ch);			
			}
			
			else $tmp = @file_get_contents($uri);
			
			if($tmp === false){
				new Exception(__('Error reading external image <code>%s</code>. Please check the URI.', array($uri)));
			}
			
			$dest = @tempnam(@sys_get_temp_dir(), 'IMAGE');
			
			if(!@file_put_contents($dest, $tmp)) new Exception(__('Error writing to temporary file <code>%s</code>.', array($dest)));
			
			return self::load($dest);
			
		}
	
		public static function load($image){
			
			if(!is_file($image) || !is_readable($image)){
				throw new Exception(__('Error loading image <code>%s</code>. Check it exists and is readable.', array($image)));
			}
			
			$meta = self::getMetaInformation($image);
			
			switch($meta->type){
					
				## GIF
				case IMAGETYPE_GIF:
					$resource = @imagecreatefromgif($image);
					break;
					
				## JPEG
				case IMAGETYPE_JPEG: 
				
					if($meta->channels <= 3){ 
						$resource = @imagecreatefromjpeg($image);
					}
						
					## Can't handle CMYK JPEG files	
					else{
						throw new Exception(__('Cannot load CMYK JPG Images'));
					}
						
					break;
				
				## PNG
				case IMAGETYPE_PNG: 
					$resource = @imagecreatefrompng($image);
					break;
					
				default: 
					throw new Exception(__('Unsupported image type. Supported types: GIF, JPEG and PNG'));
					break;
			}			
	
			if(!is_resource($resource)){
				throw new Exception(__('Error loading image <code>%s</code>. Check it exists and is readable.', array($image)));
			}
		
			$obj = new self($resource, $meta);
		
			return $obj;
		}

		
		public function Meta(){
			return $this->_meta;
		}
		
		public static function getMetaInformation($file){
			if(!$array = @getimagesize($file)) return false;

			$meta = array();

			$meta['width']    = $array[0];
			$meta['height']   = $array[1];
			$meta['type']     = $array[2];
			$meta['channels'] = $array['channels'];
			
			return (object)$meta;
		}

		private function __render($dest, $quality, $output, $interlacing=false){
			
			if(!is_resource($this->_resource)) throw new Exception(__('Invalid image resource supplied'));
			
			## Turn interlacing on for JPEG or PNG only
			if($interlacing && ($output == IMAGETYPE_JPEG || $output == IMAGETYPE_PNG)){		
				imageinterlace($this->_resource);	
			}
			
			switch($output){
				
				case IMAGETYPE_GIF:
					return imagegif($this->_resource, $dest);
					break;
									
				case IMAGETYPE_PNG:
					return imagepng($this->_resource, $dest, round(9 * ($quality * 0.01)));
					break;	
					
				case IMAGETYPE_JPEG:
				default:
					return imagejpeg($this->_resource, $dest, $quality);
					break;				
			}
			
			return false;					
		}
		
		public function applyFilter($filter, array $args = array()){
			
			include_once("filters/filter.{$filter}.php");
			
			array_unshift($args, $this->_resource);
			
			$this->_resource = call_user_func_array(array(sprintf('Filter%s', ucfirst($filter)), 'run'), $args);
			
			return true;
		}
		
		public function display($quality=NULL, $interlacing=NULL, $output=NULL){
					
			if(!$quality) $quality = self::DEFAULT_QUALITY;
			if(!$interlacing) $interlacing = self::DEFAULT_INTERLACE;
			if(!$output) $output = $this->Meta()->type; //DEFAULT_OUTPUT_TYPE;
				
			self::renderOutputHeaders($output);
			return self::__render(NULL, $quality, $output, $interlacing);
		}
	
		public function save($dest, $quality=NULL, $interlacing=NULL, $output=NULL){
		
			if(!$quality) $quality = self::DEFAULT_QUALITY;
			if(!$interlacing) $interlacing = self::DEFAULT_INTERLACE;
			if(!$output) $output = $this->Meta()->type; //DEFAULT_OUTPUT_TYPE;
		
			return self::__render($dest, $quality, $output, $interlacing);
		}
	
		public static function renderOutputHeaders($output, $dest=NULL){

			header('Content-Type: ' . image_type_to_mime_type($output));

			if(!$dest) return;
				
			## Try to remove old extension
			$ext = strrchr($dest, '.');
			if($ext !== false){  	         
				$dest = substr($dest, 0, -strlen($ext));  	     
			}
			
			header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header("Content-Disposition: inline; filename=$dest" . image_type_to_extension($output));
		    header('Pragma: no-cache');			

		}
	}	

