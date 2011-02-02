<?php

/**
 * css.php
 */

namespace phphaml\haml\filters;

use \phphaml\Node;

/**
 * The css filter wraps the content in style and CDATA tags.
 */

class Css extends Filter {
	
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
			$indent . "<style type={$q}text/css{$q}>",
			$indent . $node->indent_string() . '/*<![CDATA[*' . '/'
		);
		
		array_push(
			$node->content,
			$indent . $node->indent_string() . '/*]]>*/',
			$indent . '</style>'
		);
		
		return $node->content;
		
	}
	
}

?>