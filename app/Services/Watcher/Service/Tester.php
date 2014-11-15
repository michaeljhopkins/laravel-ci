<?php

namespace App\Services\Watcher\Service;

use App\Services\Watcher\Data\Repositories\Data as DataRepository;
use Illuminate\Console\Command;

class Tester {

	/**
	 * Is it testing?
	 *
	 * @var
	 */
	protected $testing;

	/**
	 * Instantiate a Tester.
	 *
	 * @param DataRepository $dataRepository
	 */
	public function __construct(DataRepository $dataRepository)
	{
		$this->dataRepository = $dataRepository;
	}

	/**
	 * Run the tester.
	 *
	 * @param Command $command
	 */
	public function run(Command $command)
	{
		$this->command = $command;

		$this->startTester();
	}

	/**
	 * Start the timed tester.
	 *
	 * @param int $interval
	 * @param null $timeout
	 * @param Closure $callback
	 */
	public function startTester($interval = 1000000, $timeout = null, Closure $callback = null)
	{
		$this->testing = true;

		$timeTesting = 0;

		while ($this->testing)
		{
			if (is_callable($callback))
			{
				call_user_func($callback, $this);
			}

			usleep($interval);

			$this->test();

			$timeTesting += $interval;

			if ( ! is_null($timeout) and $timeTesting >= $timeout)
			{
				$this->stopTest();
			}
		}
	}

	/**
	 * Stop testing.
	 *
	 * @return void
	 */
	public function stopTest()
	{
		$this->testing = false;
	}

	/**
	 * Find and execute a test.
	 *
	 */
	private function test()
	{
		if ( ! $test = $this->dataRepository->getNextTestFromQueue())
		{
			return;
		}

		$this->command->info('Testing '.$test->fullPath);

//		$this->executeTest($test);
	}

}
