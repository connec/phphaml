<?php

/**
 * javascript.php
 */

namespace phphaml\haml\filters;

use \phphaml\Node;

/**
 * The javascript filter wraps the content in script and CDATA tags.
 */

class Javascript extends Filter {
	
	/**
	 * Filters the given content.
	 */
	public static function filter(Node $node) {
		
		foreach($node->content as &$line)
			$line = str_repeat($node->indent_string(), 2) . $line;
		
		$indent = str_repeat($node->indent_string(), $node->indent_level);
		
		$q = $node->option('attr_wrapper');
		array_unshift(
			$node->content,
			$indent . "<script type={$q}text/javascript{$q}>",
			$indent . $node->indent_string() . '//<![CDATA['
		);
		
		array_push(
			$node->content,
			$indent . $node->indent_string() . '//]]>',
			$indent . '</script>'
		);
		
		return $node->content;
		
	}
	
}

?>