<?php

/**
 * parser.php
 */

namespace hamlparser\lib\haml;

/**
 * The Parser clas handles the parsing of HAML documents.
 */

class Parser extends \hamlparser\lib\Parser {
	
	/**
	 * The class to use for tree nodes.
	 */
	protected static $node_class = '\hamlparser\lib\haml\Node';
	
}

?>