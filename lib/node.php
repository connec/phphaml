<?php

namespace hamlparser\lib;

class Node {
	
	protected static $parser_class = '\hamlparser\lib\Parser';
	
	protected $content;
	protected $indent_string;
	protected $indent_level;
	protected $line_number;
	protected $parent;
	protected $children = array();
	
	public function __construct() {
		
		$parser = static::$parser_class;
		$this->content = $parser::line();
		$this->indent_string = $parser::indent_string();
		$this->indent_level = $parser::indent_level();
		$this->line_number = $parser::line_number();
		$this->parent = $parser::node();
		
	}
	
	public function __toString() {
		
		$return = '';
		if($this->content) {
			$return = str_repeat($this->indent_string, $this->indent_level);
			$return .= $this->content . "\n";
		}
		foreach($this->children as $child)
			$return .= (string)$child;
		return $return;
		
	}
	
	public function add_content($content) {
		
		$this->content .= $content;
		
	}
	
	public function add_child() {
		
		$class = get_class($this);
		$this->children[] = new $class;
		
	}
	
	public function content() {
		
		return $this->content;
		
	}
	
	public function parent() {
		
		return $this->parent;
		
	}
	
	public function children() {
		
		return $this->children;
		
	}
	
	public function last_child() {
		
		return end($this->children);
		
	}
	
	public function index_of($child = null) {
		
		if(!$child)
			return $this->parent->index_of($this);
		return array_search($child, $this->children, true);
		
	}
	
	public function previous_sibling($child = null) {
		
		if(!$child)
			return $this->parent->previous_sibling($this);
		
		if($i = $this->index_of($child)) {
			if(!isset($this->children[$i - 1]))
				return false;
			return $this->children[$i - 1];
		}
		
		return false;
		
	}
	
	public function next_sibling($child = null) {
		
		if(!$child)
			return $this->parent->next_sibling($this);
		
		if($i = $this->index_of($child)) {
			if(!isset($this->children[$i + 1]))
				return false;
			return $this->children[$i + 1];
		}
		
		return false;
		
	}
	
}

?>