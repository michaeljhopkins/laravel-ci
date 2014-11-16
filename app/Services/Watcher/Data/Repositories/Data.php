<?php

namespace App\Services\Watcher\Data\Repositories;

use App\Services\Watcher\Data\Entities\Queue;
use App\Services\Watcher\Data\Entities\Run;
use App\Services\Watcher\Data\Entities\Tester;
use App\Services\Watcher\Data\Entities\Project;
use App\Services\Watcher\Data\Entities\Suite;
use App\Services\Watcher\Data\Entities\Test;
use Symfony\Component\Finder\Finder;

use Response;

class Data {

	const STATE_INITIALIZED = 'initialized';

	const STATE_QUEUED = 'queued';

	const STATE_OK = 'ok';

	const STATE_FAILED = 'failed';

	const STATE_RUNNING = 'running';

	public function createOrUpdateTester($name, $data)
	{
		Tester::updateOrCreate(
			['name' => $name],
			[
				'command' => $data['command'],
			    'ok_matcher' => $data['ok_matcher'],
			]
		);
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
			    'retries' => $suite_data['retries'],
			]
		);
	}

	public function getSuites()
	{
		return Suite::all();
	}

	public function createOrUpdateTest($file, $suite)
	{
		$test = Test::updateOrCreate(
			[
	            'name' => $file->getRelativePathname(),
	            'suite_id' => $suite->id,
			],
			[
				'state' => self::STATE_INITIALIZED,
			]
		);

		$this->addTestToQueue($test);
	}

	public function syncTests()
	{
		foreach($this->getSuites() as $suite)
		{
			$this->syncTestsForSuite($suite);
		}
	}

	private function syncTestsForSuite($suite)
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

	public function isTestFile($path)
	{
		foreach(Test::all() as $test)
		{
			if ($test->fullPath == $path)
			{
				return $test;
			}
		}

		return false;
	}

	public function queueAllTests()
	{
		foreach(Test::all() as $test)
		{
			$this->addTestToQueue($test);
		}
	}

	public function addTestToQueue($test)
	{
		Queue::updateOrCreate(['test_id' => $test->id]);

		if ( ! in_array($test->state, [self::STATE_RUNNING, self::STATE_QUEUED]))
		{
            $test->state = self::STATE_QUEUED;

			$test->save();
        }
	}

	public function getNextTestFromQueue()
	{
		if ( ! $queue = Queue::first())
		{
			return;
		}

		return $queue->test;
	}

	public function storeTestResult($test, $lines)
	{
		$ok = $this->testIsOk($test, $lines);

		$run = Run::create([
	        'test_id' => $test->id,
	        'was_ok' => $ok,
	        'log' => implode(PHP_EOL, $lines),
		]);

		$test->state = $ok ? self::STATE_OK : self::STATE_FAILED;
		$test->last_run_id = $run->id;
		$test->save();

		$this->removeTestFromQueue($test);

		return $ok;
	}

	public function testIsOk($test, $lines)
	{
		$lines = array_reverse($lines);

		for ($count = 0; $count <= 1; $count++)
		{
			if (starts_with($lines[$count], $test->suite->tester->ok_matcher))
			{
				return true;
			}
		}

		return false;
	}

	private function removeTestFromQueue($test)
	{
		Queue::where('test_id', $test->id)->delete();
	}

	public function markTestAsRunning($test)
	{
		$test->state = self::STATE_RUNNING;

		$test->save();
	}

	public function getAllTests()
	{
		$tests = [];

		foreach (Test::orderBy('updated_at', 'desc')->get() as $test)
		{
			$tests[] = [
				'id' => $test->id,
			    'name' => $test->name,
			    'updated_at' => $test->updated_at->diffForHumans(),
			    'state' => $test->state,
			];
		}

		return Response::json($tests);
	}

}
