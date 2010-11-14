<?php

namespace hamlparser\lib\haml;

class Node extends \hamlparser\lib\RootNode {
	
	const RE_TAG = '/^(?:%[_a-zA-Z]|\.[-_a-zA-Z0-9]|#[a-zA-Z])/';
	
	protected static $parser_class = '\hamlparser\lib\haml\Parser';
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
		
		switch(true) {
			case preg_match(self::RE_TAG, $line):
				$this->children[] = new TagNode;
				if(substr($line, -1) == ',') {
					static::$multiline = true;
					$parser::expect_indent($parser::EXPECT_MORE);
				} else {
					end($this->children)->parse();
				}
			break;
			case $line[0] == '-':
				$this->children[] = new PhpNode;
				end($this->children)->parse();
			break;
			default:
				$this->children[] = new TextNode;
				end($this->children)->parse();
				$parser::expect_indent($parser::EXPECT_LESS | $parser::EXPECT_SAME);
			break;
		}
		
	}
	
}

?>