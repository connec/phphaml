<?php

/**
 * php.php
 */

namespace phphaml\haml\nodes;

/**
 * The Php class represents a PHP node in a Haml document.
 */

class Php extends Node {
  
  /**
   * Indicates that a newline should be printed after this node's content.
   */
  public $render_newline = false;
  
  /**
   * Indicates that an indentation string should be printed before this node's
   * content.
   */
  public $render_indent = false;
  
  /**
	 * Generates PHP/HTML code for this node and its children.
	 */
	public function render() {
	  
	  return '<?php ' . $this->content . '; ?>';
	  
	}
  
}

?>