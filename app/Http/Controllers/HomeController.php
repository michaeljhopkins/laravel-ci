<?php

namespace App\Http\Controllers;

class HomeController extends Controller {

	public function index()
	{
		return view('pragmarx/ci::dashboard');
	}

}
