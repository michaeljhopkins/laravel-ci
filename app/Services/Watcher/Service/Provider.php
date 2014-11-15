<?php

namespace App\Services\Watcher\Service;

use PragmaRX\Support\ServiceProvider;

class Provider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerWatcher();
	}

	private function registerWatcher()
	{
		$this->app->singleton('ci.watcher', function($app)
		{
			return $this->app->make('App\Services\Watcher\Service\Watcher');
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['ci.watcher'];
	}

}
