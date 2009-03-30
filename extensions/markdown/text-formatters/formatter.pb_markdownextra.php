<?php

	Class formatterpb_markdownextra extends TextFormatter{

		private static $_parser;

		function about(){
			return array(
						 'name' => 'Markdown Extra Text Formatter',
						 'version' => '1.4',
						 'release-date' => '2009-03-13',
						 'author' => array('name' => 'Alistair Kearney',
										   'website' => 'http://www.pointybeard.com',
										   'email' => 'alistair@pointybeard.com'),
						 'description' => 'Write entries in the Markdown Extra format. Wrapper for the PHP Markdown Extra text-to-HTML conversion tool written by Michel Fortin.'
				 		);
		}
				
		function run($string){
			if(!self::$_parser){
				include_once(EXTENSIONS . '/markdown/lib/markdown.php');
				self::$_parser = new MarkdownExtra_Parser();
			}
			return stripslashes(self::$_parser->transform($string));
		}		
		
	}
	
?>