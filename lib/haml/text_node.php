<?php

namespace hamlparser\lib\haml;

class TextNode extends Node {
	
	public static function parse_inline($content) {
		
		if(!$content)
			return $content;
		
		$parser = static::$parser_class;
		
		$php_wrapper = '<?php echo %s; ?>';
		
		$escape_html = $parser::options('escape_html');
		if($content[0] == '&') {
			$escape_html = true;
			$content = Helper::escape_html(trim(substr($content, 1)));
		} elseif($content[0] == '!')
			$escape_html = false;
		if($escape_html)
			$php_wrapper = '<?php echo \hamlparser\lib\haml\Helper::escape_html(%s); ?>';
		
		if($content[0] == '=')
			$content = sprintf($php_wrapper, trim(substr($content , 1)));
		
		$content = preg_replace(
			array('/^#{(.*?)}/', '/([^\\\\])#{(.*?)}/'),
			array(sprintf($php_wrapper, '$1'), '$1' . sprintf($php_wrapper, '$2')),
			$content
		);
		
		$content = preg_replace(
			array('/^\\\\#/', '/([^\\\\])\\\\#/'),
			array('#', '$1#'),
			$content
		);
		
		return $content;
		
	}
	
	public function parse() {
		
		$parser = static::$parser_class;
		
		if(strpos(strtolower($this->content), '!!! xml') === 0) {
			if($parser::options('format') != 'xhtml') {
				$this->content =  '';
				return;
			}
			
			$this->content = trim(substr($this->content, 7));
			if(empty($this->content))
				$this->content = 'utf-8';
			$this->content = sprintf(
				'<?php echo \'<?xml version=%s encoding=%s ?>\'; ?>',
				str_replace('\'', '\\\'', Helper::attribute('1.0')),
				str_replace('\'', '\\\'', Helper::attribute($this->content))
			);
			return;
		}
		
		if(strpos(strtolower($this->content), '!!!') === 0) {
			$this->content = trim(substr($this->content, 3));
			$this->content = $parser::doctype($this->content);
			return;
		}
		
		$this->content = static::parse_inline($this->content);
		
	}
	
}

?>