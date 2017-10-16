<?php

require 'vendor/autoload.php';

// args
// f - file
// h - host
$args = getopt("f:h:");

$engine = new \Silver\Engine($args);
$engine->execute();