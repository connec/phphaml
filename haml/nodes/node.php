<?php

/**
 * node.php
 */

namespace phphaml\haml\nodes;

use \phphaml\haml\Line;

/**
 * This Node class extends the base Node class with HAML specific stuff.
 */

class Node extends \phphaml\Node {
	
  /**
   * Indicates that a newline should be printed after this node's content.
   */
  public $render_newline = true;
  
  /**
   * Indicates that an indentation string should be printed before this node's
   * content.
   */
  public $render_indent = true;
  
	/**
	 * Generates PHP/HTML code for this node and its children.
	 */
	public function render() {
	  
	  return $this->render_children();
	  
	}
	
	/**
	 * Generates PHP/HTML code for this nodes children.
	 */
	public function render_children() {
		
	  $newline = '<?php echo "\n"; ?>';
	  
	  $result = '';
	  $i = 0;
		foreach($this->children as $child) {
		  if($child->render_indent)
		    $result .= $child->indent();
	    $result .= $child->render();
	    if($child->render_newline and ++$i != count($this->children))
	      $result .= $newline;
		}
		return $result;
		
	}
	
}

?>