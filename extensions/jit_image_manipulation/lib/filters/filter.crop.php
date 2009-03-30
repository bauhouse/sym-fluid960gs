<?php
	
	require_once(realpath(dirname(__FILE__).'/../') . '/class.imagefilter.php');
	
	Class FilterCrop extends ImageFilter{

		const TOP_LEFT = 1;
		const TOP_MIDDLE = 2;
		const TOP_RIGHT = 3;
		const MIDDLE_LEFT = 4;
		const CENTER = 5;
		const MIDDLE_RIGHT = 6;
		const BOTTOM_LEFT = 7;
		const BOTTOM_MIDDLE = 8;
		const BOTTOM_RIGHT = 9;
	
		public static function run($res, $width, $height, $anchor=self::TOP_LEFT, $background_fill='fff'){

			$dst_w = Image::width($res);
			$dst_h = Image::height($res);

			if(!empty($width) && !empty($height)) {
				$dst_w = $width;
				$dst_h = $height;
			} 

			elseif(empty($height)) {
				$ratio = ($dst_h / $dst_w);
				$dst_w = $width;
				$dst_h = round($dst_w * $ratio);

			} 

			elseif(empty($width)) {
				$ratio = ($dst_w / $dst_h);
				$dst_h = $height;
				$dst_w = round($dst_h * $ratio);
			}

			$tmp = imagecreatetruecolor($dst_w, $dst_h);
			self::__fill($tmp, $background_fill);

			list($src_x, $src_y, $dst_x, $dst_y) = self::__calculateDestSrcXY($dst_w, $dst_h, Image::width($res), Image::height($res), Image::width($res), Image::height($res), $anchor);

			imagecopyresampled($tmp, $res, $src_x, $src_y, $dst_x, $dst_y, Image::width($res), Image::height($res), Image::width($res), Image::height($res));
			
			@imagedestroy($res);
			
			return $tmp;

//			self::__copy($tmp, $res, true);
		}

		protected static function __calculateDestSrcXY($width, $height, $src_w, $src_h, $dst_w, $dst_h, $position=self::TOP_LEFT){

			$dst_x = $dst_y = 0;
			$src_x = $src_y = 0;

			if($width < $src_w){
				$mx = array(				
					0,
					ceil(($src_w * 0.5) - ($width * 0.5)),
					$src_x = $src_w - $width				
				);
			}

			else{
				$mx = array(				
					0,
					ceil(($width * 0.5) - ($src_w * 0.5)),
					$src_x = $width - $src_w			
				);				
			}

			if($height < $src_h){
				$my = array(				
					0,
					ceil(($src_h * 0.5) - ($height * 0.5)),
					$src_y = $src_h - $height				
				);
			}

			else{
	
				$my = array(				
					0,
					ceil(($height * 0.5) - ($src_h * 0.5)),
					$src_y = $height - $src_h				
				);
			}

			switch($position){
		
				case 1:
					break;
								
				case 2:		
					$src_x = 1;
					break;
		
				case 3:
					$src_x = 2; 
					break;
		
				case 4:
					$src_y = 1;
					break;
							
				case 5:
					$src_x = 1;
					$src_y = 1;
					break;
		
				case 6:
					$src_x = 2;
					$src_y = 1;
					break;

				case 7:
					$src_y = 2;
					break;
		
				case 8:
					$src_x = 1;
					$src_y = 2;
					break;	
															
				case 9:
					$src_x = 2;
					$src_y = 2;
					break;
						
			}

			$a = ($width >= $dst_w ? $mx[$src_x] : 0);
			$b = ($height >= $dst_h ? $my[$src_y] : 0);
			$c = ($width < $dst_w ? $mx[$src_x] : 0);
			$d = ($height < $dst_h ? $my[$src_y] : 0);

			return array($a, $b, $c, $d);		
		}
	}