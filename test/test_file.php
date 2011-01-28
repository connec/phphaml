<?php

require 'library.php';

phphaml\Library::autoload();

$parser = new phphaml\haml\Parser($argv[1]);
echo $parser->render();

?>