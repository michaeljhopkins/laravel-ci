<?php

return [

	'show_progress' => false,

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
			'watch_folders' => ['app', 'tests', 'vendor/laravel/framework/tests'],
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
		    ],
		],

		'laravel/framework' => [
			'path' => '/var/www/consultoriodigital.dev/vendor/laravel/framework',
			'watch_folders' => ['src', 'tests'],
			'exclude_folders' => [
				'tests/View/fixtures',
				'tests/Support/stubs',
			    'tests/Routing/results',
			    'tests/Routing/fixtures',
			    'tests/Database/stubs/EloquentModelNamespacedStub.php',
			],
			'tests_path' => 'tests',
		    'suites' => [
				'unit' => [
					'tester' => 'phpunit',
					'tests_path' => '',
				    'command_options' => '',
				    'file_mask' => '*.php',
				    'retries' => 3,
				]
		    ],
		]
	],

];
