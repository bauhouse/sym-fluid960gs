<?php

	Class extension_JIT_Image_Manipulation extends Extension{

		public function about(){
			return array('name' => 'JIT Image Manipulation',
						 'version' => '1.02',
						 'release-date' => '2009-03-06',
						 'author' => array('name' => 'Alistair Kearney',
										   'website' => 'http://pointybeard.com',
										   'email' => 'alistair@pointybeard.com')
				 		);
		}
		public function getSubscribedDelegates(){
			return array(
						array(
							'page' => '/system/preferences/',
							'delegate' => 'AddCustomPreferenceFieldsets',
							'callback' => 'appendPreferences'
						),
						
						array(
							'page' => '/system/preferences/',
							'delegate' => 'Save',
							'callback' => '__SavePreferences'
						),
			);
		}
		
		public function trusted(){
			return @file_get_contents(MANIFEST . '/jit-trusted-sites');
		}
		
		public function saveTrusted($string){
			return @file_put_contents(MANIFEST . '/jit-trusted-sites', $string);
		}		
		
		public function __SavePreferences($context){
			$this->saveTrusted(stripslashes($_POST['jit_image_manipulation']['trusted_external_sites']));
		}
		
		public function appendPreferences($context){

			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', 'JIT Image Manipulation'));			
			
			$label = Widget::Label('Trusted Sites');
			$label->appendChild(Widget::Textarea('jit_image_manipulation[trusted_external_sites]', 10, 50, $this->trusted()));
			
			$group->appendChild($label);
						
			$group->appendChild(new XMLElement('p', 'Leave empty to disable external linking. Single rule per line. Add * at end for wild card matching.', array('class' => 'help')));
									
			$context['wrapper']->appendChild($group);
						
		}
		
		public function enable(){
			return $this->install();			
		}
		
		public function disable(){
			$htaccess = @file_get_contents(DOCROOT . '/.htaccess');
			
			if($htaccess === false) return false;
			
			$htaccess = self::__removeImageRules($htaccess);
			$htaccess = preg_replace('/### IMAGE RULES/', NULL, $htaccess);
			
			return @file_put_contents(DOCROOT . '/.htaccess', $htaccess);
		}
		
		public function install(){
			
			$htaccess = @file_get_contents(DOCROOT . '/.htaccess');
			
			if($htaccess === false) return false;
			
			## Find out if the rewrite base is another other than /
			$rewrite_base = NULL;
			if(preg_match('/RewriteBase\s+([^\s]+)/i', $htaccess, $match)){
				$rewrite_base = trim($match[1], '/') . '/';
			}
			
			## Cannot use $1 in a preg_replace replacement string, so using a token instead
			$token = md5(time());
			
			$rule = "
	### IMAGE RULES	
	RewriteRule ^image\/(.+\.(jpg|gif|jpeg|png|bmp))\$ /{$rewrite_base}extensions/jit_image_manipulation/lib/image.php?param={$token} [L,NC]\n\n";
			
			## Remove existing the rules
			$htaccess = self::__removeImageRules($htaccess);
			
			if(preg_match('/### IMAGE RULES/', $htaccess)){
				$htaccess = preg_replace('/### IMAGE RULES/', $rule, $htaccess);
			}
			else{
				$htaccess = preg_replace('/RewriteRule .\* - \[S=14\]\s*/i', "RewriteRule .* - [S=14]\n{$rule}\t", $htaccess);
			}
			
			## Replace the token with the real value
			$htaccess = str_replace($token, '$1', $htaccess);

			return @file_put_contents(DOCROOT . '/.htaccess', $htaccess);

		}
		
		public function uninstall(){
			
			if(file_exists(MANIFEST . '/jit-trusted-sites')) unlink(MANIFEST . '/jit-trusted-sites');
			
			$htaccess = @file_get_contents(DOCROOT . '/.htaccess');
			
			if($htaccess === false) return false;
			
			$htaccess = self::__removeImageRules($htaccess);
			$htaccess = preg_replace('/### IMAGE RULES/', NULL, $htaccess);
			
			return @file_put_contents(DOCROOT . '/.htaccess', $htaccess);
		}
		
		private static function __removeImageRules($string){
			return preg_replace('/RewriteRule \^image[^\r\n]+[\r\n]?/i', NULL, $string);	
		}

	}