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
	protected $excludeFolders;

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

		$this->command->comment('Laravel-CI - Watcher');

		$this->initialize();

		$this->command->line('Watching...');

		$this->watch();

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
			$this->command->line('Loading testers...');
			$this->loadTesters();

			$this->command->line('Loading projects and suites...');
			$this->loadProjects();

			$this->command->line('Loading tests...');
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
		foreach(Config::get('watcher.testers') as $name => $data)
		{
			$this->dataRepository->createOrUpdateTester($name, $data);
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
			$project = $this->dataRepository->createOrUpdateProject($name, $data['path'], $data['tests_path']);

			foreach($data['suites'] as $suite_name => $suite_data)
			{
				$this->dataRepository->createOrUpdateSuite($name, $project->id, $suite_data);
			}

			$this->addToWatchFolders($data['path'], $data['watch_folders']);

			$this->addToExcludeFolders($data['path'], $data['exclude_folders']);
		}
	}

	/**
	 * Load all test files to database.
	 *
	 */
	private function loadTests()
	{
		$this->dataRepository->syncTests();
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

	private function addToExcludeFolders($path, $exclude_folders)
	{
		foreach($exclude_folders as $folder)
		{
			$this->excludeFolders[] = make_path([$path, $folder]);
		}
	}

	private function watch()
	{
		$me = $this;

		foreach($this->watchFolders as $folder)
		{
			$this->listeners[$folder] = $this->watcher->watch($folder);

			$this->listeners[$folder]->anything(function($event, $resource, $path) use ($me)
			{
				if ( ! $me->folderIsExcluded($path))
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

	public function folderIsExcluded($folder)
	{
		foreach($this->excludeFolders as $excluded)
		{
			if (starts_with($folder, $excluded))
			{
				return true;
			}
		}

		return false;
	}

}
