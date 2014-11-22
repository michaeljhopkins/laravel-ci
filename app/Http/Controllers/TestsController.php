<?php

namespace App\Http\Controllers;

use App\Services\Watcher\Data\Repositories\Data;

class TestsController extends Controller {

	/**
	 * @var Data
	 */
	private $dataRepository;

	public function __construct(Data $dataRepository)
	{
		$this->dataRepository = $dataRepository;
	}

	public function allTests($project_id = null)
	{
		return $this->dataRepository->getTests($project_id);
	}

	public function allProjects()
	{
		return $this->dataRepository->getProjects();
	}

	public function enableTests($enable, $project_id, $test_id = null)
	{
//		return \Response::json(['success' => true, 'state' => true]);

		return $this->dataRepository->enableTests($enable, $project_id, $test_id);
	}

}
