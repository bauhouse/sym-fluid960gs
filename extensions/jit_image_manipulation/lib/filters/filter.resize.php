<?php

	require_once(realpath(dirname(__FILE__).'/../') . '/class.imagefilter.php');

	Class FilterResize extends ImageFilter{

		public static function run($res, $width=NULL, $height=NULL){

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

			$dst = imagecreatetruecolor($dst_w, $dst_h);
			
			//imagealphablending( $dst, false );
			//imagesavealpha( $dst, true );
			
			self::__fill($dst);

			imagecopyresampled($dst, $res, 0, 0, 0, 0, $dst_w, $dst_h, Image::width($res), Image::height($res));
			
			@imagedestroy($res);
			
			return $dst;
			
		}
	}