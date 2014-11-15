<?php

return [

	'testers' => [
		'codeception' => 'sh %app_path%/vendor/bin/codecept run',
	],

	'projects' => [
		'consultoriodigital' => [
			'path' => '/var/www/consultoriodigital.dev',
			'watch_folders' => ['app', 'tests'],
			'tests_path' => 'tests',
		    'suites' => [
				'functional' => [
					'tester' => 'codeception',
					'tests_path' => 'functional',
				    'command_options' => 'functional',
				    'file_mask' => '*Cept.php',
				]
		    ]
		]
	],

];
