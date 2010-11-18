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
		
		$render = '<' . $this->tag;
		
		if(!empty($this->attributes))
			$render .= ' ' . $this->attributes();
		
		if($this->self_closing) {
			if($this->document->options['format'] == 'xhtml')
				$render .= ' /';
			return $render . '>';
		}
		
		return $render . '></' . $this->tag . '>';
		
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