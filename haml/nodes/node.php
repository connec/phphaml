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
	  
	  $result = array();
		foreach($this->children as $i => $child)
			$result = array_merge($result, $child->render());
		return implode($newline, $result);
		
	}
}

?>