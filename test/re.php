<?php

use \phphaml\haml\Parser;

require '../library.php';

\phphaml\Library::autoload();

$parser = new Parser('references\1.haml');
$parser->parse();
echo htmlentities($parser->render());

?>