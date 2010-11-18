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
	 * A flag indicating whether or not this tag is self-closing.
	 */
	public $self_closing = false;
	
	/**
	 * Generates and returns the output for this Node's subtree.
	 */
	public function render() {
		
		$render = '<' . $this->tag;
		
		if($this->self_closing) {
			if($this->document->options['format'] == 'xhtml')
				$render .= ' /';
			return $render . '>';
		}
		
		return '<' . $this->tag . '></' . $this->tag . '>';
		
	}
	
}

?>