<?php

/**
 * tag.php
 */

namespace haml\haml;

/**
 * The Tag class represents a tag node in a HAML document.
 */

class TagNode extends Node {
	
	/**
	 * The tag name of this tag node.
	 */
	public $tag = 'div';
	
	/**
	 * An array of attributes for this tag.
	 */
	public $attributes = array();
	
	/**
	 * A flag indicating whether or not this tag is self-closing.
	 */
	public $self_closing = false;
	
	/**
	 * Generates and returns the output for this Node's subtree.
	 */
	public function render() {
		
		$indent = str_repeat($this->document->indent_string, $this->indent_level);
		$open_tag = $indent . '<' . $this->tag . (empty($this->attributes) ? '' : ' ' . $this->attributes()) . '>';
		$close_tag = '</' . $this->tag . '>';
		
		if($this->self_closing) {
			if($this->document->options['format'] == 'xhtml')
				return substr($open_tag, 0, -1) . ' />';
			else
				return $open_tag;
		}
		
		if(empty($this->children))
			return $open_tag . $close_tag;
		
		return $open_tag . "\n"
			. $this->render_children() . "\n"
			. $indent . $close_tag;
		
	}
	
	/**
	 * Generates the attribute string for this tag.
	 */
	protected function attributes() {
		
		if(empty($this->attributes))
			return '';
		
		ksort($this->attributes);
		$attributes = $this->attributes;
		
		if(isset($attributes['class'])) {
			sort($attributes['class']);
			$attributes['class'] = implode(' ', $attributes['class']);
		}
		
		if(isset($attributes['id']))
			$attributes['id'] = implode('_', $attributes['id']);
		
		return $this->document->attributes($attributes);
		
	}
	
}

?>