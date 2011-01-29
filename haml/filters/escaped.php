<?php

/**
 * escaped.php
 */

namespace phphaml\haml\filters;

use
	\phphaml\Node,
	\phphaml\haml\Parser;

/**
 * The Escaped filter escapes any HTML entities in the block.
 */

class Escaped implements Filter {
	
	/**
	 * Filters the given content.
	 */
	public static function filter(Parser $parser, Node $node, array $content) {
		
		return array_map('htmlentities', $content);
		
	}
	
}

?>