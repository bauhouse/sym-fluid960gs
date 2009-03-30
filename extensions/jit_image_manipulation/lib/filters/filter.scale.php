<?php

	require_once(dirname(__FILE__) . '/filter.resize.php');
	

	Class FilterScale extends ImageFilter{
		public static function run($res, $percentage){

			$percentage = floatval(max(1.0, floatval($percentage)) * 0.01);

			$width = round(Image::height($res) * $percentage);

			return self::resize($res, $width, NULL);

		}
	}