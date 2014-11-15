<?php

namespace App\Services\Watcher\Data\Repositories;

use App\Services\Watcher\Data\Entities\Tester;
use App\Services\Watcher\Data\Entities\Project;
use App\Services\Watcher\Data\Entities\Suite;
use App\Services\Watcher\Data\Entities\Test;
use Symfony\Component\Finder\Finder;

class Watcher {

	public function createOrUpdateTester($name, $command)
	{
		Tester::updateOrCreate(['name' => $name], ['command' => $command]);
	}

	public function createOrUpdateProject($name, $path, $tests_path)
	{
		return Project::updateOrCreate(['name' => $name], ['path' => $path, 'tests_path' => $tests_path]);
	}

	public function createOrUpdateSuite($name, $project_id, $suite_data)
	{
		$tester = Tester::where('name', $suite_data['tester'])->first();

		return Suite::updateOrCreate(
			['name' => $name, 'project_id' => $project_id],
			[
				'tester_id' => $tester->id,
			    'tests_path' => $suite_data['tests_path'],
			    'command_options' => $suite_data['command_options'],
			    'file_mask' => $suite_data['file_mask'],
			]
		);
	}

	public function getSuites()
	{
		return Suite::all();
	}

	public function createOrUpdateTest($file, $suite)
	{
		Test::updateOrCreate([
            'name' => $file->getRelativePathname(),
            'suite_id' => $suite->id,
		    'state' => 'queued',
		]);
	}

	public function syncTests()
	{
		foreach($this->getSuites() as $suite)
		{
			$this->syncTestFiles($suite);
		}
	}

	private function syncTestFiles($suite)
	{
		$files = $this->getAllFilesFromSuite($suite);

		foreach($files as $file)
		{
			$this->createOrUpdateTest($file, $suite);
		}

		foreach($suite->tests as $test)
		{
			if ( ! file_exists($path = make_path([$suite->testsFullPath, $test->name])))
			{
				$test->delete();
			}
		}
	}

	private function getAllFilesFromSuite($suite)
	{
		$files = Finder::create()->files()->in($suite->testsFullPath);

		if ($suite->file_mask)
		{
			$files->name($suite->file_mask);
		}

		return iterator_to_array($files, false);
	}

}
