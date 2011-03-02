<?php

/**
 * text.php
 */

namespace phphaml\haml\nodes;

use \phphaml\haml\ruby;

/**
 * The Text node represents a line of text in a HAML document.
 */

class Text extends Node {
	
	/**
	 * Indicates whether this line's contents should be escaped.
	 */
	public $escape;
	
	/**
	 * Indicates whether this line's contents should be whitespace preserved.
	 */
	public $preserve;
	
	/**
	 * Renders the content of the node.
	 */
	public function render() {
		
		$this->content = (string)$this->content;
		
		if($this->escape)
			$this->content = htmlentities($this->content);
		
		if($this->preserve)
			$this->preserve();
		
		$this->content = '<?php echo (' . ruby\InterpolatedString::compile($this->content) . '); ?>';
		
		return $this->content;
		
	}
	
	/**
	 * Generates the string representation (render) of this node.
	 */
	public function __toString() {
		
	  return $this->render();
		
	}
	
	/**
	 * Replaces linebreaks in preserved content with "&#x000A;".
	 */
	protected function preserve() {
		
		$re = '/<(' . implode('|', $this->option('preserve')) . ")>.*?\\\\n.*?<\/\\1>/i";
		
		while(preg_match($re, $this->content, $match, PREG_OFFSET_CAPTURE)) {
			$this->content = substr_replace(
				$this->content,
				str_replace('\n', '&#x000A;', $match[0][0]),
				$match[0][1],
				strlen($match[0][0])
			);
		}
		
	}
	
}

?>