<?php

/**
 * tag_node.php
 */

namespace hamlparser\lib\haml;

use \hamlparser\lib\Exception;

/**
 * The TagNode class represents a tag node in the parse tree.
 */

class TagNode extends HamlNode {
	
	/**
	 * Parses the content of the node.
	 */
	protected function parse() {
		
		$this->metadata['tag'] = 'div';
		$this->metadata['attributes'] = array();
		$this->metadata['self_closing'] = false;
		
		if($this->content[0] == '%') {
			$this->content = substr($this->content, 1);
			
			if(!preg_match('/^([a-zA-z]+)/', $this->content, $match)) {
				throw new Exception(
					'Invalid syntax: "%" must be followed by a valid tag name - line :line',
					array('line' => $this->line)
				);
			}
			
			$this->content = substr($this->content, strlen($match[0]));
			$this->metadata['tag'] = $match[0];
		}
		
		$re_id = '/^#([a-zA-Z0-9_]+)/';
		$re_class = '/^\.([a-zA-Z0-9-]+)/';
		while($this->content[0] == '.' or $this->content[0] == '#') {
			$char = $this->content[0];
			$type = $char == '#' ? 'id' : 'class';
			$re   = ${'re_' . $type};
			
			if(!preg_match($re, $this->content, $match)) {
				throw new Exception(
					'Invalid syntax: ":char" must be followed by a valid :type name - line :line',
					array('char' => $char, 'type' => $type, 'line' => $this->line)
				);
			}
			
			$this->content = substr($this->content, strlen($match[0]));
			if(!isset($this->metadata['attributes'][$type]))
				$this->metadata['attributes'][$type] = array();
			$this->metadata['attributes'][$type][] = substr($match[0], 1);
		}
		
		if($this->content[0] == ' ') {
			$this->content = substr($this->content, 1);
		} elseif($this->content[0] == '/') {
			$this->content = substr($this->content, 1);
			$this->metadata['self_closing'] = true;
		} elseif($this->content) {
			throw new Exception(
				'Invalid syntax: unexpected ":char" in tag definition, expected " " (space), "/" or EOL - line :line',
				array('char' => $this->content[0], 'line' => $this->line)
			);
		}
		
		ksort($this->metadata['attributes']);
		
	}
	
	/**
	 * Generates the string representation of the node.
	 */
	public function __toString() {
		
		$indent = str_repeat("\t", $this->indent_level);
		
		$attributes = $this->metadata['attributes'];
				
		if(isset($attributes['id']))
			$attributes['id'] = implode('_', $attributes['id']);
		if(isset($attributes['class']))
			$attributes['class'] = implode(' ', $attributes['class']);
		
		if(!empty($attributes)) {
			$attribute_str = '';
			foreach($attributes as $attribute => $value)
				$attribute_str .= $attribute . '="' . $value . '" ';
			$attribute_str = trim($attribute_str);
		}
		
		$return = $indent . '<' . $this->metadata['tag'];
		if(!empty($attributes))
			$return .= ' ' . $attribute_str;
		if($this->metadata['self_closing'])
			return $return . ' />' . "\n";
		$return .= '>';
		
		if($this->content or empty($this->children))
			return $return . $this->content . '</' . $this->metadata['tag'] . '>' . "\n";
		
		$return .= "\n";
		foreach($this->children as $child)
			$return .= (string)$child;
		$return .= $indent . '</' . $this->metadata['tag'] . '>' . "\n";
		
		return $return;
		
	}
	
}

?>