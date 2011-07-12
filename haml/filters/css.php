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
		
		$q = $node->option('attr_wrapper');
		array_unshift(
			$node->content,
			"<style type={$q}text/css{$q}>",
			$node->indent_string() . '/*<![CDATA[*' . '/'
		);
		
		array_push(
			$node->content,
			$node->indent_string() . '/*]]>*/',
			'</style>'
		);
		
		return $node->content;
		
	}
	
}

?>