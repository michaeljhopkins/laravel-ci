<?php namespace App\Http\Controllers;

use App\Services\Watcher\Data\Repositories\Data;

class HomeController extends Controller {

	/**
	 * @var Data
	 */
	private $dataRepository;

	public function __construct(Data $dataRepository)
	{
		$this->dataRepository = $dataRepository;
	}

	public function index()
	{
		// \Artisan::call('ci:watch'); /// !!!!! YOU HAVE TO DISABLE WATCH()!!!!

		return view('home');
	}

	public function allTests($project_id = null)
	{
		return $this->dataRepository->getTests($project_id);
	}

	public function allProjects()
	{
		return $this->dataRepository->getProjects();
	}

}
