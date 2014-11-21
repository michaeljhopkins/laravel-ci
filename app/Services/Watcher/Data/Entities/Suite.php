<?php

namespace App\Services\Watcher\Data\Entities;

use Illuminate\Database\Eloquent\Model;

class Suite extends Model {

	protected $fillable = [
		'name',
		'project_id',
		'tester_id',
		'tests_path',
		'suite_path',
		'file_mask',
		'command_options',
	    'max_retries',
	];

	public function getTestsFullPathAttribute($value)
	{
		return make_path(
				[
					$this->project->path,
					$this->project->tests_path,
					$this->tests_path
				]
		);
	}

	public function project()
	{
		return $this->belongsTo('App\Services\Watcher\Data\Entities\Project');
	}

	public function tester()
	{
		return $this->belongsTo('App\Services\Watcher\Data\Entities\Tester');
	}

	public function tests()
	{
		return $this->hasMany('App\Services\Watcher\Data\Entities\Test');
	}

	public function getTestCommandAttribute()
	{
		$command = $this->tester->command . ' ' . $this->command_options;

		return str_replace('%project_path%', $this->project->path, $command);
	}

}
