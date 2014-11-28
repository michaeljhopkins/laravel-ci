<?php

use Tester\Assert;

# Load Tester library
require __DIR__ . '/../../vendor/autoload.php';          # installation by Composer
// require __DIR__ . '/../tester/Tester/bootstrap.php';  # manual installation

# Load the tested class. Composer or your autoloader surely takes care of that in practice.
require __DIR__ . '/../classes/Greeting.php';


# Environment configuration improves error dumps readability.
# You don't have to use it if you prefer the PHP default dump.
Tester\Environment::setup();


$o = new Greeting;

Assert::same( 'Hello, John', $o->say('John') );  # we expect the same
