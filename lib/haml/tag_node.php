<?php

/**
 * tag_node.php
 */

namespace hamlparser\lib\haml;

use
	\hamlparser\lib\Exception,
	\hamlparser\lib\RubyHash;

/**
 * The TagNode class represents a tag node in the parse tree.
 */

class TagNode extends HamlNode {
	
	/**
	 * Parses the content of the node.
	 */
	protected function parse() {
		
		$this->metadata['attributes'] = array();
		
		$this->parse_tag_name();
		$this->parse_classes();
		$this->parse_id();
		
		if(!isset($this->metadata['tag'])) {
			throw new Exception(
					'Sanity error: created TagNode but no tag definition (%, . or #) found - line :line',
					array('line' => $this->line)
			);
		}
		
		$this->parse_attribute_hash();
		$this->parse_self_closing();
		$this->parse_content();
		
	}
	
	/**
	 * Parses the tag definition from the content.
	 */
	protected function parse_tag_name() {
		
		if($this->content[0] != '%')
			return;
		
		$re = '/%([a-zA-Z]+)/';
		if(!preg_match($re, $this->content, $match)) {
			throw new Exception(
				'Syntax error: tags can contain only alpha characters - line :line',
				array('line' => $this->line)
			);
		}
		
		$this->content = substr($this->content, strlen($match[0]));
		$this->metadata['tag'] = $match[1];
		
	}
	
	/**
	 * Parses any classes from the content.
	 */
	protected function parse_classes() {
		
		if($this->content[0] != '.')
			return;
		
		$re = '/\.[a-zA-Z0-9-]+/';
		if(!preg_match_all($re, $this->content, $match)) {
			throw new Exception(
				'Syntax error: invalid class attribute - line :line',
				array('line' => $this->line)
			);
		}
		
		if(!isset($this->metadata['tag']))
			$this->metadata['tag'] = 'div';
		
		$this->metadata['attributes']['class'] = array();
		foreach($match[0] as $class) {
			$this->content = substr($this->content, strlen($class));
			$this->metadata['attributes']['class'][] = substr($class, 1);
		}
		
	}
	
	/**
	 * Parses the ID from the content.
	 */
	protected function parse_id() {
		
		if($this->content[0] != '#')
			return;
		
		$re = '/(#[a-zA-Z0-9_]+)/';
		if(!preg_match($re, $this->content, $match)) {
			throw new Exception(
				'Syntax error: invalid ID - line :line',
				array('line' => $this->line)
			);
		}
		
		$this->content = substr($this->content, strlen($match[0]));
		
		if(!isset($this->metadata['tag']))
			$this->metadata['tag'] = 'div';
		
		$this->metadata['attributes']['id'] = array(substr($match[1], 1));
		
	}
	
	/**
	 * Parses the self closing indicator from the content.
	 */
	protected function parse_self_closing() {
		
		if($this->content[0] != '/') {
			$this->metadata['self_closing'] = false;
		} elseif($this->content == '/') {
			$this->metadata['self_closing'] = true;
			$this->content = '';
		} else {
			throw new Exception(
				'Syntax error: unexpected ":char", expected EOL - line :line',
				array('char' => $this->content[1], 'line' => $this->line)
			);
		}
		
	}
	
	/**
	 * Parses the attributes hash from the content.
	 */
	protected function parse_attribute_hash() {
		
		if($this->content[0] != '{')
			return;
		
		$re = '/^{.*}/';
		if(!preg_match($re, $this->content, $match)) {
			throw new Exception(
				'Syntax error: bad attribute hash format - line :line',
				array('line' => $this->line)
			);
		}
		
		$this->content = substr($this->content, strlen($match[0]));
		
		try {
			$hash = new RubyHash($match[0]);
			$this->metadata['attributes'] = array_merge_recursive(
				$this->metadata['attributes'],
				$hash->to_a()
			);
			ksort($this->metadata['attributes']);
		} catch(Exception $e) {
			throw new Exception(
				$e->getMessage() . ' - line :line',
				array('line' => $this->line)
			);
		}
		
	}
	
	/**
	 * Parses the remaining content.
	 */
	protected function parse_content() {
		
		if($this->content and $this->content[0] != ' ') {
			throw new Exception(
				'Syntax error: unexpected ":char", expected " " (space) - line :line',
				array('char' => $this->content[0], 'line' => $this->line)
			);
		} elseif($this->content) {
			$this->content = substr($this->content, 1);
		}
		
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