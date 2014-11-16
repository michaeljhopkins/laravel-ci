<?php namespace App\Http\Controllers;

use App\Services\Watcher\Data\Repositories\Data;
use Artisan;

class HomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	$router->get('/', 'HomeController@showWelcome');
	|
	*/

	public function index()
	{
//		Artisan::call('ci:watch');

		return view('home');
	}

	public function allTests(Data $dataRepository)
	{
		return $dataRepository->getAllTests();
	}

}
