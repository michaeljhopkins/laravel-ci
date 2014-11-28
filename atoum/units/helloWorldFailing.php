<?php

namespace vendor\project\tests\units;

// require_once 'path/to/mageekguy.atoum.phar';

include_once '/var/www/laravel-ci/atoum/classes/helloWorld.php';

use \mageekguy\atoum;
use \vendor\project;

class helloWorld extends atoum\test
{
	public function testSay()
	{
		$helloWorld = new \vendor\project\helloWorld();

		$this->string($helloWorld->say())->isEqualTo('This one Has To Fail!');
	}
}
