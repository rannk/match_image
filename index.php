<?php
require_once "vendor/autoload.php";
require_once "libs/MatchCommand.php";

use Symfony\Component\Console\Application;
use libs\MatchCommand;
$app = new Application();
$app->add(new MatchCommand());
$app->run();
?>
