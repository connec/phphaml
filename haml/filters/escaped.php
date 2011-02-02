<?php

/**
 * escaped.php
 */

namespace phphaml\haml\filters;

use \phphaml\Node;

/**
 * The Escaped filter escapes any HTML entities in the block.
 */

class Escaped extends Filter {
	
	/**
	 * Filters the given content.
	 */
	public static function filter(Node $node) {
		
		return array_map('htmlentities', $node->content);
		
	}
	
}

?>