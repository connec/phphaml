<?php

/**
 * tag.php
 */

namespace phphaml\haml\nodes;

/**
 * The Tag node represents a HAML tag in a parse tree.
 */

class Tag extends Node {
	
	/**
	 * The tag name of this tag.
	 */
	public $tag_name = 'div';
	
	/**
	 * An array of attributes for this tag.
	 */
	public $attributes = array();
	
	/**
	 * A flag indicating whether or not this tag is self-closing.
	 */
	public $self_closing = false;
	
	/**
	 * A flag indicating whether or not this tag should have surrounding whitespace.
	 */
	public $trim_outer = false;
	
	/**
	 * A flag indicating whether or not this tag should have inner whitespace.
	 */
	public $trim_inner = false;
	
	/**
	 * Renders the parsed tree.
	 */
	public function render() {
		
		$open_tag = '<' . $this->tag_name . (empty($this->attributes) ? '' : ' ' . $this->render_attributes()) . '>';
		$close_tag = '</' . $this->tag_name . '>';
		
		if($this->self_closing) {
			if($this->option('format') == 'xhtml')
				$open_tag = substr($open_tag, 0, -1) . ' />';
			
			return $open_tag;
		}
		
		if(empty($this->children))
			return $open_tag . $this->content . $close_tag;
		
		if($this->trim_inner) {
			return
				  $open_tag
				. ltrim(str_replace("\n" . $this->indent_string(), "\n", rtrim($this->render_children())))
				. $close_tag;
		}
		
		$indent = str_repeat($this->indent_string(), $this->indent_level);
		return $open_tag . "\n" . $this->render_children() . $indent . $close_tag;
		
	}
	
	/**
	 * Renders the attribute string for this tag.
	 */
	protected function render_attributes() {
		
		$attributes = array();
		$class = array();
		$id = array();
		
		$q = $this->option('attr_wrapper');
		
		foreach($this->attributes as $attribute) {
			list($key, $value) = $attribute;
			$key = (string)$key;
			
			if(empty($value))
				continue;
			
			if($key == 'class') {
				if(is_array($value)) {
					foreach($value as $_value)
						$class[] = (string)$_value;
				} else
					$class[] = (string)$value;
			} elseif($key == 'id') {
				if(is_array($value)) {
					foreach($value as $_value)
						$id[] = (string)$_value;
				} else
					$id[] = (string)$value;
			} else {
				$value = (string)$value;
				if($value == "1") {
					if(substr($this->option('format'), 0, 4) == 'html')
						$attributes[] = $key;
					else
						$attributes[] = $key . '=' . $q . $key . $q;
				} else
					$attributes[] = $key . '=' . $q . $value . $q;
			}
		}
		
		if(!empty($class)) {
			natcasesort($class);
			$attributes[] = 'class=' . $q . implode(' ', $class) . $q;
		}
		
		if(!empty($id))
			$attributes[] = 'id=' . $q . implode('_', $id) . $q;
		
		natcasesort($attributes);
		return implode(' ', $attributes);
		
	}
	
}

?>