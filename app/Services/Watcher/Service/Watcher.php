<?php

namespace App\Services\Watcher\Service;

use App\Services\Watcher\Data\Repositories\Data as DataRepository;
use Config;
use App;
use Illuminate\Console\Command;
use JasonLewis\ResourceWatcher\Event;

class Watcher {

	/**
	 * Is the watcher initialized?
	 *
	 * @var
	 */
	protected $is_initialized;

	/**
	 * Folders to be watched.
	 *
	 * @var
	 */
	protected $watchFolders;

	/**
	 * The file watcher.
	 *
	 * @var
	 */
	protected $watcher;

	/**
	 * Folder listeners.
	 *
	 * @var
	 */
	protected $listeners;

	/**
	 * Console command object.
	 *
	 * @var
	 */
	protected $command;

	/**
	 * Exclude folders.
	 *
	 * @var
	 */
	protected $exclusions;

	/**
	 * Watcher Repository.
	 *
	 * @var DataRepository
	 */
	private $dataRepository;

	/**
	 * Instantiate a Watcher.
	 *
	 * @param DataRepository $dataRepository
	 */
	public function __construct(DataRepository $dataRepository)
	{
		$this->dataRepository = $dataRepository;

		$this->watcher = App::make('watcher');
	}

	/**
	 * Watch for file changes.
	 *
	 * @param Command $command
	 * @return bool
	 */
	public function run(Command $command)
	{
		$this->command = $command;

		$this->initialize();

		$this->watch();

	    return true;
	}

	/**
	 * Initialize the Watcher.
	 *
	 */
	private function initialize()
	{
		$this->command->comment('Laravel-CI - Watcher');

		if ( ! $this->is_initialized)
		{
			$this->loadEverything();

			$this->is_initialized = true;
		}
	}

	/**
	 * Read configuration and load testers, projects, suites...
	 *
	 */
	private function loadEverything()
	{
		$this->command->line('Loading testers...');
		$this->loadTesters();

		$this->command->line('Loading projects and suites...');
		$this->loadProjects();

		$this->command->line('Loading tests...');
		$this->loadTests();
	}

	/**
	 * Load all testers to database.
	 *
	 */
	private function loadTesters()
	{
		foreach(Config::get('watcher.testers') as $name => $data)
		{
			$this->dataRepository->createOrUpdateTester($name, $data);
		}

		$this->dataRepository->deleteUnavailableTesters(array_keys(Config::get('watcher.testers')));
	}

	/**
	 * Load all projects to database.
	 *
	 */
	private function loadProjects()
	{
		foreach(Config::get('watcher.projects') as $name => $data)
		{
			$project = $this->dataRepository->createOrUpdateProject($name, $data['path'], $data['tests_path']);

			foreach($data['suites'] as $suite_name => $suite_data)
			{
				$this->dataRepository->createOrUpdateSuite($name, $project->id, $suite_data);
			}

			$this->addToWatchFolders($data['path'], $data['watch_folders']);

			$this->addToExclusions($data['path'], $data['exclude_folders']);
		}

		$this->dataRepository->deleteUnavailableProjects(array_keys(Config::get('watcher.projects')));
	}

	/**
	 * Load all test files to database.
	 *
	 */
	private function loadTests()
	{
		$this->dataRepository->syncTests($this->exclusions);
	}

	/**
	 * Add folders to the watch list.
	 *
	 * @param $path
	 * @param $watch_folders
	 */
	private function addToWatchFolders($path, $watch_folders)
	{
		foreach($watch_folders as $folder)
		{
			$this->watchFolders[] = make_path([$path, $folder]);
		}
	}

	private function addToExclusions($path, $exclude_folders)
	{
		foreach($exclude_folders as $folder)
		{
			$this->exclusions[] = make_path([$path, $folder]);
		}
	}

	private function watch()
	{
		$this->command->line('Watching...');

		$me = $this;

		foreach($this->watchFolders as $folder)
		{
			$this->listeners[$folder] = $this->watcher->watch($folder);

			$this->listeners[$folder]->anything(function($event, $resource, $path) use ($me)
			{
				if ( ! $me->isExcluded($path))
				{
					$me->fireEvent($event, $resource, $path);
				}
			});
		}

		$this->watcher->start();
	}

	/**
	 * Fire file modified event.
	 *
	 * @param $event
	 * @param $resource
	 * @param $path
	 */
	public function fireEvent($event, $resource, $path)
	{
		$message = "File {$path} was ".$this->getEventName($event->getCode());

		$this->command->drawLine($message);

		$this->command->line($message);

		if ($test = $this->dataRepository->isTestFile($path))
		{
			$this->command->line('Test added to queue');

			$this->dataRepository->addTestToQueue($test);

			return;
		}

		$this->command->line('All tests added to queue');

		$this->dataRepository->queueAllTests();
	}

	private function getEventName($eventCode)
	{
		$event = '(unknown event)';

		switch($eventCode)
        {
		    case Event::RESOURCE_DELETED:
		        $event = "deleted";
		        break;
		    case Event::RESOURCE_CREATED:
			    $event = "created";
		        break;
		    case Event::RESOURCE_MODIFIED:
			    $event = "modified";
		        break;
		}

		return $event;
	}

	public function isExcluded($folder)
	{
		return $this->dataRepository->isExcluded($this->exclusions, $folder);
	}

}
