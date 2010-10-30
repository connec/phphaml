<?php

/**
 * haml_node.php
 */

namespace hamlparser\lib\haml;

use
	\hamlparser\lib\Exception,
	\hamlparser\lib\Node;

/**
 * The HamlNode is used in parse trees in HamlParser.
 */

class HamlNode extends Node {
	
	/**
	 * Returns the appropriate node type for the given line.
	 */
	public static function factory($line, $indent_level, $content = '', $parent = null, $children = array()) {
		
		if(empty($content))
			$node = 'Empty';
		else {
			switch($content[0]) {
				case '%':
				case '#':
				case '.':
					$node = 'Tag';
				break;
				default:
					$node = 'Text';
			}
		}
		
		$node = '\hamlparser\lib\haml\\' . $node . 'Node';
		$node = new $node($line, $indent_level, $content, $parent, $children);
		$node->parse();
		return $node;
		
	}
	
}

?>