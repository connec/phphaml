<?php

/**
 * plain.php
 */

namespace phphaml\haml\filters;

use
	\phphaml\Node,
	\phphaml\haml\Parser;

/**
 * The plain filter simply outputs what it is given, without any additional parsing.
 */

class Plain implements Filter {
	
	/**
	 * Filters the given content.
	 */
	public static function filter(Parser $parser, Node $node, array $content) {
		
		return $content;
		
	}
	
}

?>