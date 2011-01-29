<?php

/**
 * css.php
 */

namespace phphaml\haml\filters;

use
	\phphaml\Node,
	\phphaml\haml\Parser;

/**
 * The css filter wraps the content in style and CDATA tags.
 */

class Css implements Filter {
	
	/**
	 * Filters the given content.
	 */
	public static function filter(Parser $parser, Node $node, array $content) {
		
		foreach($content as &$line)
			$line = str_repeat($parser->indent_string(), 2) . $line;
		
		$indent = str_repeat($parser->indent_string(), $node->indent_level());
		
		$q = $parser->option('attr_wrapper');
		array_unshift(
			$content,
			$indent . "<style type={$q}text/css{$q}>",
			$indent . $parser->indent_string() . '/*<![CDATA[*' . '/'
		);
		
		array_push(
			$content,
			$indent . $parser->indent_string() . '/*]]>*/',
			$indent . '</style>'
		);
		
		return $content;
		
	}
	
}

?>