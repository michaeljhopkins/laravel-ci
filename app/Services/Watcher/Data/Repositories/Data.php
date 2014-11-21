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
use Symfony\Component\Finder\SplFileInfo;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

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

	public function syncTests($exclusions)
	{
		foreach($this->getSuites() as $suite)
		{
			$this->syncTestsForSuite($suite, $exclusions);
		}
	}

	private function syncTestsForSuite($suite, $exclusions)
	{
		$files = $this->getAllFilesFromSuite($suite);

		foreach($files as $file)
		{
			if ( ! $this->isExcluded($exclusions, null, $file))
			{
				$this->createOrUpdateTest($file, $suite);
			}
			else
			{
				// If the test already exists, delete it.
				//
				if ($test = $this->findTestByNameAndSuite($file, $suite))
				{
					$test->delete();
				}
			}
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

	public function storeTestResult($test, $lines, $ok)
	{
		$run = Run::create([
	        'test_id' => $test->id,
	        'was_ok' => $ok,
	        'log' => $lines ?: '(empty)',
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

	public function deleteUnavailableTesters($testers)
	{
		foreach(Tester::all() as $tester)
		{
			if ( ! in_array($tester->name, $testers))
			{
				$tester->delete();
			}
		}
	}

	public function deleteUnavailableProjects($projects)
	{
		foreach(Project::all() as $project)
		{
			if ( ! in_array($project->name, $projects))
			{
				$project->delete();
			}
		}
	}

	public function isExcluded($exclusions, $path, $file = '')
	{
		if ($file)
		{
			if ( ! $file instanceof SplFileInfo)
			{
				$path = make_path([$path, $file]);
			}
			else
			{
				$path = $file->getPathname();
			}
		}

		foreach($exclusions ?: [] as $excluded)
		{
			if (starts_with($path, $excluded))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $suite
	 * @param $file
	 * @return mixed
	 */
	private function findTestByNameAndSuite($file, $suite)
	{
		return Test::where('name', $file->getRelativePathname())->where('suite_id', $suite->id)->first();
	}

	public function getTests($project_id = null)
	{
		$tests = [];

		$order = "case state = 'failed' or state = 'running'
					  when true
						then
					     case state = 'failed'
					       when true
					        then DATE '2070-10-31 00:00:01'
					       else DATE '2070-10-31 00:00:00'
					     end
					  else tests.updated_at
					end

					desc";

		$query = Test::select('tests.*')
					->join('suites', 'suites.id', '=', 'suite_id')
					->orderByRaw($order);

		if ($project_id)
		{
			$query->where('project_id', $project_id);
		}

		foreach ($query->get() as $test)
		{
			$log = $this->formatLog($test->runs->last()->first());

			$tests[] = [
				'id' => $test->id,
			    'name' => $test->name,
			    'updated_at' => $test->updated_at->diffForHumans(),
			    'state' => $test->state,
			    'log' => $log,
			];
		}

		return Response::json($tests);
	}

	public function getProjects()
	{
		return Response::json(Project::all());
	}

	private function formatLog($log)
	{
		if ($log)
		{
			$log = $this->ansi2Html($log->log);
		}

		return $log;
	}

	private function ansi2Html($log)
	{
		$converter = new AnsiToHtmlConverter();

		$log = $converter->convert($log);

		$log = str_replace(chr(13).chr(10), '<br>', $log);

		$log = str_replace(chr(10), '<br>', $log);

		$log = str_replace(chr(13), '<br>', $log);

		return $log;
	}

}
