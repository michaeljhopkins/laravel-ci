<?php

namespace App\Services\Watcher\Service;

use App\Services\Watcher\Data\Repositories\Watcher as WatcherRepository;
use Config;

class Watcher {

	/**
	 * Is the watcher initialized?
	 *
	 * @var
	 */
	protected $is_initialized;

	/**
	 * Watcher Repository.
	 *
	 * @var WatcherRepository
	 */
	private $watcherRepository;

	/**
	 * Instantiate a Watcher.
	 *
	 * @param WatcherRepository $watcherRepository
	 */
	public function __construct(WatcherRepository $watcherRepository)
	{
		$this->watcherRepository = $watcherRepository;
	}

	/**
	 * Watch for file changes.
	 *
	 * @return bool
	 */
	public function run()
	{
		$this->initialize();

	    return true;
	}

	/**
	 * Initialize the Watcher.
	 *
	 */
	private function initialize()
	{
		if ( ! $this->is_initialized)
		{
			$this->loadTesters();
			$this->loadProjects();
			$this->loadTests();

			$this->is_initialized = true;
		}
	}

	/**
	 * Load all testers to database.
	 *
	 */
	private function loadTesters()
	{
		foreach(Config::get('watcher.testers') as $name => $command)
		{
			$this->watcherRepository->createOrUpdateTester($name, $command);
		}
	}

	/**
	 * Load all projects to database.
	 *
	 */
	private function loadProjects()
	{
		foreach(Config::get('watcher.projects') as $name => $data)
		{
			$project = $this->watcherRepository->createOrUpdateProject($name, $data['path'], $data['tests_path']);

			foreach($data['suites'] as $suite_name => $suite_data)
			{
				$this->watcherRepository->createOrUpdateSuite($name, $project->id, $suite_data);
			}
		}
	}

	/**
	 * Load all tests files to database.
	 *
	 */
	private function loadTests()
	{
		$this->watcherRepository->syncTests();
	}

}
