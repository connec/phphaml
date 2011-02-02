<?php

/**
 * node.php
 */

namespace phphaml\haml\nodes;

/**
 * This Node class extends the base Node class with HAML specific stuff.
 */

abstract class Node extends \phphaml\Node {
	
	/**
	 * Indicates whether or not a newline should be rendered after this node.
	 */
	public $append_newline = true;
	
}

?>