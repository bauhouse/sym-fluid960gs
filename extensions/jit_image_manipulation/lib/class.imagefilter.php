<?php

	Abstract Class ImageFilter{
		
		const CHANNEL_RED = 0;
		const CHANNEL_GREEN = 1;
		const CHANNEL_BLUE = 2;		
		
		public static function colourChannelHex2Dec($colour, $channel){
			return hexdec(substr($colour, ($channel * 2), 2));
		}

		public static function expandColourString($colour){
			return (strlen($colour) == 3 ? $colour{0}.$colour{0}.$colour{1}.$colour{1}.$colour{2}.$colour{2} : $colour);
		}
		
		protected static function __fill(&$res, $colour='000'){
			if(strlen(trim($colour)) == 0) return;
			
			$colour = self::expandColourString($colour);
			
			$col_a = array(
				          'r' => self::colourChannelHex2Dec($colour, self::CHANNEL_RED),
						  'g' => self::colourChannelHex2Dec($colour, self::CHANNEL_GREEN),
						  'b' => self::colourChannelHex2Dec($colour, self::CHANNEL_BLUE),
						);
		
			imagefill($res, 0, 0, imagecolorallocate($res, $col_a['r'], $col_a['g'], $col_a['b']));
			
		}	
			
		protected static function __copy($src, &$dst, $resize=true){
			$w = imagesx($src);
			$h = imagesy($src);
					
			if($resize) $dst = imagecreatetruecolor($w, $h);
			imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
		
			return $dst;
		}
	
	}
