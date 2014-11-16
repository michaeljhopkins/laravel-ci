<?php

namespace App\Services\Watcher\Service;

use App\Services\Watcher\Data\Repositories\Data as DataRepository;
use App\Services\Watcher\Support\ShellExec;
use Illuminate\Console\Command;

class Tester {

	/**
	 * Is it testing?
	 *
	 * @var
	 */
	protected $testing;

	/**
	 * The command object.
	 *
	 * @object Illuminate\Console\Command
	 */
	protected $command;

	/**
	 * @var ShellExec
	 */
	private $shell;

	/**
	 * Instantiate a Tester.
	 *
	 * @param DataRepository $dataRepository
	 */
	public function __construct(DataRepository $dataRepository, ShellExec $shell)
	{
		$this->dataRepository = $dataRepository;

		$this->shell = $shell;
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
		$me = $this;

		if ( ! $test = $this->dataRepository->getNextTestFromQueue())
		{
			return;
		}

		$this->command->info('Testing '.$test->fullPath);

		$this->command->info('Executing '.$test->testCommand);

		$this->shell->exec($test->testCommand, $test->suite->project->path, function($line) use ($me)
		{
			$me->showProgress($line);
		});
	}

	public function showProgress($line)
	{
		$this->command->info($line);
	}
}
