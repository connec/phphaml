<?php

/**
 * haml_parser.php
 */

namespace hamlparser\lib\haml;

use hamlparser\lib\Parser;

/**
 * The HamlParser class is the entrypoint for parsing HAML files.
 */

class HamlParser extends Parser {
	
	/**
	 * The class to use as a node (must extend {@link \hamlparser\lib\Node}).
	 */
	protected static $node_class = '\hamlparser\lib\haml\HamlNode';
	
}

?>