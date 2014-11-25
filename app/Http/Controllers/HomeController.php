<?php

namespace App\Http\Controllers;

class HomeController extends Controller {

	public function index()
	{
//		$data = \App::make('PragmaRX\Ci\Data\Repositories\Data');
//
//		$data->storeTestResult(\PragmaRX\Ci\Vendor\Laravel\Entities\Test::find(13), 'ERRRRRRRRR', false);
//
		return view('pragmarx/ci::dashboard');
	}

}
