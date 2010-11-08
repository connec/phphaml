<?php

namespace hamlparser\lib\haml;

class Node extends \hamlparser\lib\Node {
	
	protected static $parser = '/hamlparser/lib/haml/Parser';
	protected static $multiline = false;
	
	public function add_child() {
		
		$parser = static::$parser_class;
		$line = $parser::line();
		
		if(static::$multiline) {
			$this->content .= $line;
			$parser::expect_indent($parser::EXPECT_SAME);
			
			if(substr($line, -1) != ',') {
				static::$multiline = false;
				$this->parse();
				$parser::expect_indent($parser::EXPECT_ANY);
			}
			
			return;
		}
		
		switch($line[0]) {
			case '%':
			case '.':
			case '#':
				$this->children[] = new TagNode;
				if(substr($line, -1) == ',') {
					static::$multiline = true;
					$parser::expect_indent($parser::EXPECT_MORE);
				} else {
					end($this->children)->parse();
				}
			break;
			default:
				$this->children[] = new TextNode;
				$parser::expect_indent($parser::EXPECT_LESS | $parser::EXPECT_SAME);
			break;
		}
		
	}
	
}

?>