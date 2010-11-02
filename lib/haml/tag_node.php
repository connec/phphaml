<?php

/**
 * tag_node.php
 */

namespace hamlparser\lib\haml;

use
	\hamlparser\lib\Exception,
	\hamlparser\lib\ruby\RubyHash;

/**
 * The TagNode class handles tree representation and parsing of HAML tag nodes.
 */

class TagNode extends Node {
	
	/**
	 * The name of the tag.
	 */
	protected $tag;
	
	/**
	 * Any inline content for the tag.
	 */
	protected $content;
	
	/**
	 * The attributes of the tag.
	 */
	protected $attributes = array();
	
	/**
	 * Indicates whether or not the tag is self-closing.
	 */
	protected $self_closing = false;
	
	/**
	 * Parses the tree from this node.
	 */
	public function parse() {
		
		// Check if this node has a multiline hash.
		while(substr($this->line, -1) == ',') {
			$this->line .= ' ' . $this->children[0]->line();
			array_shift($this->children);
		}
		
		if($this->line[0] == '%')
			$this->handle_tag();
		if($this->line[0] == '.')
			$this->handle_class();
		if($this->line[0] == '#')
			$this->handle_id();
		
		if(!$this->tag) {
			throw new Exception(
				'Sanity error: generated TagNode for invalid line :line',
				array('line' => $this->line_number)
			);
		}
		
		if($this->line[0] == '{')
			$this->handle_hash();
		if($this->line[0] == '/')
			$this->handle_self_closing();
		if(!empty($this->line))
			$this->handle_content();
		
		ksort($this->attributes);
		if(isset($this->attributes['class'])) {
			sort($this->attributes['class']);
			$this->attributes['class'] = implode(' ', $this->attributes['class']);
		}
		if(isset($this->attributes['id']))
			$this->attributes['id'] = implode('_', $this->attributes['id']);
		foreach($this->attributes as $attribute => $value)
			$this->attributes[$attribute] = $attribute . '="' . $value . '"';
		
		parent::parse();
		
	}
	
	/**
	 * Handles the "%" tag definition.
	 */
	protected function handle_tag() {
		
		if(!preg_match('/^%[a-zA-Z0-9_:-]+/', $this->line, $match)) {
			throw new Exception(
				'Syntax error: invalid tag name - line :line',
				array('line' => $this->line_number)
			);
		}
		
		$this->line = substr($this->line, strlen($match[0]));
		$this->tag = substr($match[0], 1);
		
	}
	
	/**
	 * Handles any "." class attributes.
	 */
	protected function handle_class() {
		
		if(!$this->tag)
			$this->tag = 'div';
		$this->attributes['class'] = array();
		
		while($this->line[0] == '.') {
			if(!preg_match('/^\.[_a-zA-Z-][_a-zA-Z0-9-]*/', $this->line, $match)) {
				throw new Exception(
					'Syntax error: invalid class name - line :line',
					array('line' => $this->line_number)
				);
			}
			
			$this->line = substr($this->line, strlen($match[0]));
			$this->attributes['class'][] = substr($match[0], 1);
		}
		
	}
	
	/**
	 * Handles the "#" id definition.
	 */
	protected function handle_id() {
		
		$this->attributes['id'] = array();
		
		if(!preg_match('/^#[a-zA-Z][_a-zA-Z0-9:.-]*/', $this->line, $match)) {
			throw new Exception(
				'Syntax error: invalid ID - line :line',
				array('line' => $this->line_number)
			);
		}
		
		$this->line = substr($this->line, strlen($match[0]));
		$this->attributes['id'][] = substr($match[0], 1);
		
	}
	
	/**
	 * Handles an "{...}" attribute hash.
	 */
	protected function handle_hash() {
		
		if(!preg_match('/^{.*}/', $this->line, $match)) {
			throw new Exception(
				'Syntax error: missing closing "}" for hash - line :line',
				array('line' => $this->line_number)
			);
		}
		
		$this->line = substr($this->line, strlen($match[0]));
		
		try {
			$hash = new RubyHash($match[0]);
			$hash = $hash->to_a();
		} catch(Exception $e) {
			throw new Exception(
				$e->getMessage() . ' - line :line',
				array('line' => $this->line_number)
			);
		}
		
		$this->attributes = array_merge($hash, $this->attributes);
		if(isset($hash['class']) and isset($this->attributes['class']))
			$this->attributes['class'] = array_merge($this->attributes['class'], $hash['class']);
		if(isset($hash['id']) and isset($this->attributes['id']))
			$this->attributes['id'] = array_merge($this->attributes['id'], $hash['id']);
		
	}
	
	/**
	 * Handles self closing tags.
	 */
	protected function handle_self_closing() {
		
		if($this->line != '/' or !empty($this->children)) {
			throw new Exception(
				'Parse error: self-closing tags cannot have content - line :line',
				array('line' => $this->line_number)
			);
		}
		
		$this->self_closing = true;
		
	}
	
	/**
	 * Handles inline content.
	 */
	protected function handle_content() {
		
		if(!empty($this->children)) {
			throw new Exception(
				'Parse error: cannot mix inline and indented content - line :line',
				array('line' => $this->children[0]->line_number())
			);
		}
		$this->content = trim($this->line);
		$this->line = '';
		
	}
	
	/**
	 * Generates the result of the tree from this node.
	 */
	public function __toString() {
		
		$indent = str_repeat($this->parser->indent(), $this->indent_level);
		
		$return = $indent . '<' . $this->tag;
		
		if(!empty($this->attributes))
			$return .= ' ' . implode(' ', $this->attributes);
		
		if($this->self_closing)
			return $return . " />\n";
		
		$return .= '>';
		
		if($this->content or empty($this->children))
			return $return . $this->content . "</$this->tag>\n";
		
		$return .= "\n";
		foreach($this->children as $child)
			$return .= (string)$child;
		return $return . "$indent</$this->tag>\n";
		
	}
	
}

?>