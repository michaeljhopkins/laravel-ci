<?php

return [

	'testers' => [
		'codeception' => [
			'command' => 'sh %project_path%/vendor/bin/codecept run',
		    'ok_matcher' => 'OK ',
		    'failed_matcher' => 'FAILURES!'
		],

		'phpunit' => [
			'command' => 'phpunit',
			'ok_matcher' => 'OK ',
			'failed_matcher' => 'FAILURES!'
		],
	],

	'projects' => [
		'consultoriodigital' => [
			'path' => '/var/www/consultoriodigital.dev',
			'watch_folders' => ['app', 'tests'],
			'exclude_folders' => ['tests/_output'],
			'tests_path' => 'tests',
		    'suites' => [
				'functional' => [
					'tester' => 'codeception',
					'tests_path' => 'functional',
				    'command_options' => 'functional',
				    'file_mask' => '*Cept.php',
				    'retries' => 3,
				]
		    ]
		]
	],

];
