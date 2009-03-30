<?php

	Class formatterpb_markdownextrasmartypants extends TextFormatter{

		private static $_parser;

		function about(){
			return array(
						 'name' => 'Markdown Extra plus SmartyPants',
						 'version' => '1.4',
						 'release-date' => '2009-03-13',
						 'author' => array('name' => 'Alistair Kearney',
										   'website' => 'http://www.pointybeard.com',
										   'email' => 'alistair@pointybeard.com'),
						 'description' => 'Write entries in the Markdown Extra format. The final result is passed through SmartyPants. Wrapper for the PHP Markdown Extra and SmrtyPants text-to-HTML conversion tool written by Michel Fortin.'
				 		);
		}
				
		function run($string){
			if(!self::$_parser){
				include_once(EXTENSIONS . '/markdown/lib/markdown.php');
				self::$_parser = new MarkdownExtra_Parser();
			}

            if(!function_exists('SmartyPants')) include_once(EXTENSIONS . '/markdown/lib/smartypants.php');
            
			return SmartyPants(stripslashes(self::$_parser->transform($string)));
		}		
		
	}
	
?>