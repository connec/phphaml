<?php

namespace hamlparser\lib\haml;

use \hamlparser\lib\ruby\RubyHash;

class TagNode extends Node {
	
	const RE_TAG = '/^%([_a-zA-Z][_a-zA-Z0-9:-]*)/';
	const RE_CLASS = '/^\.(-?[_a-zA-Z][_a-zA-Z0-9-]*)/';
	const RE_ID = '/^#([a-zA-Z][_a-zA-Z0-9.:-]*)/';
	const RE_HASH_ATTRIBUTES = '/^{.*}/';
	const RE_HTML_ATTRIBUTES = '/^\((.*)\)/';
	
	protected $tag = 'div';
	protected $attributes = array();
	protected $trim_external = false;
	protected $trim_internal = false;
	protected $self_closing = false;
	
	public function parse() {
		
		$parser = static::$parser_class;
		
		if(preg_match(self::RE_TAG, $this->content, $match)) {
			$this->tag = $match[1];
			$this->content = substr($this->content, strlen($match[0]));
		}
		
		$this->attributes['class'] = array();
		while(preg_match(self::RE_CLASS, $this->content, $match)) {
			$this->attributes['class'][] = $match[1];
			$this->content = substr($this->content, strlen($match[0]));
		}
		
		$this->attributes['id'] = array();
		if(preg_match(self::RE_ID, $this->content, $match)) {
			$this->attributes['id'][] = $match[1];
			$this->content = substr($this->content, strlen($match[0]));
		}
		
		if(preg_match(self::RE_HASH_ATTRIBUTES, $this->content, $match)) {
			$attributes = new RubyHash($match[0]);
			$this->attributes = array_merge_recursive($this->attributes, $attributes->to_a());
			$this->content = substr($this->content, strlen($match[0]));
		}
		ksort($this->attributes);
		sort($this->attributes['class']);
		
		if($this->content[0] == '/') {
			if($this->content != '/') {
				throw new Exception(
					'Syntax error: self-closing tags cannot have content - line :line',
					array('line' => $this->line_number)
				);
			}
			$this->self_closing = true;
			$parser::expect_indent($parser::EXPECT_LESS | $parser::EXPECT_SAME);
		}
		
		$this->content = trim($this->content);
		if($this->content)
			$parser::expect_indent($parser::EXPECT_LESS | $parser::EXPECT_SAME);
		
	}
	
	public function __toString() {
		
		$indent = str_repeat($this->indent_string, $this->indent_level);
		
		$return = $indent . '<' . $this->tag . ' ';
		
		foreach($this->attributes as $attribute => $value) {
			if(!$value)
				continue;
			if($attribute == 'class')
				$value = implode(' ', $value);
			if($attribute == 'id')
				$value = implode('_', $value);
			
			$return .= $attribute . '="' . $value . '" ';
		}
		$return = rtrim($return);
		
		if($this->self_closing)
			return $return . " />\n";
		
		$return .= '>';
		
		if($this->content or empty($this->children))
			return $return . $this->content . '</' . $this->tag . ">\n";
		
		$return .= "\n";
		
		for($i = 0; $i < count($this->children); $i ++)
			$return .= (string)$this->children[$i];
		
		return $return . $indent . '</' . $this->tag . ">\n";
		
	}
	
}

?>