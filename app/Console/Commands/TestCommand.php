<?php namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'ci:test';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Continuously run tests';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->getLaravel()->make('ci.tester')->run($this);
	}

}
