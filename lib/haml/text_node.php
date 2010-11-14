<?php

namespace hamlparser\lib\haml;

class TextNode extends Node {
	
	public function parse() {
		
		$parser = static::$parser_class;
		
		if(strpos(strtolower($this->content), '!!! xml') === 0) {
			if($parser::options('format') != 'xhtml') {
				$this->content =  '';
				return;
			}
			
			$this->content = trim(substr($this->content, 7));
			if(empty($this->content))
				$this->content = 'UTF-8';
			$this->content = '<?php echo \'<?xml version="1.0" encoding="' . $this->content . '" ?>\'; ?>';
			return;
		}
		
		if(strpos(strtolower($this->content), '!!!') === 0) {
			$this->content = trim(substr($this->content, 3));
			$this->content = $parser::doctype($this->content);
			return;
		}
		
	}
	
}

?>